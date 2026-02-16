<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterCustomer;
use App\Models\OrderDispatch;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Auth;

class CorporateOrderPaymentController extends Controller
{
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

        return view('admin.payment.corporate_order.create', compact('customers', 'selectedCustomerId', 'selectedDispatchId'));
    }

    public function getDispatches(Request $request)
    {
        $customerId = $request->customer_id;

        // Fetch dispatches for this customer that belong to corporate orders and have balance
        $dispatches = OrderDispatch::where('customer_id', $customerId)
            ->whereHas('orderMain', function ($q) {
                $q->where('order_type', 'corporate');
            })
            ->get()
            ->filter(function ($dispatch) {
                return $dispatch->balance_amount > 0;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'dispatches' => $dispatches
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:master_customers,id',
            'order_dispatch_id' => 'required|exists:order_dispatch,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $dispatch = OrderDispatch::findOrFail($request->order_dispatch_id);

        if ($request->amount > $dispatch->balance_amount + 1) {
            return redirect()->back()->with('error', 'Amount cannot be greater than pending balance (' . $dispatch->balance_amount . ')');
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'payment_category' => 'corporate_order',
                'payment_type' => 'received',
                'party_type' => MasterCustomer::class,
                'party_id' => $request->customer_id,
                'paymentable_type' => OrderDispatch::class,
                'paymentable_id' => $request->order_dispatch_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'reference_id' => $request->reference_id,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

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
