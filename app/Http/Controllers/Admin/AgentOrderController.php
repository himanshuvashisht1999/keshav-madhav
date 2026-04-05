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
    public function create(Request $request)
    {
        $agent_id = $request->get('sales_agent_id');
        $shop_id = $request->get('master_customer_id');

        if (!$agent_id || !$shop_id) {
            $agents = DB::table('sales_agents')->select('id', 'name')->where('status', 1)->get();
            $shops = collect(); // Load dynamically via AJAX
            return view('admin.agent_orders.create', compact('agents', 'shops'));
        }

        if ($agent_id === 'direct') {
            $agent = (object) ['id' => 'direct', 'name' => 'Direct (No Agent)', 'discount_percentage' => 0];
        } else {
            $agent = DB::table('sales_agents')->where('id', $agent_id)->first();
        }
        $shop = DB::table('master_customers')->where('id', $shop_id)->first();

        if (!$agent || !$shop) {
            return redirect()->route('admin.agent-orders.create')->with('error', 'Invalid Agent or Shop selected.');
        }

        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->whereNotNull('box_no')->distinct()->pluck('production_goods.design_number');
        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_colors.name');
        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_size_measurements.name');

        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)->whereNotNull('box_no')
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoin('master_product_fittings', 'domestic_inventories.fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_design_patterns', 'domestic_inventories.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        // Add brand-based discount join
        if ($agent_id === 'direct') {
            $query->leftJoin('customer_brand_discounts', function ($join) use ($shop_id) {
                $join->on('production_goods.brand_id', '=', 'customer_brand_discounts.brand_id')
                    ->where('customer_brand_discounts.customer_id', '=', $shop_id);
            });
            $discount_col = 'COALESCE(customer_brand_discounts.discount_percentage, 0)';
        } else {
            $query->leftJoin('sales_agent_brand_discounts', function ($join) use ($agent_id) {
                $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                    ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
            });
            $discount_col = 'COALESCE(sales_agent_brand_discounts.discount_percentage, 0)';
        }

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
            'master_product_fittings.name as fitting_name',
            'master_design_patterns.name as pattern_name',
            'domestic_inventories.fitting_id',
            'domestic_inventories.pattern_id',
            DB::raw('COUNT(DISTINCT domestic_inventories.box_no) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.box_no) as pcs_per_box'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
        )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'master_colors.name',
                'master_size_measurements.name',
                'master_product_fittings.name',
                'master_design_patterns.name',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                DB::raw($discount_col)
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

        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        return view('admin.agent_orders.create', compact('agent', 'shop', 'designs', 'colors', 'size_sets', 'boxes', 'boxImages', 'gst_percentage'));
    }

    public function store(Request $request)
    {
        // Check if it's the Step 1 form or the Step 2 AJAX
        if (!$request->ajax()) {
            $request->validate([
                'sales_agent_id' => 'required',
                'master_customer_id' => 'required|exists:master_customers,id',
                'order_date' => 'required|date',
            ]);
            return redirect()->route('admin.agent-orders.create', [
                'sales_agent_id' => $request->sales_agent_id,
                'master_customer_id' => $request->master_customer_id,
                'order_date' => $request->order_date
            ]);
        }

        // AJAX Store Logic (matching Agent type)
        $request->validate([
            'sales_agent_id' => 'required',
            'master_customer_id' => 'required|exists:master_customers,id',
            'variations' => 'required|array|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->sales_agent_id === 'direct') {
            $shop = DB::table('master_customers')->where('id', $request->master_customer_id)->first();
            $agent_id_to_save = null;
        } else {
            $agent = DB::table('sales_agents')->where('id', $request->sales_agent_id)->first();
            $agent_id_to_save = $request->sales_agent_id;
        }

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];

        foreach ($request->variations as $var) {
            $product = \App\Models\ProductionGoods::with('series', 'brand')->find($var['product_id']);
            $color = \App\Models\MasterColor::find($var['color_id']);
            $sizeSet = \App\Models\MasterSizeMeasurement::find($var['size_set_id']);

            if (!$product || !$color || !$sizeSet)
                continue;

            // Fetch Brand-based Discount
            $brand_discount = 0;
            if ($request->sales_agent_id === 'direct') {
                $brand_discount = DB::table('customer_brand_discounts')
                    ->where('customer_id', $request->master_customer_id)
                    ->where('brand_id', $product->brand_id)
                    ->value('discount_percentage') ?? 0;
            } else {
                $brand_discount = DB::table('sales_agent_brand_discounts')
                    ->where('sales_agent_id', $request->sales_agent_id)
                    ->where('brand_id', $product->brand_id)
                    ->value('discount_percentage') ?? 0;
            }

            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $var['product_id'])
                ->where('master_size_measurement_id', $var['size_set_id'])
                ->first();

            $mrp = $variant->mrp ?? 0;
            $selling_price = $mrp - ($mrp * $brand_discount / 100);

            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
            $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

            $avg_qty = (float) DomesticInventory::where('status', 1)->where('product_id', $var['product_id'])
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
                    'fitting_name' => $fitting->name ?? null,
                    'pattern_id' => $product->master_pattern_id,
                    'pattern_name' => $pattern->name ?? null,
                    'quantity' => $avg_qty,
                    'mrp' => $mrp,
                    'selling_price' => $selling_price,
                    'packing_box_id' => null,
                ];
                $total_qty += $avg_qty;
                $total_amount += ($avg_qty * $selling_price);
            }
        }

        $gst_percentage = $request->gst_percentage ?? 5.00;
        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_amount * $discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount;

        DB::beginTransaction();
        try {
            $order = AgentOrder::create([
                'sales_agent_id' => $request->sales_agent_id === 'direct' ? 0 : $request->sales_agent_id,
                'master_customer_id' => $request->master_customer_id,
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'status' => 'pending',
                'order_date' => $request->order_date ?? date('Y-m-d'),
                'created_by' => Auth::id(),
            ]);

            foreach ($items_to_create as $item) {
                $item['agent_order_id'] = $order->id;
                AgentOrderItem::create($item);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order created successfully!', 'redirect_url' => route('admin.agent-orders.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    public function getShops(Request $request)
    {
        $agent_id = $request->get('agent_id');
        $is_direct = $request->get('is_direct');

        $query = DB::table('master_customers')
            ->select('id', 'name')
            ->where('status', 1);

        if ($is_direct == 1 || $agent_id === 'direct') {
            $query->where('subtype', 'direct');
        } elseif (!empty($agent_id)) {
            $query->where('sales_agent_id', $agent_id);
        }

        $shops = $query->get();
        return response()->json($shops);
    }

    public function index(Request $request)
    {
        $query = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                'master_customers.name as shop_name',
                DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") as total_paid')
            );

        // Filtering
        if ($request->filled('agent_id')) {
            if ($request->agent_id === 'direct') {
                $query->where(function ($q) {
                    $q->whereNull('agent_orders.sales_agent_id')
                        ->orWhere('agent_orders.sales_agent_id', 'direct');
                });
            } else {
                $query->where('agent_orders.sales_agent_id', $request->agent_id);
            }
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
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                'master_customers.name as shop_name',
                'master_customers.email as shop_email',
                'master_customers.phone as shop_phone',
                'master_customers.address as shop_address',
                DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") as total_paid')
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')
            ->get()
            ->groupBy(function ($item) {
                $dispatchStatus = $item->dispatched_at ? 'dispatched' : ($item->box_no ? 'scanned' : 'pending');
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price . '_' . $dispatchStatus;
            })->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'product_name' => $first->product_name,
                    'design_number' => $first->design_number,
                    'color_name' => $first->color_name,
                    'size_set_name' => $first->size_set_name,
                    'fitting_name' => $first->db_fitting_name ?? $first->fitting_name,
                    'pattern_name' => $first->db_pattern_name ?? $first->pattern_name,
                    'mrp' => $first->mrp,
                    'selling_price' => $first->selling_price,
                    'total_qty' => $group->sum('quantity'),
                    'box_count' => $group->count(),
                    'status' => $first->dispatched_at ? 'Dispatched' : ($first->box_no ? 'Scanned' : 'Pending'),
                    'box_nos' => $group->pluck('box_no')->filter()->values()->toArray()
                ];
            })->values();

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
        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->whereNotNull('box_no')->distinct()->pluck('production_goods.design_number');
        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_colors.name');
        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->whereNotNull('box_no')->distinct()->pluck('master_size_measurements.name');

        // Build Query for All Boxes
        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)->whereNotNull('box_no')
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        // Add brand-based discount join
        if ($order->sales_agent_id === 'direct' || empty($order->sales_agent_id)) {
            $query->leftJoin('customer_brand_discounts', function ($join) use ($shop) {
                $join->on('production_goods.brand_id', '=', 'customer_brand_discounts.brand_id')
                    ->where('customer_brand_discounts.customer_id', '=', $shop->id);
            });
            $discount_col = 'COALESCE(customer_brand_discounts.discount_percentage, 0)';
        } else {
            $query->leftJoin('sales_agent_brand_discounts', function ($join) use ($order) {
                $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                    ->where('sales_agent_brand_discounts.sales_agent_id', '=', $order->sales_agent_id);
            });
            $discount_col = 'COALESCE(sales_agent_brand_discounts.discount_percentage, 0)';
        }

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
            'domestic_inventories.fitting_id',
            'domestic_inventories.pattern_id',
            DB::raw('COUNT(DISTINCT domestic_inventories.box_no) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.box_no) as pcs_per_box'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
        )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'master_colors.name',
                'master_size_measurements.name',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                DB::raw($discount_col)
            )
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

            $product = \App\Models\ProductionGoods::with('series', 'brand')->find($var['product_id']);
            $color = \App\Models\MasterColor::find($var['color_id']);
            $sizeSet = \App\Models\MasterSizeMeasurement::find($var['size_set_id']);

            if (!$product || !$color || !$sizeSet)
                continue;

            // Fetch Brand-based Discount
            $brand_discount = 0;
            if ($order->sales_agent_id === 'direct') {
                $brand_discount = DB::table('customer_brand_discounts')
                    ->where('customer_id', $order->master_customer_id)
                    ->where('brand_id', $product->brand_id)
                    ->value('discount_percentage') ?? 0;
            } else {
                $brand_discount = DB::table('sales_agent_brand_discounts')
                    ->where('sales_agent_id', $order->sales_agent_id)
                    ->where('brand_id', $product->brand_id)
                    ->value('discount_percentage') ?? 0;
            }

            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $var['product_id'])
                ->where('master_size_measurement_id', $var['size_set_id'])
                ->first();

            $mrp = $variant->mrp ?? 0;
            $selling_price = $mrp - ($mrp * $brand_discount / 100);

            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
            $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

            $avg_qty = (float) DomesticInventory::where('status', 1)->where('product_id', $var['product_id'])
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
                    'fitting_name' => $fitting->name ?? null,
                    'pattern_id' => $product->master_pattern_id,
                    'pattern_name' => $pattern->name ?? null,
                    'quantity' => $avg_qty,
                    'mrp' => $mrp,
                    'selling_price' => $selling_price,
                    'packing_box_id' => null,
                ];
                $total_qty += $avg_qty;
                $total_amount += ($avg_qty * $selling_price);
            }
        }

        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_amount * $discount_percentage / 100);
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
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                'master_customers.name as shop_name',
                'master_customers.email as shop_email',
                'master_customers.phone as shop_phone',
                'master_customers.address as shop_address'
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')
            ->get()
            ->groupBy(function ($item) {
                $dispatchStatus = $item->dispatched_at ? 'Dispatched' : ($item->box_no ? 'Scanned' : 'Pending');
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price . '_' . $dispatchStatus;
            })->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'product_name' => $first->product_name,
                    'design_number' => $first->design_number,
                    'color_name' => $first->color_name,
                    'size_set_name' => $first->size_set_name,
                    'fitting_name' => $first->db_fitting_name ?? $first->fitting_name,
                    'pattern_name' => $first->db_pattern_name ?? $first->pattern_name,
                    'mrp' => $first->mrp,
                    'selling_price' => $first->selling_price,
                    'total_qty' => $group->sum('quantity'),
                    'box_count' => $group->count(),
                    'status' => $first->dispatched_at ? 'Dispatched' : ($first->box_no ? 'Scanned' : 'Pending')
                ];
            })->values();
        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.invoice-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-ORD-' . $id . '.pdf');
    }

    public function downloadPackingSlip($id)
    {
        $order = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                'master_customers.name as shop_name',
                'master_customers.email as shop_email',
                'master_customers.phone as shop_phone',
                'master_customers.address as shop_address'
            )
            ->first();

        if (!$order)
            abort(404);

        $items = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNotNull('box_no')
            ->select('agent_order_items.*')
            ->get()
            ->groupBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->selling_price;
            })->map(function ($group) {
                $first = $group->first();
                // Estimate Box Qty based on average (since these are orders, not actual boxes yet, but user example shows Box Qty)
                // If the items have box_no, we can group by box_no.
                // Looking at the user example,SJ-2116 CARGO-1 22*30 50.00 PCs Qty, 10.00 BOX Qty.
                // This means Box Qty = PCs Qty / Pcs per Box.
                // In this system, agent_order_items are basically "expected items". 
                // However, if they have quantity, we can sum them.
    
                $total_pcs = $group->sum('quantity');
                $box_count = $group->count(); // In create/store, one entry was created for each box requested.
    
                return (object) [
                    'description' => $first->product_name . ' ' . $first->size_set_name . ' ' . $first->color_name,
                    'pcs_qty' => $total_pcs,
                    'box_qty' => $box_count,
                    'unit' => 'BOX'
                ];
            })->values();

        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.packing-slip-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('PackingSlip-ORD-' . $id . '.pdf');
    }

    public function dispatchOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = DB::table('agent_orders')->where('id', $id)->first();
            if ($order->status == 'dispatched') {
                return redirect()->back()->with('error', 'Order already fully dispatched');
            }

            // Get items that are scanned but not yet dispatched
            $itemsToDispatch = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->whereNotNull('box_no')
                ->whereNull('dispatched_at')
                ->get();

            if ($itemsToDispatch->isEmpty()) {
                return redirect()->back()->with('error', 'No scanned items available for dispatch.');
            }

            $box_nos = $itemsToDispatch->pluck('box_no');

            // 1. Calculate Dispatch Amount (including GST)
            $dispatchSubtotal = 0;
            foreach ($itemsToDispatch as $item) {
                $dispatchSubtotal += ($item->quantity * $item->selling_price);
            }
            $gstPercentage = $order->gst_percentage ?? 0;
            $dispatchGst = $dispatchSubtotal * ($gstPercentage / 100);
            $dispatchTotal = $dispatchSubtotal + $dispatchGst;

            // 2. Update MasterCustomer Balance (Subtract because they now owe this amount)
            $customer = \App\Models\MasterCustomer::find($order->master_customer_id);
            if ($customer) {
                $customer->balance -= $dispatchTotal;
                $customer->save();
            }

            // 3. Delete boxes from DomesticInventory (remove from stock)
            DB::table('domestic_inventories')->whereIn('box_no', $box_nos)->delete();

            // 4. Mark items as dispatched
            DB::table('agent_order_items')
                ->whereIn('id', $itemsToDispatch->pluck('id'))
                ->update(['dispatched_at' => now()]);

            // 5. Update order status
            $remainingItems = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->whereNull('dispatched_at')
                ->count();

            $newStatus = ($remainingItems > 0) ? 'partially_dispatched' : 'dispatched';

            DB::table('agent_orders')->where('id', $id)->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            DB::commit();
            $msg = ($newStatus == 'dispatched') ? 'Order fully dispatched.' : 'Partial dispatch successful.';
            return redirect()->route('admin.agent-orders.index')->with('success', $msg . ' Inventory updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Dispatch failed: ' . $e->getMessage());
        }
    }

    public function dispatchScan($id)
    {
        $order = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                'master_customers.name as shop_name'
            )
            ->first();

        if (!$order)
            abort(404);

        // Only show items that are NOT yet dispatched
        $items = DB::table('agent_order_items')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->whereNull('dispatched_at')
            ->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')
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
                    'pattern_name' => $item->db_pattern_name ?? $item->pattern_name,
                    'fitting_name' => $item->db_fitting_name ?? $item->fitting_name,
                    'required' => 0,
                    'scanned' => 0,
                    'items' => []
                ];
            }
            $groupedItems[$key]['required']++;
            if ($item->box_no) {
                $groupedItems[$key]['scanned']++;
            }
            $groupedItems[$key]['items'][] = $item;
        }

        // Only show scanned boxes that are NOT yet dispatched
        $scannedBoxes = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNotNull('box_no')
            ->whereNull('dispatched_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.agent_orders.dispatch_scan', compact('order', 'groupedItems', 'scannedBoxes'));
    }

    public function processScan(Request $request, $id)
    {
        $input = trim($request->barcode);
        // Remove invisible control characters that some scanners might include
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        if (empty($input)) {
            return response()->json(['success' => false, 'message' => 'No barcode received.']);
        }

        $product_id = null;
        $color_id = null;
        $size_set_id = null;
        $availableBox = null;

        // Detect if barcode is the type-barcode format: D{design_id}-S{size_set_id}-C{color_id}-P{pattern_id}-F{fitting_id}
        // Support both old hyphenated format D1-S1-C1-P1-F1 and new compact format D1S1C1P1F1
        if (preg_match('/^D(\d+)[\-]?S(\d+)[\-]?C(\d+)[\-]?P(\d+)[\-]?F(\d+)$/i', $input, $matches)) {
            // --- TYPE BARCODE SCAN (shared barcode from barcode generator) ---
            $product_id = (int) $matches[1]; // design_id = product_id in domestic_inventories
            $size_set_id = (int) $matches[2];
            $color_id = (int) $matches[3];
            $pattern_id = (int) $matches[4];
            $fitting_id = (int) $matches[5];

            // Get all box_nos globally assigned to any order
            $globallyAssigned = DB::table('agent_order_items')
                ->whereNotNull('box_no')
                ->pluck('box_no')
                ->toArray();

            // Find an available unassigned box of this type
            $availableBox = DB::table('domestic_inventories')
                ->where('product_id', $product_id)
                ->where('color_id', $color_id)
                ->where('size_set_id', $size_set_id)
                ->where('pattern_id', $pattern_id)
                ->where('fitting_id', $fitting_id)
                ->whereNotNull('box_no')
                ->whereNotIn('box_no', $globallyAssigned)
                ->first();

            if (!$availableBox) {
                return response()->json(['success' => false, 'message' => 'No available unassigned boxes in inventory for this type: ' . $input]);
            }

        } else {
            // --- UNIQUE BOX BARCODE / BOX_NO SCAN ---
            $box = DB::table('domestic_inventories')
                ->where(function ($q) use ($input) {
                    $q->where('barcode', $input)
                        ->orWhere('qrcode', $input)
                        ->orWhere('box_no', $input);
                })
                ->whereNotNull('box_no')
                ->first();

            if (!$box) {
                return response()->json(['success' => false, 'message' => 'Box not found in inventory for code: ' . $input]);
            }

            // Check this specific box is not already assigned
            $alreadyAssigned = DB::table('agent_order_items')
                ->where('box_no', $box->box_no)
                ->exists();

            if ($alreadyAssigned) {
                return response()->json(['success' => false, 'message' => 'Box #' . $box->box_no . ' is already assigned to an order.']);
            }

            $product_id = $box->product_id;
            $color_id = $box->color_id;
            $size_set_id = $box->size_set_id;
            $pattern_id = $box->pattern_id;
            $fitting_id = $box->fitting_id;
            $availableBox = $box;
        }

        // Find a pending (unassigned) order item for this product type in THIS order
        $pendingItem = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNull('box_no')
            ->whereNull('dispatched_at')
            ->where('product_id', $product_id)
            ->where('color_id', $color_id)
            ->where('size_set_id', $size_set_id)
            ->where('pattern_id', $pattern_id)
            ->where('fitting_id', $fitting_id)
            ->first();

        if (!$pendingItem) {
            return response()->json(['success' => false, 'message' => 'No pending undispatched slot found for this type in this order.']);
        }

        DB::beginTransaction();
        try {
            DB::table('agent_order_items')->where('id', $pendingItem->id)->update([
                'packing_box_id' => $availableBox->packing_box_id,
                'box_no' => $availableBox->box_no,
                'carton_no' => $availableBox->carton_no,
                'quantity' => $availableBox->quantity,
                'barcode' => $availableBox->barcode,
                'qrcode' => $availableBox->qrcode,
                'updated_at' => now()
            ]);

            // Sync order totals based on actual scanned quantities
            $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();
            $order_row = DB::table('agent_orders')->where('id', $id)->first();
            $total_qty = $items->sum('quantity');
            $total_amount = $items->sum(fn($item) => (float) $item->quantity * (float) $item->selling_price);

            $discount_percentage = $order_row->discount_percentage ?? 0;
            $discount_amount = ($total_amount * $discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_percentage = $order_row->gst_percentage ?? 5.00;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
            $grand_total = $taxable_amount + $gst_amount;

            DB::table('agent_orders')->where('id', $id)->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Box #' . $availableBox->box_no . ' assigned successfully!',
                'variation_key' => $product_id . '_' . $color_id . '_' . $size_set_id,
                'box_no' => $availableBox->box_no,
                'packing_box_id' => $availableBox->packing_box_id ?? null,
                'product_name' => $pendingItem->product_name,
                'design_number' => $pendingItem->design_number,
                'quantity' => $availableBox->quantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function removeScan(Request $request, $id)
    {
        $box_no = $request->box_no;

        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('box_no', $box_no)
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
            $order_row = DB::table('agent_orders')->where('id', $id)->first();
            $total_qty = $items->sum('quantity');
            $total_amount = $items->sum(function ($i) {
                return (float) $i->quantity * (float) $i->selling_price;
            });

            $discount_percentage = $order_row->discount_percentage ?? 0;
            $discount_amount = ($total_amount * $discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_percentage = $order_row->gst_percentage ?? 5.00;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
            $grand_total = $taxable_amount + $gst_amount;

            DB::table('agent_orders')->where('id', $id)->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
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
