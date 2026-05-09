<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;
use App\Models\MasterCustomer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FairProduct;
use App\Models\ProductionGoods;
use App\Models\MasterColor;

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
        $agent = Auth::guard('sales_agent')->user();

        $sale_type = 'item';

        // Fetch Filter Options
        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->distinct()->pluck('production_goods.design_number');

        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, " "), " ", production_goods.name_of_garment))) as full_name'))
            ->pluck('full_name');

        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->distinct()->pluck('master_colors.name');

        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->distinct()->pluck('master_size_measurements.name');

        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        // Subquery for globally allocated boxes in PENDING agent orders
        $allocated = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('SUM(box_qty) as total_allocated')
            )
            ->groupBy('product_id', 'color_id', 'size_set_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
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

        // Brand-based discount join
        $query->leftJoin('sales_agent_brand_discounts', function ($join) use ($agent_id) {
            $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
        });
        $discount_col = 'COALESCE(sales_agent_brand_discounts.discount_percentage, 0)';

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

        $hasFilters = $request->filled('design_number') || 
                      $request->filled('product_name') || 
                      $request->filled('color_name') || 
                      $request->filled('size_set_name');

        if (!$hasFilters && !$request->has('load_more') && !$request->has('page')) {
            $boxes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        } else {
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
                ->havingRaw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0')
                ->orderBy('production_goods.design_number')
                ->paginate(50)
                ->appends($request->except('page'));
        }

        // Fetch images for the boxes
        $boxImages = [];
        foreach ($boxes as $variation) {
            $variant = DB::table('production_goods_variants')
                ->where('production_goods_id', $variation->product_id)
                ->where('master_size_measurement_id', $variation->size_set_id)
                ->first();

            $image = null;
            if ($variant) {
                $image = DB::table('production_goods_variant_colors')
                    ->where('variant_id', $variant->id)
                    ->where('master_color_id', $variation->color_id)
                    ->value('image');

                if (!$image) {
                    $image = $variant->image;
                }
            }

            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;
        }

        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        if ($request->ajax() && $request->has('load_more')) {
            $html = "";
            foreach ($boxes as $variation) {
                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                $image = $boxImages[$vKey] ?? null;
                $html .= '<div class="col-md-4 col-lg-3 mb-3 variation-row-container">' . 
                         view('sales_agent.orders.partials.variation_card', compact('variation', 'vKey', 'image'))->render() . 
                         '</div>';
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        return view('sales_agent.orders.create', compact('shop', 'agent', 'designs', 'product_names', 'colors', 'size_sets', 'boxes', 'boxImages', 'gst_percentage'));
    }

    public function store(Request $request)
    {
        $sale_type = 'item';
        $order_type = 'normal';

        $request->validate([
            'variations' => 'required|array|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $agent = Auth::guard('sales_agent')->user();
        $agent_id = $agent->id;
        $customer = \App\Models\MasterCustomer::findOrFail($request->shop_id);

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

            $selling_price = isset($var['unit_price']) ? (float) $var['unit_price'] : ($mrp - ($mrp * $brand_discount / 100));

            $seriesName = ($product->series) ? $product->series->name : '';
            $product_name = trim($seriesName . ' ' . $product->name_of_garment);

            $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
            $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

            $pcs_per_box = isset($var['pcs_per_box']) ? (float) $var['pcs_per_box'] : 0;
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
                'scanned_box_qty' => $order_type === 'direct' ? $var['qty'] : 0,
                'scanned_quantity' => $order_type === 'direct' ? $total_pcs : 0,
                'dispatched_at' => $order_type === 'direct' ? now() : null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
        }

        $other_charges = $request->other_charges ?? 0;

        if ($request->filled('discount_amount')) {
            $discount_amount = (float) $request->discount_amount;
            $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
        } else {
            $discount_percentage = $request->discount_percentage ?? 0;
            $discount_amount = ($total_amount * $discount_percentage / 100);
        }

        $taxable_amount = $total_amount - $discount_amount;

        if ($request->filled('gst_amount')) {
            $gst_amount = (float) $request->gst_amount;
            $gst_percentage = ($taxable_amount > 0) ? ($gst_amount / $taxable_amount * 100) : 0;
        } else {
            $gst_percentage = $request->gst_percentage ?? 5.00;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
        }

        $grand_total = $taxable_amount + $gst_amount + $other_charges;

        $status = $order_type === 'direct' ? 'dispatched' : 'pending';

        DB::beginTransaction();
        try {
            $order = AgentOrder::create([
                'sales_agent_id' => $agent_id,
                'party_type' => 'customer',
                'master_customer_id' => $request->shop_id,
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'status' => $status,
                'order_type' => $order_type,
                'sale_type' => $sale_type,
                'order_date' => now(),
                'created_by' => Auth::id() ?? 0,
                'remark' => $request->remark,
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
            ]);

            $dispatch = null;
            if ($order_type === 'direct') {
                $dispatch = \App\Models\AgentOrderDispatch::create([
                    'party_type' => 'customer',
                    'master_customer_id' => $request->shop_id,
                    'sales_agent_id' => $agent_id,
                    'status' => 'dispatched',
                    'created_by' => Auth::id() ?? 0,
                    'dispatch_date' => now(),
                    'total_amount' => $total_amount,
                    'discount_amount' => $discount_amount,
                    'gst_amount' => $gst_amount,
                    'gst_percentage' => $gst_percentage,
                    'grand_total' => $grand_total,
                ]);

                \App\Models\AgentOrderDispatchItem::create([
                    'agent_order_dispatch_id' => $dispatch->id,
                    'agent_order_id' => $order->id,
                ]);
            }

            foreach ($items_to_create as $item) {
                $item['agent_order_id'] = $order->id;
                if ($dispatch) {
                    $item['agent_order_dispatch_id'] = $dispatch->id;
                }
                AgentOrderItem::create($item);

                if ($order_type === 'direct') {
                    $inventories = DomesticInventory::where('barcode', $item['barcode'])
                        ->where('total_boxes', '>', 0)
                        ->get();

                    $remainingToDeduct = $item['box_qty'];
                    foreach ($inventories as $inv) {
                        if ($remainingToDeduct <= 0) break;
                        $deduct = min($inv->total_boxes, $remainingToDeduct);

                        \App\Models\DomesticInventoryHistory::create([
                            'domestic_inventory_id' => $inv->id,
                            'user_id' => Auth::id() ?? 0,
                            'type' => 'stock_consume',
                            'old_product_id' => $inv->product_id,
                            'old_color_id' => $inv->color_id,
                            'old_size_set_id' => $inv->size_set_id,
                            'old_fitting_id' => $inv->fitting_id,
                            'old_pattern_id' => $inv->pattern_id,
                            'old_rack_id' => $inv->warehouse_rack_id,
                            'box_quantity' => $deduct,
                        ]);

                        $inv->decrement('total_boxes', $deduct);
                        if ($inv->total_boxes <= 0) $inv->delete();
                        $remainingToDeduct -= $deduct;
                    }
                }
            }

            if ($order_type === 'direct') {
                $customer->decrement('balance', $grand_total);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order created successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
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
        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->distinct()->pluck('production_goods.design_number');
        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->distinct()->pluck('master_colors.name');
        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->distinct()->pluck('master_size_measurements.name');
            
        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, " "), " ", production_goods.name_of_garment))) as full_name'))
            ->pluck('full_name');

        // Build Query for All Boxes
        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        // Subquery for globally allocated boxes in PENDING agent orders
        $allocated = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->where('agent_orders.id', '!=', $id) // EXCLUDE CURRENT ORDER
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
            })
            ->leftJoin('sales_agent_brand_discounts', function($join) use ($agent_id) {
                $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                     ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
            });

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
            });

        $query->select(
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
            DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - COALESCE(sales_agent_brand_discounts.discount_percentage, 0)) / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
        );
        $hasFilters = $request->filled('design_number') || 
                      $request->filled('product_name') || 
                      $request->filled('color_name') || 
                      $request->filled('size_set_name');

        if (!$hasFilters && !$request->has('load_more') && !$request->has('page')) {
            $existingItemVariations = AgentOrderItem::where('agent_order_id', $id)
                ->select('product_id', 'color_id', 'size_set_id')
                ->get();
            
            if ($existingItemVariations->isEmpty()) {
                $boxes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
            } else {
                $query->where(function($q) use ($existingItemVariations) {
                    foreach($existingItemVariations as $v) {
                        $q->orWhere(function($sq) use ($v) {
                            $sq->where('domestic_inventories.product_id', $v->product_id)
                               ->where('domestic_inventories.color_id', $v->color_id)
                               ->where('domestic_inventories.size_set_id', $v->size_set_id);
                        });
                    }
                });
                
                $boxes = $query->groupBy(
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
                    'sales_agent_brand_discounts.discount_percentage'
                )
                    ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
                    ->orderBy('production_goods.design_number')
                    ->paginate(50)
                    ->appends($request->except('page'));
            }
        } else {
            $boxes = $query->groupBy(
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
                'sales_agent_brand_discounts.discount_percentage'
            )
                ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
                ->orderBy('production_goods.design_number')
                ->paginate(50)
                ->appends($request->except('page'));
        }

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

        $filteredCollection = $boxes->getCollection()->filter(function ($variation) {
            return $variation->available_boxes > 0;
        })->values();
        $boxes->setCollection($filteredCollection);

        if ($request->ajax() && $request->has('load_more')) {
            $html = "";
            foreach ($boxes as $variation) {
                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                $image = $boxImages[$vKey] ?? null;
                $html .= '<div class="col-md-4 col-lg-3 mb-3 variation-row-container">' . 
                         view('sales_agent.orders.partials.variation_card', compact('variation', 'vKey', 'image'))->render() . 
                         '</div>';
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        // Selected quantities for existing order
        $selected_quantities = AgentOrderItem::where('agent_order_id', $order->id)
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('SUM(box_qty) as total_boxes'),
                DB::raw('MAX(quantity / box_qty) as pcs_per_box'),
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
                    'qty' => (int) $item->total_boxes,
                    'pcs_per_box' => (float) $item->pcs_per_box,
                    'unit_price' => (float) $item->unit_price
                ];
            })
            ->toArray();

        // Fetch GST setting
        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        return view('sales_agent.orders.edit', compact('shop', 'boxes', 'designs', 'product_names', 'colors', 'size_sets', 'order', 'selected_quantities', 'boxImages', 'gst_percentage'));
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
                'box_qty' => $var['qty'],
                'quantity' => $total_pcs,
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'barcode' => $barcode,
                'packing_box_id' => null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
        }

        $other_charges = $request->other_charges ?? 0;

        if ($request->filled('discount_amount')) {
            $discount_amount = (float) $request->discount_amount;
            $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
        } else {
            $discount_percentage = $request->discount_percentage ?? 0;
            $discount_amount = ($total_amount * $discount_percentage / 100);
        }

        $taxable_amount = $total_amount - $discount_amount;

        if ($request->filled('gst_amount')) {
            $gst_amount = (float) $request->gst_amount;
            $gst_percentage = ($taxable_amount > 0) ? ($gst_amount / $taxable_amount * 100) : 0;
        } else {
            $gst_percentage = $request->gst_percentage ?? 5.00;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
        }

        $grand_total = $taxable_amount + $gst_amount + $other_charges;

        DB::beginTransaction();
        try {
            $order->update([
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'updated_at' => now(),
                'remark' => $request->remark,
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
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

    public function getVariationByBarcode(Request $request)
    {
        try {
            $barcode = $request->get('barcode');
            if (!$barcode) return response()->json(['success' => false, 'message' => 'No barcode provided.']);

            // Format: BX-{{ boxNo }}
            if (strpos($barcode, 'BX-') === 0) {
                $box = DB::table('packing_boxes')->where('box_no', $barcode)->first();
                if ($box && $box->barcode) {
                    $barcode = $box->barcode;
                } else {
                    return response()->json(['success' => false, 'message' => 'Box not found in records.']);
                }
            }

            // Format: D{productId}S{sizeSetId}C{colorId}P{patternId}F{fittingId}
            if (strpos($barcode, 'D') === 0 && strpos($barcode, 'S') !== false) {
                if (preg_match('/^D(\d+)S(\d+)C(\d+)P(\d+)F(\d+)$/', $barcode, $matches)) {
                    $productId = $matches[1];
                    $sizeSetId = $matches[2];
                    $colorId = $matches[3];
                    // pattern and fitting also available in $matches[4] and $matches[5] if needed
                    
                    \Log::info("Scanning Domestic Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $sizeSetId, 'colorId' => $colorId]);

                    $product = \App\Models\ProductionGoods::with(['series', 'variants' => function($q) use ($sizeSetId) {
                        $q->where('master_size_measurement_id', $sizeSetId);
                    }])->find($productId);

                    if (!$product) {
                        return response()->json(['success' => false, 'message' => 'Product not found.']);
                    }

                    // Get available colors from DomesticInventory
                    $availableColors = \App\Models\DomesticInventory::where('product_id', $productId)
                        ->where('size_set_id', $sizeSetId)
                        ->where('domestic_inventories.status', 1)
                        ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                        ->select('master_colors.id', 'master_colors.name', DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'), DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'))
                        ->groupBy('master_colors.id', 'master_colors.name')
                        ->get();

                    $agent_id = Auth::guard('sales_agent')->id();
                    $discount_percentage = DB::table('sales_agent_brand_discounts')
                        ->where('sales_agent_id', $agent_id)
                        ->where('brand_id', $product->brand_id)
                        ->value('discount_percentage') ?? 0;

                    $variant = $product->variants->first();
                    if (!$variant) {
                        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $productId)->first();
                    }

                    $mrp = $variant->mrp ?? 0;
                    $unit_price = $mrp - ($mrp * $discount_percentage / 100);

                    return response()->json([
                        'success' => true,
                        'product' => [
                            'id' => $product->id,
                            'name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
                            'design_number' => $product->design_number,
                            'size_set_id' => (int)$sizeSetId,
                            'size_set_name' => DB::table('master_size_measurements')->where('id', $sizeSetId)->value('name'),
                            'mrp' => $mrp,
                            'unit_price' => $unit_price,
                        ],
                        'colors' => $availableColors
                    ]);
                }
            }

            // Format: FAIR-{{ productId }}-{{ sizeSetId }}-{{ timestamp }} OR F{{ id_base36 }}
            if (strpos($barcode, 'FAIR-') === 0 || preg_match('/^F[A-Z0-9]+$/', $barcode)) {
                $fairProduct = \App\Models\FairProduct::where('barcode', $barcode)->first();
                
                if (strpos($barcode, 'FAIR-') === 0) {
                    $parts = explode('-', $barcode);
                    if (count($parts) < 3) return response()->json(['success' => false, 'message' => 'Invalid Fair barcode format.']);
                    $productId = $parts[1];
                    $sizeSetId = $parts[2];
                } elseif ($fairProduct) {
                    $productId = $fairProduct->product_id;
                    $sizeSetId = $fairProduct->size_set_id;
                } else {
                    return response()->json(['success' => false, 'message' => 'Fair product not found.']);
                }
                
                \Log::info("Scanning Fair Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $sizeSetId]);

                $fairProduct = \App\Models\FairProduct::where('barcode', $barcode)->first();
                
                $product = \App\Models\ProductionGoods::with(['series', 'variants' => function($q) use ($sizeSetId) {
                    $q->where('master_size_measurement_id', $sizeSetId);
                }])->find($productId);

                if (!$product) {
                    \Log::error("Product not found for Fair barcode", ['productId' => $productId]);
                    return response()->json(['success' => false, 'message' => 'Product not found.']);
                }

                // Get available colors from DomesticInventory
                $availableColors = \App\Models\DomesticInventory::where('product_id', $productId)
                    ->where('size_set_id', $sizeSetId)
                    ->where('domestic_inventories.status', 1)
                    ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                    ->select('master_colors.id', 'master_colors.name', DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'), DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'))
                    ->groupBy('master_colors.id', 'master_colors.name')
                    ->get();

                // Discount: Prioritize FairProduct discount if found
                if ($fairProduct) {
                    $discount_percentage = $fairProduct->discount_percent;
                } else {
                    $agent_id = Auth::guard('sales_agent')->id();
                    $discount_percentage = DB::table('sales_agent_brand_discounts')
                        ->where('sales_agent_id', $agent_id)
                        ->where('brand_id', $product->brand_id)
                        ->value('discount_percentage') ?? 0;
                }

                $variant = $product->variants->first();
                if (!$variant) {
                    \Log::warning("No variant found for product and size set", ['productId' => $productId, 'sizeSetId' => $sizeSetId]);
                    // Try to get any variant to at least show a price
                    $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $productId)->first();
                }

                $mrp = $variant->mrp ?? 0;
                $unit_price = $mrp - ($mrp * $discount_percentage / 100);

                return response()->json([
                    'success' => true,
                    'product' => [
                        'id' => $product->id,
                        'name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
                        'design_number' => $product->design_number,
                        'size_set_id' => (int)$sizeSetId,
                        'size_set_name' => DB::table('master_size_measurements')->where('id', $sizeSetId)->value('name'),
                        'mrp' => $mrp,
                        'unit_price' => $unit_price,
                    ],
                    'colors' => $availableColors
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Barcode type not recognized.']);
        } catch (\Exception $e) {
            \Log::error("Error in getVariationByBarcode", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'barcode' => $request->get('barcode')
            ]);
            return response()->json(['success' => false, 'message' => 'Internal server error while fetching details.']);
        }
    }
}
