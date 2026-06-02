<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\MasterCustomer;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agent_id = Auth::guard('sales_agent')->id();
        $is_master = Auth::guard('sales_agent')->user()->is_master_agent;

        if ($is_master) {
            $stats = [
                'total_orders' => \DB::table('agent_orders')->count(),
                'pending_orders' => \DB::table('agent_orders')->where('status', 'pending')->count(),
                'total_shops' => MasterCustomer::count(),
            ];

            $recent_orders = \DB::table('agent_orders')
                ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
                ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
                ->select('agent_orders.*', \DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'))
                ->latest('order_date')
                ->limit(5)
                ->get();
        } else {
            $stats = [
                'total_orders' => \DB::table('agent_orders')->where('sales_agent_id', $agent_id)->count(),
                'pending_orders' => \DB::table('agent_orders')->where('sales_agent_id', $agent_id)->where('status', 'pending')->count(),
                'total_shops' => MasterCustomer::where('sales_agent_id', $agent_id)->count(),
            ];

            $recent_orders = \DB::table('agent_orders')
                ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
                ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
                ->where('agent_orders.sales_agent_id', $agent_id)
                ->select('agent_orders.*', \DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'))
                ->latest('order_date')
                ->limit(5)
                ->get();
        }

        return view('sales_agent.dashboard', compact('stats', 'recent_orders'));
    }
}
