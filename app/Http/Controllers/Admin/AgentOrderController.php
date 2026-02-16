<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;

class AgentOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('agent_orders')
            ->join('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->select(
                'agent_orders.*',
                'sales_agents.name as agent_name',
                'master_customers.name as shop_name',
                DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") as total_paid')
            );

        // Filtering
        if ($request->filled('agent_id')) {
            $query->where('agent_orders.sales_agent_id', $request->agent_id);
        }
        if ($request->filled('shop_id')) {
            $query->where('agent_orders.master_customer_id', $request->shop_id);
        }
        if ($request->filled('status')) {
            $query->where('agent_orders.status', $request->status);
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status == 'paid') {
                $query->whereRaw('(SELECT SUM(amount) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") >= agent_orders.grand_total');
            } elseif ($request->payment_status == 'unpaid') {
                $query->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") < agent_orders.grand_total');
            }
        }

        $orders = $query->latest('order_date')
            ->paginate(20)
            ->appends($request->query());

        $agents = DB::table('sales_agents')->select('id', 'name')->get();
        $shops = DB::table('master_customers')->select('id', 'name')->get();

        return view('admin.agent_orders.index', compact('orders', 'agents', 'shops'));
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
                'master_customers.address as shop_address',
                DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") as total_paid')
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();

        return view('admin.agent_orders.show', compact('order', 'items'));
    }

    public function edit($id, Request $request)
    {
        $order = AgentOrder::where('id', $id)->firstOrFail();

        if ($order->status != 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be edited.');
        }

        $shop = $order->shop;

        // Fetch Filter Options
        $designs = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('design_number');
        $colors = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('color_name');
        $size_sets = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('size_set_name');

        // Build Query for All Boxes
        $prices = DB::table('inventory_prices')
            ->select('design_id', 'color_id', DB::raw('MAX(selling_price) as selling_price'), DB::raw('MAX(mrp) as mrp'))
            ->groupBy('design_id', 'color_id');

        $query = DomesticInventory::whereNotNull('packing_box_id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.design_id')
                    ->on('domestic_inventories.color_id', '=', 'ip.color_id');
            });

        if ($request->filled('design_number')) {
            $query->where('domestic_inventories.design_number', $request->design_number);
        }
        if ($request->filled('color_name')) {
            $query->where('domestic_inventories.color_name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('domestic_inventories.size_set_name', $request->size_set_name);
        }

        $boxes = $query->select(
            DB::raw('MIN(domestic_inventories.packing_box_id) as example_box_id'),
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'domestic_inventories.design_number',
            'domestic_inventories.color_name',
            'domestic_inventories.size_set_name',
            DB::raw('COUNT(DISTINCT domestic_inventories.packing_box_id) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.packing_box_id) as pcs_per_box'),
            DB::raw('MAX(COALESCE(ip.selling_price, 0)) as unit_price')
        )
            ->groupBy('domestic_inventories.product_id', 'domestic_inventories.color_id', 'domestic_inventories.size_set_id', 'domestic_inventories.design_number', 'domestic_inventories.color_name', 'domestic_inventories.size_set_name')
            ->orderBy('design_number')
            ->paginate(50)
            ->appends($request->except('page'));

        // Fetch images for the boxes
        $boxImages = [];
        foreach ($boxes as $variation) {
            $image = DB::table('inventory_price_images as ipi')
                ->join('inventory_prices as ip', 'ipi.inventory_price_id', '=', 'ip.id')
                ->where('ip.design_id', $variation->product_id)
                ->where('ip.color_id', $variation->color_id)
                ->where('ipi.is_main', 1)
                ->value('ipi.image_path');

            if (!$image) {
                $image = DB::table('inventory_prices')
                    ->where('design_id', $variation->product_id)
                    ->where('color_id', $variation->color_id)
                    ->value('image');
            }
            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;
        }

        // Selected quantities for existing order
        $selected_quantities = AgentOrderItem::where('agent_order_id', $order->id)
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('COUNT(*) as box_count'),
                DB::raw('AVG(quantity) as pcs_per_box'),
                DB::raw('MAX(selling_price) as unit_price')
            )
            ->groupBy('product_id', 'color_id', 'size_set_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id;
            })
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
                    'qty' => $item->box_count,
                    'pcs_per_box' => (float) $item->pcs_per_box,
                    'unit_price' => (float) $item->unit_price
                ];
            })
            ->toArray();

        // Fetch GST setting
        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        return view('admin.agent_orders.edit', compact('order', 'shop', 'designs', 'colors', 'size_sets', 'boxes', 'boxImages', 'selected_quantities', 'gst_percentage'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'variations' => 'required|array|min:1',
            'variations.*.product_id' => 'required',
            'variations.*.color_id' => 'required',
            'variations.*.size_set_id' => 'required',
            'variations.*.qty' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'expected_dispatch_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = AgentOrder::where('id', $id)->firstOrFail();

        if ($order->status != 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending orders can be edited.'], 403);
        }

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];

        foreach ($request->variations as $var) {
            if ($var['qty'] <= 0)
                continue;

            $variation = DomesticInventory::where('product_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->where('size_set_id', $var['size_set_id'])
                ->first();

            if (!$variation)
                continue;

            $priceData = DB::table('inventory_prices')
                ->where('design_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->first();

            $mrp = $priceData->mrp ?? $variation->mrp ?? 0;
            $selling_price = $priceData->selling_price ?? $variation->selling_price ?? 0;
            $product_name = $priceData->name ?? $variation->product_name;

            $avg_qty = (float) DomesticInventory::where('product_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->where('size_set_id', $var['size_set_id'])
                ->avg('quantity') ?? 0;

            for ($i = 0; $i < $var['qty']; $i++) {
                $items_to_create[] = [
                    'agent_order_id' => $order->id,
                    'product_id' => $var['product_id'],
                    'color_id' => $var['color_id'],
                    'size_set_id' => $var['size_set_id'],
                    'product_name' => $product_name,
                    'design_number' => $variation->design_number,
                    'color_name' => $variation->color_name,
                    'size_set_name' => $variation->size_set_name,
                    'quantity' => $avg_qty,
                    'mrp' => $mrp,
                    'selling_price' => $selling_price,
                    'packing_box_id' => null,
                ];
                $total_qty += $avg_qty;
                $total_amount += ($avg_qty * $selling_price);
            }
        }

        $discount_percentage = $request->has('discount_percentage') ? $request->discount_percentage : $order->discount_percentage;
        $discount_amount = $total_amount * ($discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;

        $gst_percentage = $request->has('gst_percentage') ? $request->gst_percentage : ($order->gst_percentage ?: 5.00);

        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount;

        $expected_dispatch_date = $request->expected_dispatch_date ?: $order->expected_dispatch_date;

        DB::beginTransaction();
        try {
            $order->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'gst_percentage' => $gst_percentage,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $expected_dispatch_date,
                'updated_at' => now()
            ]);

            // DELETE EXISTING ITEMS
            AgentOrderItem::where('agent_order_id', $order->id)->delete();

            // INSERT NEW ITEMS
            foreach ($items_to_create as $item) {
                AgentOrderItem::create($item);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!',
                'redirect_url' => route('admin.agent-orders.show', $order->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
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

            // Check if all items have a packing_box_id assigned
            $unassignedCount = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->whereNull('packing_box_id')
                ->count();

            if ($unassignedCount > 0) {
                return redirect()->back()->with('error', 'Cannot dispatch. ' . $unassignedCount . ' items are not yet scanned/assigned.');
            }

            // Get boxes associated with this order
            $box_ids = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->distinct()
                ->pluck('packing_box_id');

            // 1. Delete boxes from DomesticInventory (remove from stock)
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

    public function dispatchScan($id)
    {
        $order = DB::table('agent_orders')
            ->join('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                'sales_agents.name as agent_name',
                'master_customers.name as shop_name'
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->get();

        $groupedItems = [];
        foreach ($items as $item) {
            $key = $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id;
            if (!isset($groupedItems[$key])) {
                $groupedItems[$key] = [
                    'product_name' => $item->product_name,
                    'design_number' => $item->design_number,
                    'color_name' => $item->color_name,
                    'size_set_name' => $item->size_set_name,
                    'required' => 0,
                    'scanned' => 0,
                    'items' => []
                ];
            }
            $groupedItems[$key]['required']++;
            if ($item->packing_box_id) {
                $groupedItems[$key]['scanned']++;
            }
            $groupedItems[$key]['items'][] = $item;
        }

        $scannedBoxes = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNotNull('packing_box_id')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.agent_orders.dispatch_scan', compact('order', 'groupedItems', 'scannedBoxes'));
    }

    public function processScan(Request $request, $id)
    {
        $input = $request->barcode;

        // Find box in inventory using barcode, qrcode, or box_no
        $box = DB::table('domestic_inventories')
            ->where('barcode', $input)
            ->orWhere('qrcode', $input)
            ->orWhere('box_no', $input)
            ->first();

        if (!$box) {
            return response()->json(['success' => false, 'message' => 'Box not found in inventory.']);
        }

        // Check if box is in a carton
        if (!$box->packing_box_id) {
            return response()->json(['success' => false, 'message' => 'This inventory item is not in a packing box.']);
        }

        // Check if box is already assigned to ANY order
        $alreadyAssigned = DB::table('agent_order_items')->where('packing_box_id', $box->packing_box_id)->exists();
        if ($alreadyAssigned) {
            return response()->json(['success' => false, 'message' => 'Box already assigned to an order.']);
        }

        // Find a pending item in THIS order that matches this box's properties
        $pendingItem = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNull('packing_box_id')
            ->where('product_id', $box->product_id)
            ->where('color_id', $box->color_id)
            ->where('size_set_id', $box->size_set_id)
            ->first();

        if (!$pendingItem) {
            return response()->json(['success' => false, 'message' => 'This box variant is not required for this order.']);
        }

        DB::beginTransaction();
        try {
            // Assign box
            DB::table('agent_order_items')->where('id', $pendingItem->id)->update([
                'packing_box_id' => $box->packing_box_id,
                'box_no' => $box->box_no,
                'carton_no' => $box->carton_no,
                'quantity' => $box->quantity,
                'barcode' => $box->barcode,
                'qrcode' => $box->qrcode,
                'updated_at' => now()
            ]);

            // Sync order totals based on actual scanned quantities
            $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();
            $total_qty = $items->sum('quantity');
            $total_amount = $items->sum(function ($item) {
                return $item->quantity * $item->selling_price;
            });

            $order = DB::table('agent_orders')->where('id', $id)->first();
            $discount_amount = $total_amount * ($order->discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_amount = $taxable_amount * ($order->gst_percentage / 100);
            $grand_total = $taxable_amount + $gst_amount;

            DB::table('agent_orders')->where('id', $id)->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Box assigned and totals updated.',
                'variation_key' => $box->product_id . '_' . $box->color_id . '_' . $box->size_set_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function removeScan(Request $request, $id)
    {
        $packing_box_id = $request->packing_box_id;

        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('packing_box_id', $packing_box_id)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Scanned box not found in this order.']);
        }

        DB::beginTransaction();
        try {
            // Reset individual item
            DB::table('agent_order_items')->where('id', $item->id)->update([
                'packing_box_id' => null,
                'box_no' => null,
                'carton_no' => null,
                'barcode' => null,
                'qrcode' => null,
                'updated_at' => now()
            ]);

            // Sync order totals
            $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();
            $total_qty = $items->sum('quantity');
            $total_amount = $items->sum(function ($i) {
                return $i->quantity * $i->selling_price;
            });

            $order = DB::table('agent_orders')->where('id', $id)->first();
            $discount_amount = $total_amount * ($order->discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_amount = $taxable_amount * ($order->gst_percentage / 100);
            $grand_total = $taxable_amount + $gst_amount;

            DB::table('agent_orders')->where('id', $id)->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Scan removed and totals updated.',
                'variation_key' => $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
