<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesAgent;
use App\Models\AgentOrder;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Auth;

class AgentOrderPaymentController extends Controller
{
    public function create(Request $request)
    {
        $agents = SalesAgent::where('status', 1)->get();
        $selectedAgentId = $request->get('agent_id');
        $selectedOrderId = $request->get('order_id');

        return view('admin.payment.agent_order.create', compact('agents', 'selectedAgentId', 'selectedOrderId'));
    }

    public function getOrders(Request $request)
    {
        $agentId = $request->agent_id;
        // Fetch orders for the agent that have a remaining balance
        $orders = AgentOrder::where('sales_agent_id', $agentId)
            ->get()
            ->filter(function ($order) {
                return $order->balance_amount > 0;
            })
            ->values(); // Reset keys for JSON

        // Return JSON to be handled by frontend
        return response()->json([
            'status' => 'success',
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:sales_agents,id',
            'agent_order_id' => 'required|exists:agent_orders,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $order = AgentOrder::findOrFail($request->agent_order_id);

        if ($request->amount > $order->balance_amount + 1) { // Tolerance of 1 for float issues
            return redirect()->back()->with('error', 'Amount cannot be greater than pending balance (' . $order->balance_amount . ')');
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'payment_category' => 'agent_order',
                'payment_type' => 'received',
                'party_type' => SalesAgent::class,
                'party_id' => $request->agent_id,
                'paymentable_type' => AgentOrder::class,
                'paymentable_id' => $request->agent_order_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'reference_id' => $request->reference_id,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Handle Image Upload if any
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('assets/payment-images'), $imageName);
                $payment->image = $imageName;
                $payment->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
