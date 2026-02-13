<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;
use App\Models\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $shop_id = $request->query('shop_id');
        if (!$shop_id) {
            return redirect()->route('agent.shops.index')->with('error', 'Please select a shop to create an order.');
        }

        $shop = MasterCustomer::findOrFail($shop_id);

        // Fetch Filter Options
        $designs = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('design_number');
        $colors = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('color_name');
        $size_sets = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('size_set_name');

        // Build Query - Join with inventory_prices to get prices
        $prices = DB::table('inventory_prices')
            ->select('design_id', 'color_id', 'name', DB::raw('MAX(selling_price) as selling_price'), DB::raw('MAX(mrp) as mrp'))
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

        if ($request->filled('name')) {
            $query->where('ip.name', 'like', '%' . $request->name . '%');
        }

        // Get distinct variations with aggregated data
        $boxes = $query->select(
            DB::raw('MIN(domestic_inventories.packing_box_id) as example_box_id'), // Just for image lookup context if needed
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'domestic_inventories.design_number',
            'domestic_inventories.color_name',
            'domestic_inventories.size_set_name',
            DB::raw('COUNT(DISTINCT domestic_inventories.packing_box_id) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.packing_box_id) as pcs_per_box'),
            DB::raw('MAX(COALESCE(ip.selling_price, 0)) as unit_price'),
            DB::raw('ip.name')
        )
            ->groupBy('domestic_inventories.product_id', 'domestic_inventories.color_id', 'domestic_inventories.size_set_id', 'domestic_inventories.design_number', 'domestic_inventories.color_name', 'domestic_inventories.size_set_name')
            ->orderBy('design_number')
            ->paginate(20)
            ->appends($request->except('page'));

        // Fetch images for the variations
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

        // Fetch GST setting
        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        return view('sales_agent.orders.create', compact('shop', 'boxes', 'designs', 'colors', 'size_sets', 'boxImages', 'gst_percentage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:master_customers,id',
            'variations' => 'required|array|min:1',
            'variations.*.product_id' => 'required',
            'variations.*.color_id' => 'required',
            'variations.*.size_set_id' => 'required',
            'variations.*.qty' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $agent_id = Auth::guard('sales_agent')->id();
        $shop_id = $request->shop_id;

        $box_ids = [];
        foreach ($request->variations as $var) {
            // Find available box IDs for this variation
            $available_box_ids = DomesticInventory::where('product_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->where('size_set_id', $var['size_set_id'])
                ->whereNotNull('packing_box_id')
                ->distinct()
                ->pluck('packing_box_id')
                ->take($var['qty'])
                ->toArray();

            $box_ids = array_merge($box_ids, $available_box_ids);
        }

        if (empty($box_ids)) {
            return response()->json(['success' => false, 'message' => 'No boxes found for selected variations.'], 400);
        }

        // Calculate Totals using inventory_prices join
        $prices = DB::table('inventory_prices')
            ->select('design_id', 'color_id', DB::raw('MAX(selling_price) as selling_price'), DB::raw('MAX(mrp) as mrp'))
            ->groupBy('design_id', 'color_id');

        $items_to_snapshot = DomesticInventory::whereIn('packing_box_id', $box_ids)
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.design_id')
                    ->on('domestic_inventories.color_id', '=', 'ip.color_id');
            })
            ->select('domestic_inventories.*', DB::raw('COALESCE(ip.selling_price, 0) as master_selling_price'), DB::raw('COALESCE(ip.mrp, 0) as master_mrp'))
            ->get();

        $total_qty = $items_to_snapshot->sum('quantity');
        $total_amount = $items_to_snapshot->sum(function ($item) {
            return $item->quantity * $item->master_selling_price;
        });

        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = $total_amount * ($discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;

        // Fetch GST from settings if not provided (though stored usually fixed at creation)
        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount;

        DB::beginTransaction();
        try {
            $order = AgentOrder::create([
                'sales_agent_id' => $agent_id,
                'master_customer_id' => $shop_id,
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'status' => 'pending',
                'order_date' => now(),
            ]);

            foreach ($items_to_snapshot as $item) {
                AgentOrderItem::create([
                    'agent_order_id' => $order->id,
                    'packing_box_id' => $item->packing_box_id,
                    'box_no' => $item->box_no,
                    'carton_no' => $item->carton_no,
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_id' => $item->size_id,
                    'size_set_id' => $item->size_set_id,
                    'product_name' => $item->product_name,
                    'design_number' => $item->design_number,
                    'color_name' => $item->color_name,
                    'size_name' => $item->size_name,
                    'size_set_name' => $item->size_set_name,
                    'quantity' => $item->quantity,
                    'mrp' => $item->master_mrp,
                    'selling_price' => $item->master_selling_price,
                    'barcode' => $item->barcode,
                    'qrcode' => $item->qrcode,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order placed successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->where('status', 'pending')
            ->firstOrFail();

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
            DB::raw('MIN(domestic_inventories.packing_box_id) as example_box_id'), // Just for image lookup context if needed
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
            ->paginate(20)
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

        // Selected quantities for existing order with metadata
        $selected_quantities = AgentOrderItem::where('agent_order_id', $order->id)
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('COUNT(DISTINCT packing_box_id) as box_count'),
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

        return view('sales_agent.orders.edit', compact('shop', 'boxes', 'designs', 'colors', 'size_sets', 'order', 'selected_quantities', 'boxImages', 'gst_percentage'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'variations' => 'required|array|min:1',
            'variations.*.product_id' => 'required',
            'variations.*.color_id' => 'required',
            'variations.*.size_set_id' => 'required',
            'variations.*.qty' => 'required|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $box_ids = [];
        foreach ($request->variations as $var) {
            if ($var['qty'] <= 0)
                continue;

            $available_box_ids = DomesticInventory::where('product_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->where('size_set_id', $var['size_set_id'])
                ->whereNotNull('packing_box_id')
                ->distinct()
                ->pluck('packing_box_id')
                ->take($var['qty'])
                ->toArray();

            $box_ids = array_merge($box_ids, $available_box_ids);
        }

        $prices = DB::table('inventory_prices')
            ->select('design_id', 'color_id', DB::raw('MAX(selling_price) as selling_price'), DB::raw('MAX(mrp) as mrp'))
            ->groupBy('design_id', 'color_id');

        $items_to_snapshot = DomesticInventory::whereIn('packing_box_id', $box_ids)
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.design_id')
                    ->on('domestic_inventories.color_id', '=', 'ip.color_id');
            })
            ->select('domestic_inventories.*', DB::raw('COALESCE(ip.selling_price, 0) as master_selling_price'), DB::raw('COALESCE(ip.mrp, 0) as master_mrp'))
            ->get();

        $total_qty = $items_to_snapshot->sum('quantity');
        $total_amount = $items_to_snapshot->sum(function ($item) {
            return $item->quantity * $item->master_selling_price;
        });

        $discount_percentage = $request->has('discount_percentage') ? $request->discount_percentage : $order->discount_percentage;

        // Recalculate totals
        $discount_amount = $total_amount * ($discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;

        // GST percentage remains what was set at order creation unless we want to update it to current settings? 
        // Logic: Keep existing GST % unless admin edits it. Agent edit might technically update it if we don't pass it.
        // For agent edit, let's keep the gst_percentage from the order itself.
        $gst_percentage = $order->gst_percentage;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount;

        DB::beginTransaction();
        try {
            $order->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'updated_at' => now()
            ]);
            AgentOrderItem::where('agent_order_id', $order->id)->delete();

            foreach ($items_to_snapshot as $item) {
                AgentOrderItem::create([
                    'agent_order_id' => $order->id,
                    'packing_box_id' => $item->packing_box_id,
                    'box_no' => $item->box_no,
                    'carton_no' => $item->carton_no,
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_id' => $item->size_id,
                    'size_set_id' => $item->size_set_id,
                    'product_name' => $item->product_name,
                    'design_number' => $item->design_number,
                    'color_name' => $item->color_name,
                    'size_name' => $item->size_name,
                    'size_set_name' => $item->size_set_name,
                    'quantity' => $item->quantity,
                    'mrp' => $item->master_mrp,
                    'selling_price' => $item->master_selling_price,
                    'barcode' => $item->barcode,
                    'qrcode' => $item->qrcode,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order updated successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

    // Retain existing methods for order history
    public function myOrders()
    {
        $orders = AgentOrder::where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->with('shop')
            ->latest()
            ->paginate(10);
        return view('sales_agent.orders.index', compact('orders'));
    }

    public function orderDetails($id)
    {
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->with(['shop', 'items', 'agent'])
            ->firstOrFail();

        // Group items for display if needed, or just pass them
        $items = $order->items;

        return view('sales_agent.orders.show', compact('order', 'items'));
    }

    public function downloadInvoice($id)
    {
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->firstOrFail();

        // Convert model to object with expected properties for view compatibility
        $orderData = (object) [
            'id' => $order->id,
            'order_date' => $order->order_date,
            'status' => $order->status,
            'total_qty' => $order->total_qty,
            'total_amount' => $order->total_amount,
            'discount_percentage' => $order->discount_percentage ?? 0,
            'discount_amount' => $order->discount_amount ?? 0,
            'gst_percentage' => $order->gst_percentage ?? 0,
            'gst_amount' => $order->gst_amount ?? 0,
            'grand_total' => $order->grand_total ?? $order->total_amount,
            'agent_name' => $order->agent->name ?? 'Sales Agent',
            'shop_name' => $order->shop->name ?? 'N/A',
            'shop_email' => $order->shop->email ?? '',
            'shop_phone' => $order->shop->phone ?? '',
            'shop_address' => $order->shop->address ?? '',
        ];

        $items = $order->items;
        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.invoice-pdf', [
            'order' => $orderData,
            'items' => $items,
            'settings' => $settings
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-ORD-' . $id . '.pdf');
    }
}
