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
        $agent_id = Auth::guard('sales_agent')->id();

        // Fetch Filter Options
        $designs = DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->whereNotNull('box_no')->distinct()->pluck('production_goods.design_number');
        $colors = DomesticInventory::join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_colors.name');
        $size_sets = DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_size_measurements.name');

        // Build Query - Join with production_goods_variants to get mrp
        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $query = DomesticInventory::whereNotNull('box_no')
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            })
            ->leftJoin('sales_agent_brand_discounts', function($join) use ($agent_id) {
                $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                     ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
            });

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }

        // Get distinct variations with aggregated data
        $boxes = $query->select(
            DB::raw('MIN(domestic_inventories.box_no) as example_box_no'),
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'production_goods.design_number',
            'master_colors.name as color_name',
            'master_size_measurements.name as size_set_name',
            DB::raw('COUNT(DISTINCT domestic_inventories.box_no) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.box_no) as pcs_per_box'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - COALESCE(sales_agent_brand_discounts.discount_percentage, 0)) / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
            DB::raw('production_goods.name_of_garment as name')
        )
            ->groupBy(
                'domestic_inventories.product_id', 
                'domestic_inventories.color_id', 
                'domestic_inventories.size_set_id', 
                'production_goods.design_number', 
                'master_colors.name', 
                'master_size_measurements.name',
                'production_goods.name_of_garment',
                'sales_agent_brand_discounts.discount_percentage'
            )
            ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
            ->orderBy('production_goods.design_number')
            ->paginate(50)
            ->appends($request->except('page'));

        // Fetch images for the variations
        $boxImages = [];
        foreach ($boxes as $variation) {
            $image = DB::table('domestic_inventory_images')
                ->where('product_id', $variation->product_id)
                ->where('color_id', $variation->color_id)
                ->where('is_main', 1)
                ->value('image_path');

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
            'expected_dispatch_date' => 'nullable|date|after_or_equal:today',
        ]);

        $agent = Auth::guard('sales_agent')->user();
        $agent_id = $agent->id;
        $shop_id = $request->shop_id;

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];

        foreach ($request->variations as $var) {
            $product = \App\Models\ProductionGoods::with('series', 'brand')->find($var['product_id']);
            $color = \App\Models\MasterColor::find($var['color_id']);
            $sizeSet = \App\Models\MasterSizeMeasurement::find($var['size_set_id']);
            
            if (!$product || !$color || !$sizeSet) continue;

            // Fetch Brand-based Discount
            $brand_discount = DB::table('sales_agent_brand_discounts')
                ->where('sales_agent_id', $agent_id)
                ->where('brand_id', $product->brand_id)
                ->value('discount_percentage') ?? 0;

            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $var['product_id'])
                ->where('master_size_measurement_id', $var['size_set_id'])
                ->first();

            $mrp = $variant->mrp ?? 0;
            $selling_price = $mrp - ($mrp * $brand_discount / 100);
            
            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $avg_qty = (float) DomesticInventory::where('product_id', $var['product_id'])
                ->where('color_id', $var['color_id'])
                ->where('size_set_id', $var['size_set_id'])
                ->avg('quantity') ?? 0;

            for ($i = 0; $i < $var['qty']; $i++) {
                $items_to_create[] = [
                    'product_id' => $var['product_id'],
                    'color_id' => $var['color_id'],
                    'size_set_id' => $var['size_set_id'],
                    'product_name' => $product_name ?: 'N/A',
                    'design_number' => $product->design_number,
                    'color_name' => $color->name,
                    'size_set_name' => $sizeSet->name,
                    'fitting_id' => $product->master_product_fitting_id,
                    'fitting_name' => $product->fitting->name ?? null,
                    'pattern_id' => $product->master_pattern_id,
                    'pattern_name' => $product->pattern->name ?? null,
                    'quantity' => $avg_qty,
                    'mrp' => $mrp,
                    'selling_price' => $selling_price,
                    'packing_box_id' => null,
                ];
                $total_qty += $avg_qty;
                $total_amount += ($avg_qty * $selling_price);
            }
        }

        if (empty($items_to_create)) {
            return response()->json(['success' => false, 'message' => 'No variations found.'], 400);
        }

        $taxable_amount = $total_amount;
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
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'status' => 'pending',
                'order_date' => now(),
            ]);

            foreach ($items_to_create as $item) {
                $item['agent_order_id'] = $order->id;
                AgentOrderItem::create($item);
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
        $agent_id = Auth::guard('sales_agent')->id();
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', $agent_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $shop = $order->shop;

        // Fetch Filter Options
        $designs = DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->whereNotNull('box_no')->distinct()->pluck('production_goods.design_number');
        $colors = DomesticInventory::join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_colors.name');
        $size_sets = DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_size_measurements.name');

        // Build Query for All Boxes
        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $query = DomesticInventory::whereNotNull('box_no')
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            })
            ->leftJoin('sales_agent_brand_discounts', function($join) use ($agent_id) {
                $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                     ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
            });

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }

        $boxes = $query->select(
            DB::raw('MIN(domestic_inventories.box_no) as example_box_no'),
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'production_goods.design_number',
            'master_colors.name as color_name',
            'master_size_measurements.name as size_set_name',
            DB::raw('COUNT(DISTINCT domestic_inventories.box_no) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.box_no) as pcs_per_box'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - COALESCE(sales_agent_brand_discounts.discount_percentage, 0)) / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
        )
            ->groupBy(
                'domestic_inventories.product_id', 
                'domestic_inventories.color_id', 
                'domestic_inventories.size_set_id', 
                'production_goods.design_number', 
                'master_colors.name', 
                'master_size_measurements.name',
                'sales_agent_brand_discounts.discount_percentage'
            )
            ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
            ->orderBy('production_goods.design_number')
            ->paginate(50)
            ->appends($request->except('page'));

        // Fetch images for the boxes
        $boxImages = [];
        foreach ($boxes as $variation) {
            $image = DB::table('domestic_inventory_images')
                ->where('product_id', $variation->product_id)
                ->where('color_id', $variation->color_id)
                ->where('is_main', 1)
                ->value('image_path');
            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;
        }

        // Selected quantities for existing order
        $selected_quantities = AgentOrderItem::where('agent_order_id', $order->id)
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('COUNT(id) as box_count'),
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
            'variations.*.qty' => 'required|integer|min:1',
            'expected_dispatch_date' => 'nullable|date|after_or_equal:today',
        ]);

        $agent = Auth::guard('sales_agent')->user();
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', $agent->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];

        foreach ($request->variations as $var) {
            if ($var['qty'] <= 0) continue;

            $product = \App\Models\ProductionGoods::with('series', 'brand')->find($var['product_id']);
            $color = \App\Models\MasterColor::find($var['color_id']);
            $sizeSet = \App\Models\MasterSizeMeasurement::find($var['size_set_id']);
            
            if (!$product || !$color || !$sizeSet) continue;

            // Fetch Brand-based Discount
            $brand_discount = DB::table('sales_agent_brand_discounts')
                ->where('sales_agent_id', $agent->id)
                ->where('brand_id', $product->brand_id)
                ->value('discount_percentage') ?? 0;

            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $var['product_id'])
                ->where('master_size_measurement_id', $var['size_set_id'])
                ->first();

            $mrp = $variant->mrp ?? 0;
            $selling_price = $mrp - ($mrp * $brand_discount / 100);
            
            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

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
                    'product_name' => $product_name ?: 'N/A',
                    'design_number' => $product->design_number,
                    'color_name' => $color->name,
                    'size_set_name' => $sizeSet->name,
                    'fitting_id' => $product->master_product_fitting_id,
                    'fitting_name' => $product->fitting->name ?? null,
                    'pattern_id' => $product->master_pattern_id,
                    'pattern_name' => $product->pattern->name ?? null,
                    'quantity' => $avg_qty,
                    'mrp' => $mrp,
                    'selling_price' => $selling_price,
                    'packing_box_id' => null,
                ];
                $total_qty += $avg_qty;
                $total_amount += ($avg_qty * $selling_price);
            }
        }

        $taxable_amount = $total_amount;
        $gst_percentage = $order->gst_percentage;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount;

        DB::beginTransaction();
        try {
            $order->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'updated_at' => now()
            ]);
            AgentOrderItem::where('agent_order_id', $order->id)->delete();

            foreach ($items_to_create as $item) {
                AgentOrderItem::create($item);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order updated successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

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

        $items = $order->items;
        $groupedItems = $items->groupBy(function ($item) {
            $status = $item->dispatched_at ? 'dispatched' : 'pending';
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price . '_' . $status;
        })
        ->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'fitting_name' => $first->fitting_name,
                'pattern_name' => $first->pattern_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->count(),
                'status' => $first->dispatched_at ? 'Dispatched' : 'Pending',
                'boxes' => $group
            ];
        });

        return view('sales_agent.orders.show', compact('order', 'groupedItems'));
    }

    public function downloadInvoice($id)
    {
        $order = AgentOrder::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->firstOrFail();

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

        $items = $order->items->groupBy(function ($item) {
            $status = $item->dispatched_at ? 'dispatched' : 'pending';
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price . '_' . $status;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'fitting_name' => $first->fitting_name,
                'pattern_name' => $first->pattern_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->count(),
                'status' => $first->dispatched_at ? 'Dispatched' : 'Pending'
            ];
        })->values();
        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.invoice-pdf', [
            'order' => $orderData,
            'items' => $items,
            'settings' => $settings
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-ORD-' . $id . '.pdf');
    }
}
