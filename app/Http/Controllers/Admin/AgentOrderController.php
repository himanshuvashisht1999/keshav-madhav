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
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->distinct()->pluck('production_goods.design_number');

        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, " "), " ", production_goods.name_of_garment))) as full_name'))
            ->pluck('full_name');

        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->distinct()->pluck('master_colors.name');

        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->distinct()->pluck('master_size_measurements.name');

        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        // Subquery for globally allocated boxes in PENDING agent orders
        $allocated = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->select(
                'product_id', 'color_id', 'size_set_id', 
                DB::raw('SUM(box_qty) as total_allocated')
            )
            ->groupBy('product_id', 'color_id', 'size_set_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
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
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", production_goods.name_of_garment))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }

        $query->leftJoinSub($allocated, 'alloc', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'alloc.product_id')
                    ->on('domestic_inventories.color_id', '=', 'alloc.color_id')
                    ->on('domestic_inventories.size_set_id', '=', 'alloc.size_set_id');
            })
            ->select(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'production_goods.name_of_garment',
                'master_series.name as series_name',
                'master_colors.name as color_name',
                'master_size_measurements.name as size_set_name',
                'master_product_fittings.name as fitting_name',
                'master_design_patterns.name as pattern_name',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
            );

        $boxes = $query->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'production_goods.name_of_garment',
                'master_series.name',
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
            ->paginate(20)
            ->appends($request->except('page'));

        // Fetch images for the boxes
        $boxImages = [];
        foreach ($boxes as $variation) {
            // Find variant ID first (Product + Size Set)
            $variant = DB::table('production_goods_variants')
                ->where('production_goods_id', $variation->product_id)
                ->where('master_size_measurement_id', $variation->size_set_id)
                ->first();

            $image = null;
            if ($variant) {
                // Try to get color-specific image first
                $image = DB::table('production_goods_variant_colors')
                    ->where('variant_id', $variant->id)
                    ->where('master_color_id', $variation->color_id)
                    ->value('image');

                // Fallback to size-set-wide image if color image is missing
                if (!$image) {
                    $image = $variant->image;
                }
            }

            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;

            // Deduct pending ordered stock (Available-To-Promise logic)
            $agentOrderBoxes = \App\Models\AgentOrderItem::whereHas('order', function ($q) {
                $q->where('status', '!=', 'dispatched');
            })
            ->where('product_id', $variation->product_id)
            ->where('color_id', $variation->color_id)
            ->where('size_set_id', $variation->size_set_id)
            ->where(function($q) use ($variation) {
                if ($variation->fitting_id) {
                    $q->where('fitting_id', $variation->fitting_id);
                } else {
                    $q->whereNull('fitting_id');
                }
            })
            ->where(function($q) use ($variation) {
                if ($variation->pattern_id) {
                    $q->where('pattern_id', $variation->pattern_id);
                } else {
                    $q->whereNull('pattern_id');
                }
            })
            ->sum(DB::raw('box_qty - IFNULL(scanned_box_qty, 0)'));

            $variation->available_boxes = max(0, $variation->available_boxes - $agentOrderBoxes);
        }

        // Filter out variations with 0 available boxes from the paginated collection
        $filteredCollection = $boxes->getCollection()->filter(function ($variation) {
            return $variation->available_boxes > 0;
        })->values();
        $boxes->setCollection($filteredCollection);

        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        if ($request->ajax() && $request->has('load_more')) {
            $html = "";
            foreach ($boxes as $variation) {
                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                $image = $boxImages[$vKey] ?? null;
                $html .= view('admin.agent_orders.partials.variation_row', compact('variation', 'vKey', 'image'))->render();
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        return view('admin.agent_orders.create', compact('agent', 'shop', 'designs', 'product_names', 'colors', 'size_sets', 'boxes', 'boxImages', 'gst_percentage'));
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
            
            // Use unit_price from front-end if available, else calculate
            $selling_price = isset($var['unit_price']) ? (float)$var['unit_price'] : ($mrp - ($mrp * $brand_discount / 100));

            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
            $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

            // Determine PCS per Box (Source of Truth: Front-end > Current Inventory > Master Config)
            $pcs_per_box = isset($var['pcs_per_box']) ? (float)$var['pcs_per_box'] : 0;
            if ($pcs_per_box <= 0) {
                $pcs_per_box = (float) DomesticInventory::where('product_id', $var['product_id'])
                    ->where('color_id', $var['color_id'])
                    ->where('size_set_id', $var['size_set_id'])
                    ->avg('quantity') ?? 0;
            }
            if ($pcs_per_box <= 0) {
                $pcs_per_box = (float) ($sizeSet->total_pieces ?? 0);
            }

            $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'] . 'P' . ($product->master_pattern_id ?? 0) . 'F' . ($product->master_product_fitting_id ?? 0);

            $total_pcs = $var['qty'] * $pcs_per_box;

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
                'quantity' => $total_pcs,
                'box_qty' => $var['qty'],
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'barcode' => $barcode,
                'packing_box_id' => null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
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
                DB::raw('(SELECT SUM(box_qty) FROM agent_order_items WHERE agent_order_id = agent_orders.id) as total_boxes'),
                DB::raw('(SELECT SUM(scanned_box_qty) FROM agent_order_items WHERE agent_order_id = agent_orders.id) as scanned_count'),
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
        $order = AgentOrder::with(['shop', 'agent', 'dispatches'])->findOrFail($id);

        // Map expected attributes for standard view usage
        $order->agent_name = $order->agent->name ?? "Direct (No Agent)";
        $order->shop_name = $order->shop->name ?? "N/A";
        $order->shop_email = $order->shop->email ?? "";
        $order->shop_phone = $order->shop->phone ?? "";
        $order->shop_address = $order->shop->address ?? "";
        
        // Sum total paid
        $order->total_paid = DB::table('payments')
            ->where('paymentable_id', $order->id)
            ->where('paymentable_type', 'App\Models\AgentOrder')
            ->sum('amount');

        $items = DB::table('agent_order_items')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'product_name' => $item->product_name,
                    'design_number' => $item->design_number,
                    'color_name' => $item->color_name,
                    'size_set_name' => $item->size_set_name,
                    'fitting_name' => $item->db_fitting_name ?? $item->fitting_name,
                    'pattern_name' => $item->db_pattern_name ?? $item->pattern_name,
                    'mrp' => $item->mrp,
                    'selling_price' => $item->selling_price,
                    'total_qty' => $item->quantity, // Total pieces
                    'box_count' => $item->box_qty ?: 1, // Total boxes
                    'scanned_box_qty' => (int) ($item->scanned_box_qty ?? 0),
                    'status' => $item->dispatched_at ? 'Dispatched' : (($item->scanned_box_qty >= $item->box_qty && $item->box_qty > 0) ? 'Scanned' : ($item->scanned_box_qty > 0 ? 'Partial' : 'Pending')),
                    'box_nos' => $item->box_no ? [$item->box_no] : [],
                    'barcode' => $item->barcode
                ];
            });

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

        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, ""), " ", production_goods.name_of_garment))) as full_name'))
            ->pluck('full_name');

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

        // Subquery for globally allocated boxes in PENDING agent orders
        $allocated = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->where('agent_orders.id', '!=', $order->id) // Exclude CURRENT order
            ->select(
                'product_id', 'color_id', 'size_set_id', 
                DB::raw('SUM(box_qty) as total_allocated')
            )
            ->groupBy('product_id', 'color_id', 'size_set_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->where(function ($q) {
                $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            })
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
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
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", production_goods.name_of_garment))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }
        $boxes = $query->leftJoinSub($allocated, 'alloc', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'alloc.product_id')
                    ->on('domestic_inventories.color_id', '=', 'alloc.color_id')
                    ->on('domestic_inventories.size_set_id', '=', 'alloc.size_set_id');
            })
            ->select(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'production_goods.name_of_garment',
                'master_series.name as series_name',
                'master_colors.name as color_name',
                'master_size_measurements.name as size_set_name',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
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
            ->paginate(20)
            ->appends($request->except('page'));

        // Fetch images for the boxes
        $boxImages = [];
        foreach ($boxes as $variation) {
            // First look for color-specific variant image
            $image = DB::table('production_goods_variant_colors')
                ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                ->where('production_goods_variants.production_goods_id', $variation->product_id)
                ->where('production_goods_variants.master_size_measurement_id', $variation->size_set_id)
                ->where('production_goods_variant_colors.master_color_id', $variation->color_id)
                ->whereNotNull('production_goods_variant_colors.image')
                ->value('production_goods_variant_colors.image');

            // Fallback to variant image if no color-specific image found
            if (!$image) {
                $image = DB::table('production_goods_variants')
                    ->where('production_goods_id', $variation->product_id)
                    ->where('master_size_measurement_id', $variation->size_set_id)
                    ->whereNotNull('image')
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
                DB::raw('SUM(box_qty) as box_count'),
                DB::raw('MAX(quantity / NULLIF(box_qty, 0)) as pcs_per_box'),
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

        if ($request->ajax() && $request->has('load_more')) {
            $html = '';
            foreach ($boxes as $variation) {
                $variant_key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                $image = $boxImages[$variant_key] ?? null;
                $initialQty = $selected_quantities[$variant_key]['qty'] ?? 0;
                $html .= view('admin.agent_orders.partials.variation_row', compact('variation', 'image', 'initialQty'))->render();
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        return view('admin.agent_orders.edit', compact('order', 'shop', 'designs', 'product_names', 'colors', 'size_sets', 'boxes', 'boxImages', 'selected_quantities', 'gst_percentage'));
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
            if ($order->sales_agent_id === 'direct' || empty($order->sales_agent_id)) {
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
            
            // Use unit_price from front-end if available, else calculate
            $selling_price = isset($var['unit_price']) ? (float)$var['unit_price'] : ($mrp - ($mrp * $brand_discount / 100));

            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
            $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

            // Determine PCS per Box (Source of Truth: Front-end > Current Inventory > Master Config)
            $pcs_per_box = isset($var['pcs_per_box']) ? (float)$var['pcs_per_box'] : 0;
            if ($pcs_per_box <= 0) {
                // Check current status 1 (available) inventory
                $pcs_per_box = (float) DomesticInventory::where('status', 1)->where('product_id', $var['product_id'])
                    ->where('color_id', $var['color_id'])
                    ->where('size_set_id', $var['size_set_id'])
                    ->avg('quantity') ?? 0;
            }
            if ($pcs_per_box <= 0) {
                // Fallback to ANY past inventory if available status is 0
                $pcs_per_box = (float) DomesticInventory::where('product_id', $var['product_id'])
                    ->where('color_id', $var['color_id'])
                    ->where('size_set_id', $var['size_set_id'])
                    ->avg('quantity') ?? 0;
            }
            if ($pcs_per_box <= 0) {
                // Final fallback to size measurement master
                $pcs_per_box = (float) ($sizeSet->total_pieces ?? 0);
            }

            $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'] . 'P' . ($product->master_pattern_id ?? 0) . 'F' . ($product->master_product_fitting_id ?? 0);

            $total_pcs = $var['qty'] * $pcs_per_box;

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
                'quantity' => $total_pcs,
                'box_qty' => $var['qty'],
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'barcode' => $barcode,
                'packing_box_id' => null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
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
                $item['agent_order_id'] = $order->id;
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

    public function downloadInvoice(Request $request, $id)
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

        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $itemsRaw = $query->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')->get();
        $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;

        // Recalculate totals for filtered items
        $filteredSubtotal = 0;
        foreach ($itemsRaw as $item) {
            $filteredSubtotal += ($item->quantity * $item->selling_price);
        }
        $filteredGst = $filteredSubtotal * (($order->gst_percentage ?? 5) / 100);
        $filteredGrandTotal = $filteredSubtotal + $filteredGst;

        $items = $itemsRaw->groupBy(function ($item) {
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

        $pdf = Pdf::loadView('admin.agent_orders.invoice-pdf', compact(
            'order', 'items', 'settings', 'selectedBrand', 'type',
            'filteredSubtotal', 'filteredGst', 'filteredGrandTotal'
        ))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-ORD-' . $id . '.pdf');
    }
    public function downloadOrder($id)
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
                'master_customers.see_price'

            )
            ->first();

        if (!$order) abort(404);

        $itemsRaw = DB::table('agent_order_items')
            ->leftJoin('master_design_patterns', 'agent_order_items.pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'agent_order_items.fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->select('agent_order_items.*', 'master_design_patterns.name as db_pattern_name', 'master_product_fittings.name as db_fitting_name')
            ->get();

        $items = $itemsRaw->groupBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
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
                    'box_count' => $group->sum('box_qty'),
                ];
            })->values();

        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Order_Sheet_ORD_' . $id . '.pdf');
    }

    public function downloadPackingSlip(Request $request, $id)
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

        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_id', $id)
            ->whereNotNull('box_no');

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $itemsRaw = $query->select('agent_order_items.*')->get();
        $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;

        $items = $itemsRaw->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'quantity' => $group->sum('quantity'),
                'box_qty' => $group->sum('box_qty'),
                'carton_no' => $first->carton_no
            ];
        })->values();

        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('admin.agent_orders.packing-slip-pdf', compact('order', 'items', 'settings', 'selectedBrand', 'type'))
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

            // Create Dispatch Log Entry
            $dispatch = \App\Models\AgentOrderDispatch::create([
                'master_customer_id' => $order->master_customer_id,
                'sales_agent_id' => $order->sales_agent_id,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => now(),
                'total_amount' => $dispatchSubtotal,
                'gst_amount' => $dispatchGst,
                'gst_percentage' => $gstPercentage,
                'grand_total' => $dispatchTotal,
            ]);

            // 4. Mark items as dispatched and link to this dispatch session
            DB::table('agent_order_items')
                ->whereIn('id', $itemsToDispatch->pluck('id'))
                ->update([
                    'dispatched_at' => now(),
                    'agent_order_dispatch_id' => $dispatch->id
                ]);

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

            \App\Models\AgentOrderDispatchItem::create([
                'agent_order_dispatch_id' => $dispatch->id,
                'agent_order_id' => $order->id,
            ]);

            DB::commit();
            $msg = ($newStatus == 'dispatched') ? 'Order fully dispatched.' : 'Partial dispatch successful.';
            return redirect()->route('admin.agent-orders.dispatches.show', $dispatch->id)->with('success', $msg . ' Inventory updated and dispatch record created.');

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
                    'barcode' => "D{$item->product_id}S{$item->size_set_id}C{$item->color_id}P{$item->pattern_id}F{$item->fitting_id}",
                    'items' => []
                ];
            }
            $groupedItems[$key]['required'] += $item->box_qty;
            $groupedItems[$key]['scanned'] += $item->scanned_box_qty;
            $groupedItems[$key]['items'][] = $item;
        }

        // Only show scanned boxes that are NOT yet dispatched
        $scannedBoxes = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('scanned_box_qty', '>', 0)
            ->whereNull('dispatched_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.agent_orders.dispatch_scan', compact('order', 'groupedItems', 'scannedBoxes'));
    }

    public function processScan(Request $request, $id)
    {
        $input = trim($request->barcode);
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        if (empty($input)) {
            return response()->json(['success' => false, 'message' => 'No barcode received.']);
        }

        // 1. Find the inventory record by barcode (Inventory is now consolidated)
        // Check if input is a compact barcode (D1S1C1P1F1) or a unique box_no
        $inventory = DB::table('domestic_inventories')
            ->where(function ($q) use ($input) {
                $q->where('barcode', $input)->orWhere('box_no', $input);
            })
            ->where('order_main_id', 0)
            ->where('total_boxes', '>', 0)
            ->first();

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'No available stock found in inventory for: ' . $input]);
        }

        // 2. Find the pending order item for this design
        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('barcode', $inventory->barcode)
            // Still have boxes to scan in this variant row
            ->whereRaw('scanned_box_qty < box_qty')
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'This design is not required for this order OR already fully scanned.']);
        }

        DB::beginTransaction();
        try {
            // Direct decrement in inventory
            DB::table('domestic_inventories')->where('id', $inventory->id)->decrement('total_boxes', 1);

            // Directly update the aggregate order item
            DB::table('agent_order_items')->where('id', $item->id)->update([
                'scanned_box_qty' => $item->scanned_box_qty + 1,
                'scanned_quantity' => $item->scanned_quantity + $inventory->quantity,
                // We'll store the LAST scanned box_no for UI reference, but keep the row aggregated
                'box_no' => $inventory->box_no, 
                'updated_at' => now()
            ]);

            // Sync order totals (scanned items now represent the partial dispatch)
            // Note: In a 'direct change' model, we might want to keep the original total_amount 
            // representing the FULL order, while tracking 'dispatch_value'. 
            // The user said 'why you make it so much complees', so I'll keep the standard total sync.
            $items = DB::table('agent_order_items')->where('agent_order_id', $id)->get();
            $order_row = DB::table('agent_orders')->where('id', $id)->first();
            
            // Wait! For 'Dispatch Scan' UI, it usually shows only WHAT was scanned.
            // But 'total_amount' usually means the order value. 
            // I'll leave the totals as they were to avoid messing up the pricing logic.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Box scanned: ' . $inventory->box_no,
                // Variation key for UI grouping (design_color_size)
                'variation_key' => "{$item->product_id}_{$item->color_id}_{$item->size_set_id}",
                'box_no' => $inventory->box_no,
                'quantity' => $inventory->quantity,
                    'scanned' => $item->scanned_box_qty + 1,
                    'required' => $item->box_qty,
                    'product_name' => $item->product_name,
                    'color_name' => $item->color_name,
                    'barcode' => $item->barcode
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function removeScan(Request $request, $id)
    {
        $barcode = $request->barcode;

        // 1. Find the summary inventory record for this barcode
        $inventory = DB::table('domestic_inventories')
            ->where('barcode', $barcode)
            ->where('order_main_id', 0)
            ->first();

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'Inventory record for this box not found.']);
        }

        // 2. Find the aggregate order item for this design in this order
        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('barcode', $barcode)
            ->where('scanned_box_qty', '>', 0)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'No active scan found for this barcode in this order.']);
        }

        DB::beginTransaction();
        try {
            // Direct increment back to inventory
            DB::table('domestic_inventories')->where('id', $inventory->id)->increment('total_boxes', 1);

            // Decrement the aggregate order item counts
            DB::table('agent_order_items')->where('id', $item->id)->update([
                'scanned_box_qty' => $item->scanned_box_qty - 1,
                'scanned_quantity' => $item->scanned_quantity - $inventory->quantity,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scan removed successfully!',
                'variation_key' => "{$item->product_id}_{$item->color_id}_{$item->size_set_id}",
                'scanned' => $item->scanned_box_qty - 1,
                'required' => $item->box_qty,
                'design_number' => $item->design_number,
                'product_name' => $item->product_name,
                'color_name' => $item->color_name,
                'barcode' => $item->barcode
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function indexDispatches(Request $request)
    {
        $query = \App\Models\AgentOrderDispatch::with(['shop', 'agent'])
            ->latest();

        if ($request->filled('shop_id')) {
            $query->where('master_customer_id', $request->shop_id);
        }

        $dispatches = $query->paginate(20);
        $shops = DB::table('master_customers')->select('id', 'name')->get();

        return view('admin.agent_orders.dispatches.index', compact('dispatches', 'shops'));
    }

    public function dispatchShow($id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'agent', 'orders.items'])->findOrFail($id);
        
        $items = DB::table('agent_order_items')
            ->where('agent_order_dispatch_id', '=', $id)
            ->get();

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->sum('box_qty'),
            ];
        })->values();

        return view('admin.agent_orders.dispatches.show', compact('dispatch', 'groupedItems'));
    }

    public function dispatchSelected(Request $request)
    {
        $orderIds = $request->input('order_ids');
        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'No orders selected.');
        }

        $orders = \App\Models\AgentOrder::whereIn('id', $orderIds)->get();
        
        // Validation: Verify all orders belong to the same shop
        $shopIds = $orders->pluck('master_customer_id')->unique();
        if ($shopIds->count() > 1) {
            return redirect()->back()->with('error', 'Please select orders for the same shop.');
        }

        $firstOrder = $orders->first();

        DB::beginTransaction();
        try {
            // Create dispatch header
            $dispatch = \App\Models\AgentOrderDispatch::create([
                'master_customer_id' => $firstOrder->master_customer_id,
                'sales_agent_id' => $firstOrder->sales_agent_id,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => now(),
            ]);

            $grandTotalSubtotal = 0;
            $grandTotalGst = 0;

            foreach ($orders as $order) {
                if ($order->status == 'dispatched') continue;

                $itemsToDispatch = DB::table('agent_order_items')
                    ->where('agent_order_id', $order->id)
                    ->where('scanned_box_qty', '>', 0)
                    ->whereNull('dispatched_at')
                    ->get();

                if ($itemsToDispatch->isEmpty()) continue;

                // 1. Calculate Dispatch Amount & Handle Split Lines
                $subtotal = 0;
                foreach ($itemsToDispatch as $item) {
                    $scannedPcs = $item->scanned_quantity;
                    $subtotal += ($scannedPcs * $item->selling_price);

                    // If partially scanned, split the row so the remainder stays pending
                    if ($item->scanned_box_qty < $item->box_qty) {
                        // Create remaining pending item
                        DB::table('agent_order_items')->insert([
                            'agent_order_id' => $item->agent_order_id,
                            'product_id' => $item->product_id,
                            'design_number' => $item->design_number,
                            'product_name' => $item->product_name ?? null,
                            'color_id' => $item->color_id,
                            'color_name' => $item->color_name ?? null,
                            'size_set_id' => $item->size_set_id,
                            'fitting_id' => $item->fitting_id,
                            'pattern_id' => $item->pattern_id,
                            'box_qty' => $item->box_qty - $item->scanned_box_qty,
                            'quantity' => $item->quantity - $scannedPcs, // remaining pcs
                            'selling_price' => $item->selling_price,
                            'barcode' => $item->barcode,
                            'scanned_box_qty' => 0,
                            'scanned_quantity' => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // Update current item to exactly what was scanned & mark dispatched
                        DB::table('agent_order_items')->where('id', $item->id)->update([
                            'box_qty' => $item->scanned_box_qty,
                            'quantity' => $scannedPcs,
                            'dispatched_at' => now(),
                            'agent_order_dispatch_id' => $dispatch->id
                        ]);
                    } else {
                        // Fully scanned line item
                        DB::table('agent_order_items')->where('id', $item->id)->update([
                            'dispatched_at' => now(),
                            'agent_order_dispatch_id' => $dispatch->id
                        ]);
                    }
                }
                
                $gst = $subtotal * (($order->gst_percentage ?? 5) / 100);
                
                $grandTotalSubtotal += $subtotal;
                $grandTotalGst += $gst;

                // 2. Update Order Status
                $remainingItems = DB::table('agent_order_items')
                    ->where('agent_order_id', $order->id)
                    ->whereNull('dispatched_at')
                    ->count();

                $order->status = ($remainingItems > 0) ? 'partially_dispatched' : 'dispatched';
                $order->save();

                // 5. Link to Dispatch
                \App\Models\AgentOrderDispatchItem::create([
                    'agent_order_dispatch_id' => $dispatch->id,
                    'agent_order_id' => $order->id
                ]);
            }

            // Update Dispatch Totals
            $dispatch->total_amount = $grandTotalSubtotal;
            $dispatch->gst_amount = $grandTotalGst;
            $dispatch->gst_percentage = $firstOrder->gst_percentage ?? 5;
            $dispatch->grand_total = $grandTotalSubtotal + $grandTotalGst;
            $dispatch->save();

            // Finally: Update Customer Balance
            $customer = \App\Models\MasterCustomer::find($firstOrder->master_customer_id);
            if ($customer) {
                $customer->balance -= $dispatch->grand_total;
                $customer->save();
            }

            DB::commit();
            return redirect()->route('admin.agent-orders.dispatches.show', $dispatch->id)->with('success', 'Multi-order dispatch completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Dispatch failed: ' . $e->getMessage());
        }
    }

    public function downloadDispatchInvoice(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();
        
        $brandId = $request->get('brand_id');
        $type = $request->get('type'); // 'actual' if selected

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();
        $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;

        // Recalculate totals for filtered items
        $filteredSubtotal = 0;
        $uniqueBrandIds = [];
        foreach ($items as $item) {
            $filteredSubtotal += ($item->quantity * $item->selling_price);
            if ($item->brand_id) {
                $uniqueBrandIds[$item->brand_id] = true;
            }
        }
        $brandCount = count($uniqueBrandIds);
        
        $gstPercent = $dispatch->gst_percentage ?? 5;
        $discountAmt = 0;
        
        if (!$brandId) {
            // Full dispatch invoice - use saved values
            $filteredSubtotal = $dispatch->total_amount;
            $discountAmt = $dispatch->discount_amount ?? 0;
            $filteredGst = $dispatch->gst_amount;
            $filteredGrandTotal = $dispatch->grand_total;
        } else {
            // Brand specific - calculate proportionate discount if any? 
            // For now, just use the GST percentage and no extra discount for brand-specific.
            $filteredGst = $filteredSubtotal * ($gstPercent / 100);
            $filteredGrandTotal = $filteredSubtotal + $filteredGst;
        }

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->sum('box_qty'),
            ];
        })->values();

        $pdf = Pdf::loadView('admin.agent_orders.dispatches.invoice-pdf', compact(
            'dispatch', 'groupedItems', 'settings', 'selectedBrand', 'type', 
            'filteredSubtotal', 'filteredGst', 'filteredGrandTotal', 'brandCount', 'discountAmt'
        ));
        return $pdf->download('Dispatch_Invoice_' . $dispatch->id . '.pdf');
    }

    public function updateDispatchInvoice(Request $request, $id)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $dispatch = \App\Models\AgentOrderDispatch::findOrFail($id);
        
        $oldGrandTotal = $dispatch->grand_total;
        
        $total_amount = $request->total_amount;
        $discount_amount = $request->discount_amount ?? 0;
        $gst_percentage = $request->gst_percentage ?? 5;
        
        $taxable_amount = $total_amount - $discount_amount;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grandTotal = $taxable_amount + $gst_amount;

        DB::beginTransaction();
        try {
            $dispatch->update([
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'grand_total' => $grandTotal,
            ]);

            // Adjust Customer Balance
            $customer = \App\Models\MasterCustomer::find($dispatch->master_customer_id);
            if ($customer) {
                // We subtracted $oldGrandTotal before. Add it back and subtract the new one.
                $customer->balance = $customer->balance + $oldGrandTotal - $grandTotal;
                $customer->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Invoice updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update invoice: ' . $e->getMessage()], 500);
        }
    }
    public function downloadDispatchPackingSlip(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();

        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();
        $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;

        $uniqueBrandIds = [];
        foreach ($items as $item) {
            if ($item->brand_id) {
                $uniqueBrandIds[$item->brand_id] = true;
            }
        }
        $brandCount = count($uniqueBrandIds);

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'product_id' => $first->product_id,
                'brand_id' => $first->brand_id,
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'size_set_name' => $first->size_set_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'box_count' => $group->sum('box_qty'),
                'total_qty' => $group->sum('quantity'),
            ];
        })->values();

        $pdf = Pdf::loadView('admin.agent_orders.dispatches.packing-slip-pdf', compact('dispatch', 'groupedItems', 'settings', 'selectedBrand', 'type', 'brandCount'));
        return $pdf->download('Packing_Slip_' . $dispatch->id . '.pdf');
    }
}
