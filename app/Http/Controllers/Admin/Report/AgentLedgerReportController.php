<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\SalesAgent;
use App\Models\AgentOrder;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentLedgerReportController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesAgent::withCount('shops')
            ->with(['shops' => function($q) {
                $q->select('id', 'name', 'sales_agent_id');
            }])
            ->with(['orders' => function ($q) {
                $q->select('sales_agent_id', 'grand_total');
            }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $agents = $query->get()->map(function ($agent) {
            $total_orders = $agent->orders->sum('grand_total');
            
            // Polymorphic payments for this agent (as a party)
            $total_payments = Payment::where('party_type', SalesAgent::class)
                ->where('party_id', $agent->id)
                ->sum('amount');

            $agent->total_order_value = $total_orders;
            $agent->total_payments = $total_payments;
            $agent->balance = $total_orders - $total_payments;
            return $agent;
        });

        return view('admin.report.agent_ledger.index', compact('agents'));
    }

    public function show(Request $request, $id)
    {
        $agent = SalesAgent::with('shops')->findOrFail($id);
        
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $shopId = $request->query('shop_id');

        $shops = $agent->shops;

        // --- Shop-wise Summary ---
        $shopSummary = $shops->map(function ($shop) {
            $total_orders = AgentOrder::where('master_customer_id', $shop->id)->sum('grand_total');
            $total_received = Payment::whereHasMorph('paymentable', [AgentOrder::class], function($q) use ($shop) {
                $q->where('master_customer_id', $shop->id);
            })->sum('amount');

            return (object) [
                'id' => $shop->id,
                'name' => $shop->name,
                'total_orders' => $total_orders,
                'total_received' => $total_received,
                'pending_payment' => $total_orders - $total_received
            ];
        });

        // --- Detailed Transactions ---
        // Orders (Debits)
        $ordersQuery = AgentOrder::with('shop')
            ->where('sales_agent_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('order_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('order_date', '<=', $endDate))
            ->when($shopId, fn($q) => $q->where('master_customer_id', $shopId));

        $orders = $ordersQuery->get()
            ->map(function ($order) {
                return (object) [
                    'date' => $order->order_date,
                    'description' => 'Order: #ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'shop_name' => $order->shop ? $order->shop->name : 'N/A',
                    'reference' => $order->id,
                    'debit' => $order->grand_total,
                    'credit' => 0,
                    'type' => 'order'
                ];
            });

        // Payments (Credits)
        $paymentsQuery = Payment::where('party_type', SalesAgent::class)
            ->where('party_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate));

        if ($shopId) {
            $paymentsQuery->whereHasMorph('paymentable', [AgentOrder::class], function($q) use ($shopId) {
                $q->where('master_customer_id', $shopId);
            });
        }

        $payments = $paymentsQuery->get()
            ->map(function ($payment) {
                $shopName = 'N/A';
                if ($payment->paymentable_type === AgentOrder::class && $payment->paymentable) {
                    $shopName = $payment->paymentable->shop ? $payment->paymentable->shop->name : 'N/A';
                }

                return (object) [
                    'date' => $payment->payment_date,
                    'description' => 'Payment: ' . ($payment->payment_mode ?: 'N/A') . ' (' . ($payment->reference_id ?: '-') . ')',
                    'shop_name' => $shopName,
                    'reference' => $payment->id,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'type' => 'payment'
                ];
            });

        // Combine and sort by date
        $transactions = $orders->concat($payments)->sortBy('date')->values();

        // Calculate running balance
        $balance = 0;
        foreach ($transactions as $tx) {
            $balance += ($tx->debit - $tx->credit);
            $tx->running_balance = $balance;
        }

        return view('admin.report.agent_ledger.show', compact('agent', 'transactions', 'startDate', 'endDate', 'shops', 'shopId', 'shopSummary'));
    }
}
