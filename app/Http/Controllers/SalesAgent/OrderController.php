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
        $party_type = $request->query('party_type', 'customer');
        $agent = Auth::guard('sales_agent')->user();
        $is_master = $agent->is_master_agent;

        if (!$shop_id) {
            if ($is_master) {
                $shops = MasterCustomer::where('status', 1)->get();
                $vendors = \DB::table('vendors')->where('status', 1)->get();
                return view('sales_agent.orders.master_select', compact('shops', 'vendors', 'party_type'));
            }
            return redirect()->route('agent.shops.index')->with('error', 'Please select a shop to create an order.');
        }

        if ($party_type == 'vendor') {
            $shop = \DB::table('vendors')->where('id', $shop_id)->first();
            if (!$shop) abort(404);
        } else {
            if ($is_master) {
                $shop = MasterCustomer::findOrFail($shop_id);
            } else {
                $shop = MasterCustomer::where('id', $shop_id)->where('sales_agent_id', $agent->id)->firstOrFail();
            }
        }

        $agent_id = $agent->id;

        $sale_type = 'item';

        // Fetch Filter Options
        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->distinct()->pluck('production_goods.design_number');

        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->whereRaw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, ""))) != ""')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))) as full_name'))
            ->pluck('full_name');

        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->distinct()->pluck('master_colors.name');

        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->distinct()->pluck('master_size_measurements.name');
            
        $series = \App\Models\MasterSeries::where('status', 1)->pluck('name', 'id');
        $brands = \App\Models\Brand::where('status', 1)->pluck('name', 'id');
        $fittings = \App\Models\MasterProductFitting::where('status', 1)->pluck('name', 'id');
        $patterns = \App\Models\MasterDesignPattern::where('status', 1)->pluck('name', 'id');
        $product_natures = \App\Models\ProductNature::where('status', 1)->pluck('name', 'id');
        $fabric_types = \App\Models\FabricType::where('status', 1)->pluck('name', 'id');

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
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        // Always join brand-based discount
        $query->leftJoin('sales_agent_brand_discounts', function ($join) use ($agent_id) {
            $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
        });

        // Join storerooms to check for advance sample and priority
        $query->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where(function ($q) {
                $q->whereNull('storerooms.id')
                  ->orWhere('storerooms.order_taken', '=', 'Yes');
            });

        $isSampleSet = $request->query('sample_set') == '1';

        if ($isSampleSet) {
            $agentBatches = DB::table('fair_batches')
                ->whereJsonContains('sales_agent_ids', (string)$agent_id)
                ->pluck('id')
                ->toArray();
                
            $fpSubquery = DB::table('fair_products')
                ->select('product_id', 'size_set_id', DB::raw('MAX(discount_percent) as max_discount'))
                ->whereIn('fair_batch_id', empty($agentBatches) ? [-1] : $agentBatches)
                ->groupBy('product_id', 'size_set_id');
                
            $query->leftJoinSub($fpSubquery, 'fp', function ($join) {
                $join->on('production_goods.id', '=', 'fp.product_id')
                     ->on('domestic_inventories.size_set_id', '=', 'fp.size_set_id');
            });
            $discount_col = 'COALESCE(fp.max_discount, sales_agent_brand_discounts.discount_percentage, 0)';
        } else {
            $discount_col = 'COALESCE(sales_agent_brand_discounts.discount_percentage, 0)';
        }

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }
        if ($request->filled('series_id')) {
            $query->where('production_goods.master_series_id', $request->series_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('production_goods.brand_id', $request->brand_id);
        }
        if ($request->filled('fitting_id')) {
            $query->where('production_goods.master_product_fitting_id', $request->fitting_id);
        }
        if ($request->filled('pattern_id')) {
            $query->where('production_goods.master_pattern_id', $request->pattern_id);
        }
        if ($request->filled('product_nature_id')) {
            $query->where('production_goods.product_nature_id', $request->product_nature_id);
        }
        if ($request->filled('fabric_type_id')) {
            $query->where('production_goods.fabric_type_id', $request->fabric_type_id);
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
                DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
                DB::raw('MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) as is_advance_sample')
            );

        if ($isSampleSet) {
            $query->addSelect(DB::raw('MAX(fp.product_id) IS NOT NULL as is_sample_product'));
        } else {
            $query->addSelect(DB::raw('0 as is_sample_product'));
        }
        
        $hasFilters = $request->filled('design_number') || 
                      $request->filled('product_name') || 
                      $request->filled('color_name') || 
                      $request->filled('size_set_name') ||
                      $request->filled('series_id') ||
                      $request->filled('brand_id') ||
                      $request->filled('fitting_id') ||
                      $request->filled('pattern_id') ||
                      $request->filled('product_nature_id') ||
                      $request->filled('fabric_type_id');

        if (!$hasFilters && !$request->has('load_more') && !$request->has('page') && !$request->has('cart_keys')) {
            $boxes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        } else {
            if ($request->has('cart_keys') && is_array($request->cart_keys)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->cart_keys as $keyStr) {
                        $parts = explode('_', $keyStr);
                        if (count($parts) == 3) {
                            $q->orWhere(function ($sq) use ($parts) {
                                $sq->where('domestic_inventories.product_id', $parts[0])
                                   ->where('domestic_inventories.color_id', $parts[1])
                                   ->where('domestic_inventories.size_set_id', $parts[2]);
                            });
                        }
                    }
                });
            }
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
                DB::raw($discount_col)
            );
            
            if (!$request->has('cart_keys') || !is_array($request->cart_keys)) {
                $boxes->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');
            }

            $settings = \DB::table('settings')->first();
            
            $allowGlobal = false;
            $allowSample = $settings && $settings->agent_app_allow_over_stock_sample;
            
            // If requesting specific cart keys, bypass stock filters and increase pagination limit
            if ($request->has('cart_keys') && is_array($request->cart_keys)) {
                $allowGlobal = true; 
            }
            
            if (!$allowGlobal) {
                if ($allowSample && $isSampleSet) {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                } else {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                }
            }

            $perPage = ($request->has('cart_keys') && is_array($request->cart_keys)) ? 500 : 50;

            $boxes = $query->orderBy('production_goods.design_number')
                ->paginate($perPage)
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
                $settings = \DB::table('settings')->first();
                $html .= '<div class="col-md-4 col-lg-3 mb-3 variation-row-container">' . 
                         view('sales_agent.orders.partials.variation_card', compact('variation', 'vKey', 'image', 'settings'))->render() . 
                         '</div>';
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        // Fetch active sales men
        $sales_men = \App\Models\SalesMan::where('status', 1)->get();
        $settings = \DB::table('settings')->first();

        return view('sales_agent.orders.create', compact('shop', 'agent', 'designs', 'product_names', 'colors', 'size_sets', 'boxes', 'boxImages', 'gst_percentage', 'series', 'brands', 'fittings', 'patterns', 'product_natures', 'fabric_types', 'sales_men', 'party_type', 'isSampleSet', 'settings'));
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
        
        $master_agent_id = null;

        $party_type = $request->party_type ?? 'customer';
        if ($party_type == 'vendor') {
            $customer = \DB::table('vendors')->where('id', $request->shop_id)->first();
            if (!$customer) abort(404);
            $actual_agent_id = 0;
            if ($agent->is_master_agent) $master_agent_id = $agent_id;
        } else {
            $customer = \App\Models\MasterCustomer::findOrFail($request->shop_id);
            if ($agent->is_master_agent) {
                $actual_agent_id = $customer->sales_agent_id ?: 0;
                $master_agent_id = $agent_id;
            } else {
                $actual_agent_id = $agent_id;
            }
        }

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
            $selling_price = ceil($selling_price);

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

            $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'];

            $total_pcs = $var['qty'] * $pcs_per_box;

            $rack_id = $this->determineRackId($var['product_id'], $var['color_id'], $var['size_set_id'], $barcode);

            $items_to_create[] = [
                'rack_id' => $rack_id,
                'product_id' => $var['product_id'],
                'color_id' => $var['color_id'],
                'size_set_id' => $var['size_set_id'],
                'product_name' => $product_name ?: 'N/A',
                'design_number' => $product->design_number,
                'color_name' => $color->name,
                'size_set_name' => $sizeSet->name,
                'fitting_name' => $fitting->name ?? null,
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
                'remark' => $var['remark'] ?? null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
        }

        $total_amount = ceil($total_amount);
        $other_charges = ceil($request->other_charges ?? 0);

        if ($request->filled('discount_amount')) {
            $discount_amount = ceil((float) $request->discount_amount);
            $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
        } else {
            $discount_percentage = $request->discount_percentage ?? 0;
            $discount_amount = ceil($total_amount * $discount_percentage / 100);
        }

        $taxable_amount = $total_amount - $discount_amount;

        if ($request->filled('gst_amount')) {
            $gst_amount = ceil((float) $request->gst_amount);
            $gst_percentage = ($taxable_amount > 0) ? ($gst_amount / $taxable_amount * 100) : 0;
        } else {
            $gst_percentage = $request->gst_percentage ?? 5.00;
            $gst_amount = ceil($taxable_amount * ($gst_percentage / 100));
        }

        $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

        $status = $order_type === 'direct' ? 'dispatched' : 'pending';

        DB::beginTransaction();
        try {
            $order = AgentOrder::create([
                'sales_agent_id' => $actual_agent_id,
                'master_agent_id' => $master_agent_id,
                'sales_man_id' => $request->sales_man_id,
                'party_type' => $party_type,
                'master_customer_id' => $party_type == 'customer' ? $request->shop_id : null,
                'master_vendor_id' => $party_type == 'vendor' ? $request->shop_id : null,
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
                'is_sample_set' => $request->input('is_sample_set', 0),
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
        $agent = Auth::guard('sales_agent')->user();
        $agent_id = $agent->id;
        $query = AgentOrder::where('id', $id)->where('status', 'pending');
        
        if (!$agent->is_master_agent) {
            $query->where('sales_agent_id', $agent->id);
        } else {
            $query->where('master_agent_id', $agent->id);
        }
        
        $order = $query->firstOrFail();

        $shop = $order->party_type == 'vendor' ? $order->vendor : $order->shop;

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
            ->whereRaw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, ""))) != ""')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))) as full_name'))
            ->pluck('full_name');

        $series = \App\Models\MasterSeries::where('status', 1)->pluck('name', 'id');
        $brands = \App\Models\Brand::where('status', 1)->pluck('name', 'id');
        $fittings = \App\Models\MasterProductFitting::where('status', 1)->pluck('name', 'id');
        $patterns = \App\Models\MasterDesignPattern::where('status', 1)->pluck('name', 'id');
        $product_natures = \App\Models\ProductNature::where('status', 1)->pluck('name', 'id');
        $fabric_types = \App\Models\FabricType::where('status', 1)->pluck('name', 'id');

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
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        // Always join brand-based discount
        $query->leftJoin('sales_agent_brand_discounts', function($join) use ($agent_id) {
            $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
                 ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
        });

        // Join storerooms to check for advance sample
        $query->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id');

        if ($request->has('product_name')) {
            $isSampleSet = $request->input('sample_set') == '1';
        } else {
            $isSampleSet = $order->is_sample_set == 1;
        }
        $discount_col = '0';

        if ($isSampleSet) {
            $agentBatches = DB::table('fair_batches')
                ->whereJsonContains('sales_agent_ids', (string)$agent_id)
                ->pluck('id')
                ->toArray();
                
            $fpSubquery = DB::table('fair_products')
                ->select('product_id', 'size_set_id', DB::raw('MAX(discount_percent) as max_discount'))
                ->whereIn('fair_batch_id', empty($agentBatches) ? [-1] : $agentBatches)
                ->groupBy('product_id', 'size_set_id');
                
            $query->leftJoinSub($fpSubquery, 'fp', function ($join) {
                $join->on('production_goods.id', '=', 'fp.product_id')
                     ->on('domestic_inventories.size_set_id', '=', 'fp.size_set_id');
            });
            $discount_col = 'COALESCE(fp.max_discount, sales_agent_brand_discounts.discount_percentage, 0)';
        } else {
            $discount_col = 'COALESCE(sales_agent_brand_discounts.discount_percentage, 0)';
        }

        // Clone query BEFORE filters to fetch all selected items from the DB
        $unfilteredQuery = $query->clone();

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }
        if ($request->filled('series_id')) {
            $query->where('production_goods.master_series_id', $request->series_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('production_goods.brand_id', $request->brand_id);
        }
        if ($request->filled('fitting_id')) {
            $query->where('production_goods.master_product_fitting_id', $request->fitting_id);
        }
        if ($request->filled('pattern_id')) {
            $query->where('production_goods.master_pattern_id', $request->pattern_id);
        }
        if ($request->filled('product_nature_id')) {
            $query->where('production_goods.product_nature_id', $request->product_nature_id);
        }
        if ($request->filled('fabric_type_id')) {
            $query->where('production_goods.fabric_type_id', $request->fabric_type_id);
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
            'production_goods.name_of_garment',
            'master_series.name as series_name',
            'master_colors.name as color_name',
            'master_size_measurements.name as size_set_name',
            'master_product_fittings.name as fitting_name',
            'master_design_patterns.name as pattern_name',
            DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
            DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
            DB::raw('MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) as is_advance_sample')
        );

        if ($isSampleSet) {
            $query->addSelect(DB::raw('MAX(fp.product_id) IS NOT NULL as is_sample_product'));
        } else {
            $query->addSelect(DB::raw('0 as is_sample_product'));
        }

        $hasFilters = $request->filled('design_number') || 
                      $request->filled('product_name') || 
                      $request->filled('color_name') || 
                      $request->filled('size_set_name') ||
                      $request->filled('series_id') ||
                      $request->filled('brand_id') ||
                      $request->filled('fitting_id') ||
                      $request->filled('pattern_id') ||
                      $request->filled('product_nature_id') ||
                      $request->filled('fabric_type_id');

        if (!$hasFilters && !$request->has('load_more') && !$request->has('page') && !$request->has('cart_keys')) {
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
                    'production_goods.name_of_garment',
                    'master_series.name',
                    'master_colors.name', 
                    'master_size_measurements.name',
                    'master_product_fittings.name',
                    'master_design_patterns.name',
                    DB::raw($discount_col)
                )
                    ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');

                $settings = \DB::table('settings')->first();
                $allowGlobal = true; // Always allow existing items to show regardless of current stock
                $allowSample = $settings && $settings->agent_app_allow_over_stock_sample;
                
                if (!$allowGlobal) {
                    if ($allowSample && $isSampleSet) {
                        $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                    } else {
                        $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                    }
                }

                $boxes = $query->orderBy('production_goods.design_number')
                    ->paginate(50)
                    ->appends($request->except('page'));
            }
        } else {
            if ($request->has('cart_keys') && is_array($request->cart_keys)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->cart_keys as $keyStr) {
                        $parts = explode('_', $keyStr);
                        if (count($parts) == 3) {
                            $q->orWhere(function ($sq) use ($parts) {
                                $sq->where('domestic_inventories.product_id', $parts[0])
                                   ->where('domestic_inventories.color_id', $parts[1])
                                   ->where('domestic_inventories.size_set_id', $parts[2]);
                            });
                        }
                    }
                });
            }

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
                DB::raw($discount_col)
            )
                ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');

            $settings = \DB::table('settings')->first();
            $allowGlobal = false;
            $allowSample = $settings && $settings->agent_app_allow_over_stock_sample;
            
            if ($request->has('cart_keys') && is_array($request->cart_keys)) {
                $allowGlobal = true; 
            }

            if (!$allowGlobal) {
                if ($allowSample && $isSampleSet) {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                } else {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)');
                }
            }

            $perPage = ($request->has('cart_keys') && is_array($request->cart_keys)) ? 500 : 50;
            
            $boxes = $query->orderBy('production_goods.design_number')
                ->paginate($perPage)
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

        $settings = \DB::table('settings')->first();
        $allowOverStock = $settings && $settings->agent_app_allow_over_stock;



        if ($request->ajax() && $request->has('load_more')) {
            $html = "";
            foreach ($boxes as $variation) {
                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                $image = $boxImages[$vKey] ?? null;
                $settings = \DB::table('settings')->first();
                $html .= '<div class="col-md-4 col-lg-3 mb-3 variation-row-container">' . 
                         view('sales_agent.orders.partials.variation_card', compact('variation', 'vKey', 'image', 'settings'))->render() . 
                         '</div>';
            }
            return response()->json([
                'html' => $html,
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null
            ]);
        }

        // Selected quantities for existing order with updated prices
        $currentOrderItems = DB::table('agent_order_items')
            ->where('agent_order_id', $order->id)
            ->select(
                'product_id',
                'color_id',
                'size_set_id',
                DB::raw('SUM(box_qty) as current_order_qty')
            )
            ->groupBy('product_id', 'color_id', 'size_set_id');

        $existing_items_with_prices = $unfilteredQuery->clone()
            ->joinSub($currentOrderItems, 'current_items', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'current_items.product_id')
                    ->on('domestic_inventories.color_id', '=', 'current_items.color_id')
                    ->on('domestic_inventories.size_set_id', '=', 'current_items.size_set_id');
            })
            ->select(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                DB::raw('MAX(COALESCE(current_items.current_order_qty, 0)) as current_order_qty'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price')
            )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                DB::raw($discount_col)
            )
            ->havingRaw('MAX(COALESCE(current_items.current_order_qty, 0)) > 0')
            ->get();

        $selected_quantities = $existing_items_with_prices->keyBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id;
            })
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
                    'qty' => (int) $item->current_order_qty,
                    'pcs_per_box' => (float) $item->pcs_per_box,
                    'unit_price' => (float) $item->unit_price
                ];
            })
            ->toArray();

        // Fetch GST setting
        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        // Fetch active sales men
        $sales_men = \App\Models\SalesMan::where('status', 1)->get();
        $settings = \DB::table('settings')->first();

        return view('sales_agent.orders.edit', compact('shop', 'boxes', 'designs', 'product_names', 'colors', 'size_sets', 'order', 'selected_quantities', 'boxImages', 'gst_percentage', 'series', 'brands', 'fittings', 'patterns', 'product_natures', 'fabric_types', 'sales_men', 'settings'));
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
        $query = AgentOrder::where('id', $id)->where('status', 'pending');
        
        if (!$agent->is_master_agent) {
            $query->where('sales_agent_id', $agent->id);
        } else {
            $query->where('master_agent_id', $agent->id);
        }
        
        $order = $query->firstOrFail();

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
            $selling_price = ceil($selling_price);
            
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

            $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'];
            $total_pcs = $var['qty'] * $pcs_per_box;

            $rack_id = $this->determineRackId($var['product_id'], $var['color_id'], $var['size_set_id'], $barcode, $order->id);

            $items_to_create[] = [
                'agent_order_id' => $order->id,
                'rack_id' => $rack_id,
                'product_id' => $var['product_id'],
                'color_id' => $var['color_id'],
                'size_set_id' => $var['size_set_id'],
                'product_name' => $product_name ?: 'N/A',
                'design_number' => $product->design_number,
                'color_name' => $color->name,
                'size_set_name' => $sizeSet->name,
                'fitting_name' => $fitting->name ?? null,
                'pattern_name' => $pattern->name ?? null,
                'box_qty' => $var['qty'],
                'quantity' => $total_pcs,
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'barcode' => $barcode,
                'packing_box_id' => null,
                'remark' => $var['remark'] ?? null,
            ];
            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $selling_price);
        }

        $total_amount = ceil($total_amount);
        $other_charges = ceil($request->other_charges ?? 0);

        if ($request->filled('discount_amount')) {
            $discount_amount = ceil((float) $request->discount_amount);
            $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
        } else {
            $discount_percentage = $request->discount_percentage ?? 0;
            $discount_amount = ceil($total_amount * $discount_percentage / 100);
        }

        $taxable_amount = $total_amount - $discount_amount;

        if ($request->filled('gst_amount')) {
            $gst_amount = ceil((float) $request->gst_amount);
            $gst_percentage = ($taxable_amount > 0) ? ($gst_amount / $taxable_amount * 100) : 0;
        } else {
            $gst_percentage = $request->gst_percentage ?? 5.00;
            $gst_amount = ceil($taxable_amount * ($gst_percentage / 100));
        }

        $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

        DB::beginTransaction();
        try {
            $order->update([
                'sales_man_id' => $request->sales_man_id,
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'is_sample_set' => $request->input('is_sample_set', 0),
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
        $agent = Auth::guard('sales_agent')->user();
        if ($agent->is_master_agent) {
            $orders = AgentOrder::where('master_agent_id', $agent->id)
                ->whereDate('created_at', date('Y-m-d'))
                ->with(['shop', 'vendor'])
                ->latest()
                ->paginate(10);
        } else {
            $orders = AgentOrder::where('sales_agent_id', $agent->id)
                ->whereDate('created_at', date('Y-m-d'))
                ->with(['shop', 'vendor'])
                ->latest()
                ->paginate(10);
        }
        return view('sales_agent.orders.index', compact('orders'));
    }

    public function orderDetails($id)
    {
        $agent = Auth::guard('sales_agent')->user();
        $query = AgentOrder::where('id', $id)->with(['shop', 'vendor', 'items', 'agent']);
        
        if (!$agent->is_master_agent) {
            $query->where('sales_agent_id', $agent->id);
        } else {
            $query->where('master_agent_id', $agent->id);
        }
        
        $order = $query->firstOrFail();

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
                'box_count' => $group->sum('box_qty') > 0 ? $group->sum('box_qty') : $group->count(),
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

    public function downloadOrder(Request $request, $id)
    {
        $order = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->leftJoin('sales_men', 'agent_orders.sales_man_id', '=', 'sales_men.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'),
                DB::raw('COALESCE(master_customers.email, vendors.email) as shop_email'),
                DB::raw('COALESCE(master_customers.phone, vendors.phone) as shop_phone'),
                DB::raw('COALESCE(master_customers.address, vendors.address) as shop_address'),
                DB::raw('COALESCE(master_customers.see_price, 1) as see_price'),
                'sales_men.name as sales_man_name'
            )
            ->first();

        if (!$order)
            abort(404);

        if ($request->has('see_price')) {
            $order->see_price = $request->see_price;
        }

        if ($order->sale_type === 'fabric') {
            $items = DB::table('agent_order_fabric_items')
                ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
                ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_fabric_items.*',
                    'fabrics.name as fabric_name',
                    'fabrics.sku as fabric_sku',
                    'fabric_receipt_details.roll_number',
                    'fabric_receipt_details.batch_no'
                )
                ->get();

            $settings = DB::table('settings')->first();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.order-pdf-fabric', compact('order', 'items', 'settings'));
            return $pdf->download("Order_Sheet_Fabric_{$order->id}.pdf");
        }

        $itemsRaw = DB::table('agent_order_items')
            ->leftJoin('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->where('agent_order_id', $id)
            ->select(
                'agent_order_items.*',
                'master_design_patterns.name as db_pattern_name',
                'master_product_fittings.name as db_fitting_name',
                'production_goods.name_of_garment',
                'master_series.name as series_name'
            )
            ->get();

        $items = $itemsRaw->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();

            $inventoryInfo = DB::table('domestic_inventories')
                ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                ->where('domestic_inventories.product_id', $first->product_id)
                ->where('domestic_inventories.color_id', $first->color_id)
                ->where('domestic_inventories.size_set_id', $first->size_set_id)
                ->where('domestic_inventories.total_boxes', '>', 0)
                ->select('racks.name as rack_name', 'storerooms.name as warehouse_name')
                ->first();

            $itemRemarks = $group->pluck('remark')->filter()->unique()->implode(', ');

            return (object) [
                'product_name' => $first->product_name,
                'series_name' => $first->series_name ?? '',
                'name_of_garment' => $first->name_of_garment ?? '',
                'design_number' => $first->design_number,
                'color_name' => $first->color_name,
                'color_id' => $first->color_id,
                'size_set_name' => $first->size_set_name,
                'fitting_name' => $first->db_fitting_name ?? $first->fitting_name,
                'pattern_name' => $first->db_pattern_name ?? $first->pattern_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->sum('box_qty'),
                'barcode' => $first->barcode,
                'warehouse_name' => $inventoryInfo->warehouse_name ?? 'N/A',
                'rack_name' => $inventoryInfo->rack_name ?? 'N/A',
                'remark' => $itemRemarks ?: ($first->remark ?? null),
            ];
        })->values();

        $settings = DB::table('settings')->first();
        $pdf = Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Order_Sheet_ORD_' . $id . '.pdf');
    }

    public function sendWhatsappOrder(Request $request, $id)
    {
        $order = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->leftJoin('sales_men', 'agent_orders.sales_man_id', '=', 'sales_men.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'),
                DB::raw('COALESCE(master_customers.email, vendors.email) as shop_email'),
                DB::raw('COALESCE(master_customers.phone, vendors.phone) as shop_phone'),
                DB::raw('COALESCE(master_customers.address, vendors.address) as shop_address'),
                DB::raw('COALESCE(master_customers.see_price, 1) as see_price'),
                'sales_men.name as sales_man_name'
            )
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        $whatsappPhone = $order->shop_phone;

        if ($request->has('phone') && !empty($request->phone)) {
            $whatsappPhone = $request->phone;
        }

        if (empty($whatsappPhone)) {
            return back()->with('error', 'No phone number available for this party.');
        }

        // Override see_price if provided in request
        if ($request->has('see_price')) {
            $order->see_price = $request->see_price;
        }

        $pdf = null;
        $fileName = '';

        if ($order->sale_type === 'fabric') {
            $items = DB::table('agent_order_fabric_items')
                ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
                ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_fabric_items.*',
                    'fabrics.name as fabric_name',
                    'fabrics.sku as fabric_sku',
                    'fabric_receipt_details.roll_number',
                    'fabric_receipt_details.batch_no'
                )
                ->get();

            $settings = DB::table('settings')->first();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.order-pdf-fabric', compact('order', 'items', 'settings'));
            $fileName = "Order_Sheet_Fabric_{$order->id}.pdf";
        } else {
            $itemsRaw = DB::table('agent_order_items')
                ->leftJoin('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
                ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
                ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
                ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_items.*',
                    'master_design_patterns.name as db_pattern_name',
                    'master_product_fittings.name as db_fitting_name',
                    'production_goods.name_of_garment',
                    'master_series.name as series_name'
                )
                ->get();

            $items = $itemsRaw->groupBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
            })->map(function ($group) {
                $first = $group->first();

                $inventoryInfo = DB::table('domestic_inventories')
                    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $first->product_id)
                    ->where('domestic_inventories.color_id', $first->color_id)
                    ->where('domestic_inventories.size_set_id', $first->size_set_id)
                    ->where('domestic_inventories.total_boxes', '>', 0)
                    ->select('racks.name as rack_name', 'storerooms.name as warehouse_name')
                    ->first();

                $itemRemarks = $group->pluck('remark')->filter()->unique()->implode(', ');

                return (object) [
                    'product_name' => $first->product_name,
                    'series_name' => $first->series_name ?? '',
                    'name_of_garment' => $first->name_of_garment ?? '',
                    'design_number' => $first->design_number,
                    'color_name' => $first->color_name,
                    'color_id' => $first->color_id,
                    'size_set_name' => $first->size_set_name,
                    'fitting_name' => $first->db_fitting_name ?? $first->fitting_name,
                    'pattern_name' => $first->db_pattern_name ?? $first->pattern_name,
                    'mrp' => $first->mrp,
                    'selling_price' => $first->selling_price,
                    'total_qty' => $group->sum('quantity'),
                    'box_count' => $group->sum('box_qty'),
                    'barcode' => $first->barcode,
                    'warehouse_name' => $inventoryInfo->warehouse_name ?? 'N/A',
                    'rack_name' => $inventoryInfo->rack_name ?? 'N/A',
                    'remark' => $itemRemarks ?: ($first->remark ?? null),
                ];
            })->values();

            $settings = DB::table('settings')->first();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings'))
                ->setPaper('a4', 'portrait');
            $fileName = "Order_Sheet_ORD_{$order->id}.pdf";
        }

        $dir = public_path('whatsapp_pdfs');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdf->save($dir . '/' . $fileName);
        $physicalPath = $dir . '/' . $fileName;

        $msg = "Dear {$order->shop_name},\n\nYour Order #ORD-{$order->id} has been generated.\nPlease find your order sheet attached.\n\nThank you!";

        $status = send_whatsapp_attachment($whatsappPhone, $msg, $physicalPath, $fileName);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp message sent to ' . $whatsappPhone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
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
                if (preg_match('/^D(\d+)S(\d+)C(\d+)(?:P(\d+)F(\d+))?$/', $barcode, $matches)) {
                    $productId = $matches[1];
                    $scannedSizeSetId = $matches[2];
                    $colorId = $matches[3];
                    $isFairBarcode = false;
                    $fairProduct = null;
                    \Log::info("Scanning Domestic Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $scannedSizeSetId, 'colorId' => $colorId]);
                }
            }

            // Format: FAIR-{{ productId }}-{{ sizeSetId }}-{{ timestamp }} OR F{{ id_base36 }}
            if (strpos($barcode, 'FAIR-') === 0 || preg_match('/^F[A-Z0-9]+$/', $barcode)) {
                $fairProduct = \App\Models\FairProduct::where('barcode', $barcode)->first();
                
                if (strpos($barcode, 'FAIR-') === 0) {
                    $parts = explode('-', $barcode);
                    if (count($parts) < 3) return response()->json(['success' => false, 'message' => 'Invalid Fair barcode format.']);
                    $productId = $parts[1];
                    $scannedSizeSetId = $parts[2];
                } elseif ($fairProduct) {
                    $agent_id = Auth::guard('sales_agent')->id();
                    $batch = \App\Models\FairBatch::find($fairProduct->fair_batch_id);
                    if ($batch) {
                        if ($batch->status == 0) {
                            return response()->json(['success' => false, 'message' => 'This sample set is inactive and cannot be scanned.']);
                        }
                        $assignedAgents = is_array($batch->sales_agent_ids) ? $batch->sales_agent_ids : json_decode($batch->sales_agent_ids, true) ?? [];
                        if (!in_array((string)$agent_id, $assignedAgents, true) && !in_array((int)$agent_id, $assignedAgents, true)) {
                            return response()->json(['success' => false, 'message' => 'This sample set barcode is not assigned to your account.']);
                        }
                    }

                    $productId = $fairProduct->product_id;
                    $scannedSizeSetId = $fairProduct->size_set_id;
                } else {
                    return response()->json(['success' => false, 'message' => 'Fair product not found.']);
                }
                
                \Log::info("Scanning Fair Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $scannedSizeSetId]);
                $isFairBarcode = true;
            }

            if (isset($productId) && isset($scannedSizeSetId)) {
                $product = \App\Models\ProductionGoods::with(['series', 'variants'])->find($productId);

                if (!$product) {
                    return response()->json(['success' => false, 'message' => 'Product not found.']);
                }

                // Identify all available size_set_ids for this product in the current context
                if ($isFairBarcode && $fairProduct) {
                    $fairBatchId = $fairProduct->fair_batch_id;
                    $availableFairProducts = \App\Models\FairProduct::where('product_id', $productId)
                        ->where('fair_batch_id', $fairBatchId)
                        ->get();
                    
                    $availableSizeSetIds = $availableFairProducts->pluck('size_set_id')->unique()->toArray();
                    $discount_percentage = $fairProduct->discount_percent;
                } else {
                    $availableSizeSetIds = \App\Models\DomesticInventory::where('product_id', $productId)
                        ->where('status', 1)
                        ->pluck('size_set_id')
                        ->unique()
                        ->toArray();

                    $agent_id = Auth::guard('sales_agent')->id();
                    $discount_percentage = DB::table('sales_agent_brand_discounts')
                        ->where('sales_agent_id', $agent_id)
                        ->where('brand_id', $product->brand_id)
                        ->value('discount_percentage') ?? 0;
                }

                if (!in_array($scannedSizeSetId, $availableSizeSetIds)) {
                    $availableSizeSetIds[] = $scannedSizeSetId;
                }

                $isAdvanceSample = \DB::table('domestic_inventories')
                    ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $productId)
                    ->where('storerooms.name', 'ADVANCE SAMPLE')
                    ->exists();

                $sizeSetsData = [];
                $main_image_base = $product->photo;

                foreach ($availableSizeSetIds as $sizeSetId) {
                    $availableColorsQuery = \App\Models\DomesticInventory::where('product_id', $productId)
                        ->where('size_set_id', $sizeSetId)
                        ->where('domestic_inventories.status', 1);

                    if ($isFairBarcode && isset($availableFairProducts)) {
                        $fp = $availableFairProducts->firstWhere('size_set_id', $sizeSetId);
                        if ($fp && !empty($fp->color_ids)) {
                            $availableColorsQuery->whereIn('color_id', $fp->color_ids);
                        }
                    }

                    $availableColors = $availableColorsQuery
                        ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                        ->select('master_colors.id', 'master_colors.name', DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'), DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'))
                        ->groupBy('master_colors.id', 'master_colors.name')
                        ->get();
                        
                    $allocQuery = \DB::table('agent_order_items')
                        ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
                        ->where('agent_orders.status', 'pending')
                        ->where('agent_order_items.product_id', $productId)
                        ->where('agent_order_items.size_set_id', $sizeSetId);
                    
                    if ($request->filled('order_id')) {
                        $allocQuery->where('agent_orders.id', '!=', $request->order_id);
                    }
                    
                    $allocations = $allocQuery->select('color_id', \DB::raw('SUM(box_qty) as total_allocated'))
                        ->groupBy('color_id')
                        ->pluck('total_allocated', 'color_id');
                        
                    $variant = $product->variants->where('master_size_measurement_id', $sizeSetId)->first();
                    if (!$variant) {
                        $variant = $product->variants->first();
                    }
                    
                    $mrp = $variant->mrp ?? 0;
                    $unit_price = ceil($mrp - ($mrp * $discount_percentage / 100));
                    
                    $main_image = $main_image_base;
                    if (!$main_image && $variant && $variant->image) {
                        $main_image = $variant->image;
                    }

                    foreach ($availableColors as $color) {
                        if ($isAdvanceSample) {
                            $color->available_boxes = 99999;
                        } else {
                            $allocated = $allocations->get($color->id) ?? 0;
                            $color->available_boxes = max(0, $color->available_boxes - $allocated);
                        }

                        $cImg = DB::table('production_goods_variant_colors')
                            ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                            ->where('production_goods_variants.production_goods_id', $productId)
                            ->where('production_goods_variants.master_size_measurement_id', $sizeSetId)
                            ->where('production_goods_variant_colors.master_color_id', $color->id)
                            ->whereNotNull('production_goods_variant_colors.image')
                            ->value('production_goods_variant_colors.image');
                        
                        $color->image = $cImg ? asset('assets/products/' . $cImg) : ($main_image ? asset('assets/products/' . $main_image) : null);
                    }
                    
                    $sizeSetsData[] = [
                        'size_set_id' => (int)$sizeSetId,
                        'size_set_name' => DB::table('master_size_measurements')->where('id', $sizeSetId)->value('name'),
                        'mrp' => $mrp,
                        'unit_price' => $unit_price,
                        'image' => $main_image ? asset('assets/products/' . $main_image) : null,
                        'colors' => $availableColors
                    ];
                }

                return response()->json([
                    'success' => true,
                    'is_advance_sample' => $isAdvanceSample,
                    'product' => [
                        'id' => $product->id,
                        'name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
                        'design_number' => $product->design_number,
                        'image' => $main_image_base ? asset('assets/products/' . $main_image_base) : null,
                    ],
                    'scanned_size_set_id' => (int)$scannedSizeSetId,
                    'size_sets' => $sizeSetsData
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

    protected function determineRackId($product_id, $color_id, $size_set_id, $barcode, $exclude_order_id = null)
    {
        $allocated_query = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->where('agent_order_items.product_id', $product_id)
            ->where('agent_order_items.color_id', $color_id)
            ->where('agent_order_items.size_set_id', $size_set_id);
            
        if ($exclude_order_id) {
            $allocated_query->where('agent_orders.id', '!=', $exclude_order_id);
        }
        
        $allocated_qty = $allocated_query->sum('agent_order_items.box_qty') ?? 0;

        $inventories = \App\Models\DomesticInventory::select('domestic_inventories.*', 'storerooms.name as storeroom_name', 'storerooms.order_priority')
            ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where('domestic_inventories.product_id', $product_id)
            ->where('domestic_inventories.color_id', $color_id)
            ->where('domestic_inventories.size_set_id', $size_set_id)
            ->where('domestic_inventories.total_boxes', '>', 0)
            ->where(function ($q) {
                $q->whereNull('storerooms.id')
                  ->orWhere('storerooms.order_taken', '=', 'Yes');
            })
            ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
            ->orderBy('domestic_inventories.total_boxes', 'desc')
            ->get();

        $total_physical = $inventories->sum('total_boxes');
        $available_global = $total_physical - $allocated_qty;

        $rack_id = null;
        
        if ($available_global > 0) {
            $rack_id = $inventories->first()?->rack_id;
        }

        if (!$rack_id) {
            $max_stock_rack = \App\Models\DomesticInventory::select('domestic_inventories.*')
                ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                ->where('domestic_inventories.barcode', $barcode)
                ->where('domestic_inventories.total_boxes', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('storerooms.id')
                      ->orWhere('storerooms.order_taken', '=', 'Yes');
                })
                ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
                ->orderBy('domestic_inventories.total_boxes', 'desc')
                ->first();
                
            if (!$max_stock_rack) {
                $max_stock_rack = \App\Models\DomesticInventory::select('domestic_inventories.*')
                    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.barcode', $barcode)
                    ->where(function ($q) {
                        $q->whereNull('storerooms.id')
                          ->orWhere('storerooms.order_taken', '=', 'Yes');
                    })
                    ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
                    ->orderBy('domestic_inventories.total_boxes', 'desc')
                    ->first();
            }
            if (!$max_stock_rack) {
                $max_stock_rack = \App\Models\DomesticInventory::select('domestic_inventories.*')
                    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $product_id)
                    ->where(function ($q) {
                        $q->whereNull('storerooms.id')
                          ->orWhere('storerooms.order_taken', '=', 'Yes');
                    })
                    ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
                    ->orderBy('domestic_inventories.total_boxes', 'desc')
                    ->first();
            }
            $rack_id = $max_stock_rack ? $max_stock_rack->rack_id : null;
        }

        return $rack_id;
    }
}
