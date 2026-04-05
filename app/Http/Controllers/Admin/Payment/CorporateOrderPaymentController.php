<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterCustomer;
use App\Models\OrderDispatch;
use App\Models\OrderMain;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\CashPayment;
use App\Services\Admin\BalanceService;
use Illuminate\Support\Facades\DB;
use Auth;

class CorporateOrderPaymentController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function index()
    {
        $payments = Payment::where('payment_category', 'corporate_order')
            ->with(['party', 'paymentable'])
            ->latest()
            ->get();

        return view('admin.payment.corporate_order.index', compact('payments'));
    }

    public function create(Request $request)
    {
        // Fetch customers who have corporate orders/dispatches
        $customers = MasterCustomer::whereHas('orders', function ($q) {
            $q->where('order_type', 'corporate');
        })->get();

        $selectedCustomerId = $request->get('customer_id');
        $selectedDispatchId = $request->get('dispatch_id');
        $bank_accounts = BankAccount::where('status', 1)->orderBy('bank_name')->get();
        $cash_accounts = CashPayment::where('status', 1)->orderBy('name')->get();

        return view('admin.payment.corporate_order.create', compact('customers', 'selectedCustomerId', 'selectedDispatchId', 'bank_accounts', 'cash_accounts'));
    }

    public function getDispatches(Request $request)
    {
        $customerId = $request->customer_id;

        // Fetch dispatches for this customer that belong to corporate orders and are not fully paid
        $dispatches = OrderDispatch::where('customer_id', $customerId)
            ->where('is_paid', 0)
            ->whereHas('orderMain', function ($q) {
                $q->where('order_type', 'corporate');
            })
            ->get()
            ->values();

        // Fetch corporate orders for this customer that are not fully paid
        $orders = OrderMain::where('master_customer_id', $customerId)
            ->where('order_type', 'corporate')
            ->where('is_paid', 0)
            ->get();

        return response()->json([
            'status' => 'success',
            'dispatches' => $dispatches,
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:master_customers,id',
            'order_type' => 'required|in:order,dispatch',
            'order_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string|in:Bank,Cash',
            'payment_method_id' => 'required|integer',
        ]);

        $paymentableType = $request->order_type === 'dispatch' ? OrderDispatch::class : OrderMain::class;
        $paymentable = $paymentableType::findOrFail($request->order_id);

        // Validation for dispatch amount (Orders don't have total_amount yet)
        if ($request->order_type === 'dispatch' && $request->amount > $paymentable->balance_amount + 1) {
            return redirect()->back()->with('error', 'Amount cannot be greater than pending balance (' . $paymentable->balance_amount . ')');
        }

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

            $payment = Payment::create([
                'payment_category' => 'corporate_order',
                'payment_type' => 'received',
                'party_type' => MasterCustomer::class,
                'party_id' => $request->customer_id,
                'paymentable_type' => $paymentableType,
                'paymentable_id' => $request->order_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $methodName,
                'payment_method_type' => $request->payment_mode,
                'payment_method_id' => $request->payment_method_id,
                'reference_id' => $request->reference_id,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Mark as paid if requested
            if ($request->has('complete_payment')) {
                $paymentable->is_paid = 1;
                $paymentable->save();
            }

            // Update Balance (Corporate Order is always 'received' -> add)
            $this->balanceService->updateBalance($request->payment_mode, $request->payment_method_id, $request->amount, 'add');

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('assets/payment-images'), $imageName);
                $payment->image = $imageName;
                $payment->save();
            }

            DB::commit();

            return redirect()->route('admin.payment.corporate-order.index')->with('success', 'Corporate order payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
