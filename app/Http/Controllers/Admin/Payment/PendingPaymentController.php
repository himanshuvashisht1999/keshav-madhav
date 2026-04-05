<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentOrder;
use App\Models\FabricReceipt;
use App\Models\OrderDispatch;
use App\Models\SalesAgent;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\OrderMain;

class PendingPaymentController extends Controller
{
    public function index(Request $request)
    {
        $isOwner = $request->is('owner/*') || $request->is('owner');
        $layout = $isOwner ? 'owner.layouts.app' : 'admin.layouts.app';
        $routePrefix = $isOwner ? 'owner.payment.pending.' : 'admin.payment.pending.';
        $homeRoute = $isOwner ? 'owner.dashboard' : 'admin.dashboard';

        $activeTab = $request->get('tab', 'agent_orders');

        // Initializing collections
        $agentOrders = collect();
        $fabricReceipts = collect();
        $corporateDispatches = collect();

        // Totals for summary cards
        $totalReceivable = 0;
        $totalPayable = 0;

        // Fetching Agents, Vendors, Customers for filters
        $agents = SalesAgent::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        $customers = MasterCustomer::whereHas('orders', function ($q) {
            $q->where('order_type', 'corporate');
        })->get();

        // Fetch Shops if an Agent is selected
        $shops = collect();
        if ($request->agent_id) {
            $shops = MasterCustomer::where('sales_agent_id', $request->agent_id)
                ->where('status', 1)
                ->get();
        }

        // Logic for Agent Orders (Receivable)
        $agentOrdersQuery = AgentOrder::with(['agent', 'shop'])->latest();
        if ($request->from_date)
            $agentOrdersQuery->whereDate('order_date', '>=', $request->from_date);
        if ($request->to_date)
            $agentOrdersQuery->whereDate('order_date', '<=', $request->to_date);
        if ($request->agent_id)
            $agentOrdersQuery->where('sales_agent_id', $request->agent_id);
        if ($request->shop_id)
            $agentOrdersQuery->where('master_customer_id', $request->shop_id);

        $agentOrders = $agentOrdersQuery->get()->filter(function ($order) {
            return $order->balance_amount > 0;
        });
        $totalReceivable += $agentOrders->sum('balance_amount');

        // Logic for Fabric Shipments (Payable)
        $fabricReceiptsQuery = FabricReceipt::with('vendor')->latest();
        if ($request->from_date)
            $fabricReceiptsQuery->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)
            $fabricReceiptsQuery->whereDate('created_at', '<=', $request->to_date);
        if ($request->vendor_id)
            $fabricReceiptsQuery->where('vendor_id', $request->vendor_id);

        $fabricReceipts = $fabricReceiptsQuery->get()->filter(function ($receipt) {
            return $receipt->balance_amount > 0;
        });
        $totalPayable += $fabricReceipts->sum('balance_amount');

        // Logic for Corporate Orders & Dispatches (Receivable)
        $corporateDispatchesQuery = OrderDispatch::with(['customer', 'orderMain'])
            ->where('is_paid', 0)
            ->whereHas('orderMain', function ($q) {
                $q->where('order_type', 'corporate');
            })->latest();
        if ($request->from_date)
            $corporateDispatchesQuery->whereDate('dispatch_date', '>=', $request->from_date);
        if ($request->to_date)
            $corporateDispatchesQuery->whereDate('dispatch_date', '<=', $request->to_date);
        if ($request->customer_id)
            $corporateDispatchesQuery->where('customer_id', $request->customer_id);

        $corporateDispatches = $corporateDispatchesQuery->get()->filter(function ($dispatch) {
            return $dispatch->balance_amount > 0;
        });
        $totalReceivable += $corporateDispatches->sum('balance_amount');

        // Logic for Corporate Orders without dispatches yet
        $corporateOrdersQuery = OrderMain::with('customer')
            ->where('order_type', 'corporate')
            ->where('is_paid', 0)
            // Ideally only orders WITHOUT any dispatch or if we want to show all pending orders
            ->latest();

        if ($request->from_date)
            $corporateOrdersQuery->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date)
            $corporateOrdersQuery->whereDate('created_at', '<=', $request->to_date);
        if ($request->customer_id)
            $corporateOrdersQuery->where('master_customer_id', $request->customer_id);

        $corporateOrders = $corporateOrdersQuery->get();
        // Since we don't have total_amount for orders, we can't easily add to totalReceivable unless we calculate it

        return view('admin.payment.pending.index', compact(
            'agentOrders',
            'fabricReceipts',
            'corporateDispatches',
            'corporateOrders',
            'totalReceivable',
            'totalPayable',
            'activeTab',
            'agents',
            'shops',
            'vendors',
            'customers',
            'layout',
            'routePrefix',
            'homeRoute',
            'isOwner'
        ));
    }
}
