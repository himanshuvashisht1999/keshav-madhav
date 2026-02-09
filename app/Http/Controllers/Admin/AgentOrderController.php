<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class AgentOrderController extends Controller
{
    public function index()
    {
        $orders = DB::table('agent_orders')
            ->join('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->select('agent_orders.*', 'sales_agents.name as agent_name', 'master_customers.name as shop_name')
            ->latest('order_date')
            ->get();

        return view('admin.agent_orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = DB::table('agent_orders')
            ->join('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                'sales_agents.name as agent_name',
                'master_customers.name as shop_name',
                'master_customers.email as shop_email',
                'master_customers.phone as shop_phone',
                'master_customers.address as shop_address'
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();

        return view('admin.agent_orders.show', compact('order', 'items'));
    }

    public function downloadInvoice($id)
    {
        $order = DB::table('agent_orders')
            ->join('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                'sales_agents.name as agent_name',
                'master_customers.name as shop_name',
                'master_customers.email as shop_email',
                'master_customers.phone as shop_phone',
                'master_customers.address as shop_address'
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();
        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.invoice-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-ORD-' . $id . '.pdf');
    }

    public function dispatchOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = DB::table('agent_orders')->where('id', $id)->first();
            if ($order->status == 'dispatched') {
                return redirect()->back()->with('error', 'Order already dispatched');
            }

            // Get boxes associated with this order
            $box_ids = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->distinct()
                ->pluck('packing_box_id');

            // 1. Delete boxes from DomesticInventory
            DB::table('domestic_inventories')->whereIn('packing_box_id', $box_ids)->delete();

            // 2. Update order status
            DB::table('agent_orders')->where('id', $id)->update([
                'status' => 'dispatched',
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->route('admin.agent-orders.index')->with('success', 'Order dispatched and inventory updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Dispatch failed: ' . $e->getMessage());
        }
    }
}
