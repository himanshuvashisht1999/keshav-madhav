<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\BankAccount;
use App\Models\CashPayment;
use App\Services\Admin\BalanceService;
use Illuminate\Support\Facades\DB;
use Auth;

class OtherPaymentController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }
    public function create()
    {
        $employees = Employee::where('status', 1)->orderBy('name')->get();
        $payment_types = \App\Models\PaymentType::where('status', 1)->orderBy('name')->get();
        $bank_accounts = BankAccount::where('status', 1)->orderBy('bank_name')->get();
        $cash_accounts = CashPayment::where('status', 1)->orderBy('name')->get();
        return view('admin.payment.other.create', compact('employees', 'payment_types', 'bank_accounts', 'cash_accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string|in:Bank,Cash',
            'payment_method_id' => 'required|integer',
            'payment_type' => 'required|in:received,paid',
            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_type_id' => 'required|exists:payment_types,id',
            'payments.*.employee_id' => 'nullable|exists:employees,id',
            'payments.*.payee_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Get method name for record description
            $methodName = '';
            if ($request->payment_mode === 'Bank') {
                $account = BankAccount::find($request->payment_method_id);
                $methodName = $account ? "Bank: {$account->bank_name} ({$account->account_number})" : "Bank";
            } else {
                $account = CashPayment::find($request->payment_method_id);
                $methodName = $account ? "Cash: {$account->name}" : "Cash";
            }

            $totalAmount = 0;

            foreach ($request->payments as $item) {
                // Skip if both employee and payee are empty
                if (empty($item['employee_id']) && empty($item['payee_name'])) {
                    continue;
                }

                $payment = new Payment();
                $payment->payment_category = 'other';
                $payment->payment_type = $request->payment_type;
                $payment->payment_type_id = $item['payment_type_id'];
                $payment->amount = $item['amount'];
                $payment->payment_date = $request->payment_date;
                $payment->payment_mode = $methodName;
                $payment->payment_method_type = $request->payment_mode;
                $payment->payment_method_id = $request->payment_method_id;
                $payment->reference_id = $request->reference_id;
                $payment->remarks = $request->remarks;
                $payment->created_by = auth()->id();

                if (!empty($item['employee_id'])) {
                    $employee = Employee::findOrFail($item['employee_id']);
                    $payment->party_type = 'App\Models\Employee';
                    $payment->party_id = $employee->id;
                    $payment->payee_name = $employee->name;
                } else {
                    $payment->payee_name = $item['payee_name'];
                }

                $payment->save();
                $totalAmount += $item['amount'];
            }

            if ($totalAmount > 0) {
                // Update Balance once for the total
                $action = ($request->payment_type === 'received') ? 'add' : 'deduct';
                $this->balanceService->updateBalance($request->payment_mode, $request->payment_method_id, $totalAmount, $action);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Payments recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
