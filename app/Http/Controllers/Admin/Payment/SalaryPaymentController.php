<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

use App\Services\Admin\BalanceService;
use App\Models\BankAccount;
use App\Models\CashPayment;

class SalaryPaymentController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }
    public function create()
    {
        $employees = Employee::where('status', 1)->orderBy('name')->get();
        $bank_accounts = BankAccount::where('status', 1)->get();
        $cash_accounts = CashPayment::where('status', 1)->get();
        return view('admin.payment.salary.create', compact('employees', 'bank_accounts', 'cash_accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:Bank,Cash',
            'payment_method_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($request->employee_id);

            $payment = new Payment();
            $payment->payment_category = 'salary';
            $payment->payment_type = 'paid';
            $payment->party_type = 'App\Models\Employee';
            $payment->party_id = $employee->id;
            $payment->paymentable_type = 'App\Models\Employee';
            $payment->paymentable_id = $employee->id;
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;

            // Get method name for record description
            $methodName = '';
            if ($request->payment_mode === 'Bank') {
                $account = BankAccount::find($request->payment_method_id);
                $methodName = $account ? "Bank: {$account->bank_name} ({$account->account_number})" : "Bank";
            } else {
                $account = CashPayment::find($request->payment_method_id);
                $methodName = $account ? "Cash: {$account->name}" : "Cash";
            }

            $payment->payment_mode = $methodName;
            $payment->payment_method_type = $request->payment_mode; // "Bank" or "Cash"
            $payment->payment_method_id = $request->payment_method_id;
            $payment->reference_id = $request->reference_id;
            $payment->remarks = $request->remarks;
            $payment->created_by = auth()->id();
            $payment->save();

            // DEDUCT balance (Salary is always a payment)
            $this->balanceService->updateBalance(
                $request->payment_mode,
                $request->payment_method_id,
                $request->amount,
                'deduct'
            );

            DB::commit();

            return redirect()->back()->with('success', 'Salary Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
