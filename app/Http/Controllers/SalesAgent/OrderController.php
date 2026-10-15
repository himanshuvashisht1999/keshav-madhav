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
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FairProduct;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Services\Admin\InventoryStockService;
use App\Services\SalesAgent\AgentOrderException;
use App\Services\SalesAgent\AgentOrderRules;

class OrderController extends Controller
{
    private function rules(): AgentOrderRules
    {
        return app(AgentOrderRules::class);
    }

    /** Orders this agent may see: their own, or for a master agent the ones they booked. */
    private function agentOrders($agent)
    {
        return AgentOrder::query()->when(
            $agent->is_master_agent,
            fn($q) => $q->where('master_agent_id', $agent->id),
            fn($q) => $q->where('sales_agent_id', $agent->id)
        );
    }

    /** Boxes open orders still have to take from stock, per item ($exceptPickedOrderId: that order's picked boxes count as its own). */
    private function openOrderBoxes($exceptPickedOrderId = null)
    {
        $own = $exceptPickedOrderId
            ? 'CASE WHEN agent_orders.id = ' . (int) $exceptPickedOrderId . ' THEN -agent_order_items.scanned_box_qty ELSE GREATEST(agent_order_items.box_qty - agent_order_items.scanned_box_qty, 0) END'
            : 'GREATEST(agent_order_items.box_qty - agent_order_items.scanned_box_qty, 0)';
        return DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', '!=', 'dispatched')
            ->whereNull('agent_order_items.dispatched_at')
            ->select('agent_order_items.product_id', 'agent_order_items.color_id', 'agent_order_items.size_set_id', DB::raw("SUM($own) as total_allocated"))
            ->groupBy('agent_order_items.product_id', 'agent_order_items.color_id', 'agent_order_items.size_set_id');
    }

    /** Group-by list of the order screens (the discount is only grouped when it comes from a table). */
    private static function gridGroupBy(string $discountCol): array
    {
        $cols = [
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
        ];
        if (!is_numeric($discountCol)) {
            $cols[] = DB::raw($discountCol);
        }
        return $cols;
    }

    /** Reply for a refused / failed save: JSON for the app (422 with the reason). */
    private function refuse(string $message, int $status = 422)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    /**
     * Brand discount % from the customer's own per-brand discounts (vendors / unknown customer get none).
     */
    private function customerBrandDiscount($partyType, $customerId, $brandId)
    {
        if ($partyType === 'vendor' || empty($customerId)) {
            return 0;
        }

        return DB::table('customer_brand_discounts')
            ->where('customer_id', $customerId)
            ->where('brand_id', $brandId)
            ->value('discount_percentage') ?? 0;
    }

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
            // only master agents order for vendors
            $shop = $this->rules()->vendorFor($agent, $shop_id);
            if (!$shop) abort(404);
        } else {
            $party_type = 'customer';
            $shop = MasterCustomer::where('id', $shop_id)->where('status', '!=', 3)
                ->when(!$is_master, fn($q) => $q->where('sales_agent_id', $agent->id))
                ->firstOrFail();
            if ((int) $shop->status !== 1) {
                return redirect()->route($is_master ? 'agent.orders.create' : 'agent.shops.index')
                    ->with('error', "{$shop->name} is inactive. Activate the shop before placing an order.");
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

        // Boxes all open orders still have to take (picked boxes have already left the stock)
        $allocated = $this->openOrderBoxes();

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

        // Discount = the customer's own per-brand discount (vendors get none)
        if ($party_type === 'vendor') {
            $base_discount_col = '0';
        } else {
            $query->leftJoin('customer_brand_discounts', function ($join) use ($shop_id) {
                $join->on('production_goods.brand_id', '=', 'customer_brand_discounts.brand_id')
                    ->where('customer_brand_discounts.customer_id', '=', $shop_id);
            });
            $base_discount_col = 'COALESCE(customer_brand_discounts.discount_percentage, 0)';
        }

        // Join storerooms to check for storerooms that allow ordering more than stock and priority
        $query->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where(function ($q) {
                $q->whereNull('storerooms.id')
                  ->orWhere('storerooms.order_taken', '=', 'Yes');
            });

        $isSampleSet = $request->query('sample_set') == '1';

        if ($isSampleSet) {
            $agentBatches = DB::table('fair_batches')
                ->where('status', 1)
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
            $discount_col = 'COALESCE(fp.max_discount, ' . $base_discount_col . ')';
        } else {
            $discount_col = $base_discount_col;
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
                DB::raw('GREATEST(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0), 0) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
                DB::raw('MAX(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) as is_advance_sample')
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
            $boxes = $query->groupBy(...self::gridGroupBy($discount_col));

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
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
                } else {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
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
        $request->validate([
            'shop_id' => 'required|integer',
            'party_type' => 'nullable|in:customer,vendor',
            'variations' => 'required|array|min:1',
            'variations.*.product_id' => 'required|integer',
            'variations.*.color_id' => 'required|integer',
            'variations.*.size_set_id' => 'required|integer',
            'variations.*.qty' => 'required|integer|min:1',
            'variations.*.pcs_per_box' => 'nullable|numeric|min:0',
            'variations.*.unit_price' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'other_charges' => 'nullable|numeric|min:0',
            'expected_dispatch_date' => 'nullable|date',
        ], [
            'variations.*.qty.integer' => 'Box quantity must be a whole number.',
            'variations.*.qty.min' => 'Box quantity must be at least 1.',
            'discount_amount.min' => 'Discount cannot be less than 0.',
            'other_charges.min' => 'Other charges cannot be less than 0.',
        ]);

        $agent = Auth::guard('sales_agent')->user();
        $rules = $this->rules();

        $party_type = $request->input('party_type', 'customer') === 'vendor' ? 'vendor' : 'customer';
        if ($party_type === 'vendor') {
            if (!$rules->vendorFor($agent, $request->shop_id)) {
                return $this->refuse('Only master agents can order for vendors, and the vendor must be active.', 403);
            }
            $actual_agent_id = 0;
            $master_agent_id = $agent->id;
        } else {
            $customer = $rules->customerFor($agent, $request->shop_id);
            if (!$customer) {
                return $this->refuse('This shop is inactive or is not one of your shops.', 403);
            }
            if ($agent->is_master_agent) {
                $actual_agent_id = $customer->sales_agent_id ?: 0;
                $master_agent_id = $agent->id;
            } else {
                $actual_agent_id = $agent->id;
                $master_agent_id = null;
            }
        }
        $isSampleSet = (int) $request->input('is_sample_set', 0) === 1;
        $customerId = $party_type === 'customer' ? (int) $request->shop_id : null;

        DB::beginTransaction();
        try {
            // price, pieces per box and rack are decided here; boxes only while stock is free
            [$lines, $need] = $rules->newLines($agent, $party_type, $customerId, $request->variations);
            if (empty($lines)) {
                throw new AgentOrderException('The order has no items.');
            }
            $rules->checkStock($need, $isSampleSet);

            $value = 0;
            $total_qty = 0;
            foreach ($lines as $line) {
                $value += $line['quantity'] * $line['selling_price'];
                $total_qty += $line['quantity'];
            }
            $discount = $request->filled('discount_amount')
                ? (float) $request->discount_amount
                : ceil(ceil($value) * (float) $request->input('discount_percentage', 0) / 100);
            $totals = $rules->totals($value, $discount, $request->input('other_charges', 0));

            $order = AgentOrder::create([
                'sales_agent_id' => $actual_agent_id,
                'master_agent_id' => $master_agent_id,
                'sales_man_id' => $rules->salesManId($request->sales_man_id),
                'party_type' => $party_type,
                'master_customer_id' => $party_type == 'customer' ? $request->shop_id : null,
                'master_vendor_id' => $party_type == 'vendor' ? $request->shop_id : null,
                'total_qty' => $total_qty,
                'status' => 'pending',
                'order_type' => 'normal',
                'sale_type' => 'item',
                'is_sample_set' => $isSampleSet ? 1 : 0,
                'expected_dispatch_date' => $request->expected_dispatch_date,
                'order_date' => now(),
                'created_by' => Auth::id() ?? 0,
                'remark' => $request->remark,
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
            ] + $totals);

            foreach ($lines as $line) {
                $line['agent_order_id'] = $order->id;
                AgentOrderItem::create($line);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order created successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (AgentOrderException $e) {
            DB::rollBack();
            return $this->refuse($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->refuse('Failed to create order: ' . $e->getMessage(), 500);
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

        // Boxes other open orders still have to take; boxes already picked for this order count as this order's own
        $allocated = $this->openOrderBoxes($id);

        // Same stock as Create Order (stock made in Packing included)
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

        // Discount = the customer's own per-brand discount (vendors get none)
        if ($order->party_type === 'vendor' || empty($order->master_customer_id)) {
            $base_discount_col = '0';
        } else {
            $query->leftJoin('customer_brand_discounts', function ($join) use ($order) {
                $join->on('production_goods.brand_id', '=', 'customer_brand_discounts.brand_id')
                    ->where('customer_brand_discounts.customer_id', '=', $order->master_customer_id);
            });
            $base_discount_col = 'COALESCE(customer_brand_discounts.discount_percentage, 0)';
        }

        // Join storerooms: only storerooms that take orders (as on Create Order), and the "order more than stock" setting
        $query->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where(function ($q) {
                $q->whereNull('storerooms.id')
                  ->orWhere('storerooms.order_taken', '=', 'Yes');
            });

        if ($request->has('product_name')) {
            $isSampleSet = $request->input('sample_set') == '1';
        } else {
            $isSampleSet = $order->is_sample_set == 1;
        }
        $discount_col = $base_discount_col;

        if ($isSampleSet) {
            $agentBatches = DB::table('fair_batches')
                ->where('status', 1)
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
            $discount_col = 'COALESCE(fp.max_discount, ' . $base_discount_col . ')';
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
            DB::raw('GREATEST(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0), 0) as available_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
            DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
            DB::raw('MAX(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) as is_advance_sample')
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
                
                $boxes = $query->groupBy(...self::gridGroupBy($discount_col))
                    ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');

                $settings = \DB::table('settings')->first();
                $allowGlobal = true; // Always allow existing items to show regardless of current stock
                $allowSample = $settings && $settings->agent_app_allow_over_stock_sample;
                
                if (!$allowGlobal) {
                    if ($allowSample && $isSampleSet) {
                        $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
                    } else {
                        $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
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

            $boxes = $query->groupBy(...self::gridGroupBy($discount_col))
                ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');

            $settings = \DB::table('settings')->first();
            $allowGlobal = false;
            $allowSample = $settings && $settings->agent_app_allow_over_stock_sample;
            
            if ($request->has('cart_keys') && is_array($request->cart_keys)) {
                $allowGlobal = true; 
            }

            if (!$allowGlobal) {
                if ($allowSample && $isSampleSet) {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (MAX(fp.product_id) IS NOT NULL) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
                } else {
                    $query->havingRaw('(SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0) OR (SUM(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) > 0)');
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

        // The order's own items (not dispatched), taken from the order itself so every item stays in the cart
        // even when it has no stock row any more; prices are the ones saved on the order.
        $selected_quantities = AgentOrderItem::where('agent_order_id', $order->id)
            ->whereNull('dispatched_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn($item) => $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id)
            ->map(function ($lines) {
                $first = $lines->first();
                $boxes = (int) $lines->sum('box_qty');
                return [
                    'product_id' => (int) $first->product_id,
                    'color_id' => (int) $first->color_id,
                    'size_set_id' => (int) $first->size_set_id,
                    'qty' => $boxes,
                    'pcs_per_box' => $boxes > 0 ? round((float) $lines->sum('quantity') / $boxes, 4) : 0,
                    'unit_price' => (float) $first->selling_price,
                    'picked' => (int) $lines->sum('scanned_box_qty'),
                ];
            })
            ->filter(fn($i) => $i['qty'] > 0)
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
            'variations.*.product_id' => 'required|integer',
            'variations.*.color_id' => 'required|integer',
            'variations.*.size_set_id' => 'required|integer',
            'variations.*.qty' => 'required|integer|min:1',
            'variations.*.pcs_per_box' => 'nullable|numeric|min:0',
            'variations.*.unit_price' => 'nullable|numeric|min:0',
            'expected_dispatch_date' => 'nullable|date',
        ], [
            'variations.*.qty.integer' => 'Box quantity must be a whole number.',
            'variations.*.qty.min' => 'Box quantity must be at least 1.',
        ]);

        $agent = Auth::guard('sales_agent')->user();
        $order = $this->agentOrders($agent)->where('id', $id)->where('status', 'pending')->first();
        if (!$order) {
            return $this->refuse('This order can no longer be edited (it is not pending, or it is not your order).', 404);
        }

        // A new expected dispatch date cannot be in the past; the order's own date may stay as it is
        $date = $request->filled('expected_dispatch_date') ? date('Y-m-d', strtotime($request->expected_dispatch_date)) : $order->expected_dispatch_date;
        $oldDate = $order->expected_dispatch_date ? date('Y-m-d', strtotime($order->expected_dispatch_date)) : null;
        if ($date && $date !== $oldDate && $date < date('Y-m-d')) {
            return $this->refuse('Expected dispatch date cannot be in the past.');
        }

        $rules = $this->rules();
        $stock = app(InventoryStockService::class);
        $partyType = $order->party_type === 'vendor' ? 'vendor' : 'customer';
        $customerId = $partyType === 'customer' ? $order->master_customer_id : null;
        $isSampleSet = (int) $request->input('is_sample_set', $order->is_sample_set) === 1;

        $wanted = [];
        foreach ($request->variations as $var) {
            $key = (int) $var['product_id'] . '_' . (int) $var['color_id'] . '_' . (int) $var['size_set_id'];
            if (isset($wanted[$key])) {
                $wanted[$key]['qty'] += (int) $var['qty'];
            } else {
                $wanted[$key] = $var;
                $wanted[$key]['qty'] = (int) $var['qty'];
            }
        }

        DB::beginTransaction();
        try {
            // Open lines are changed in place: boxes already picked stay picked, saved prices and remarks stay.
            $open = AgentOrderItem::where('agent_order_id', $order->id)->whereNull('dispatched_at')
                ->orderBy('id')->lockForUpdate()->get()
                ->groupBy(fn($i) => $i->product_id . '_' . $i->color_id . '_' . $i->size_set_id);

            // 1. Items taken off the order
            foreach ($open as $key => $lines) {
                if (isset($wanted[$key])) continue;
                $picked = (int) $lines->sum('scanned_box_qty');
                if ($picked > 0) {
                    $l = $lines->first();
                    throw new AgentOrderException(AgentOrderRules::itemLabel($l->design_number, $l->color_name, $l->size_set_name)
                        . ": {$picked} box(es) are already picked by the warehouse, so it cannot be removed. Please ask the office.");
                }
                AgentOrderItem::whereIn('id', $lines->pluck('id'))->delete();
            }

            // 2. Items kept, changed or added
            $need = [];
            foreach ($wanted as $key => $var) {
                $qty = $var['qty'];
                $lines = $open->get($key, collect());

                if ($lines->isEmpty()) { // new item: priced like a new order
                    $line = $rules->newLine($agent, $partyType, $customerId, $var, $qty, $order->id);
                    $line['agent_order_id'] = $order->id;
                    AgentOrderItem::create($line);
                    $need[$key] = ['product_id' => $line['product_id'], 'color_id' => $line['color_id'], 'size_set_id' => $line['size_set_id'],
                        'boxes' => $qty, 'label' => AgentOrderRules::itemLabel($line['design_number'], $line['color_name'], $line['size_set_name'])];
                    continue;
                }

                $have = (int) $lines->sum('box_qty');
                $picked = (int) $lines->sum('scanned_box_qty');
                $first = $lines->first();
                $label = AgentOrderRules::itemLabel($first->design_number, $first->color_name, $first->size_set_name);
                if ($qty < $picked) {
                    throw new AgentOrderException("{$label}: {$picked} box(es) are already picked by the warehouse, so the order cannot have fewer than {$picked}.");
                }

                if ($qty > $have) {
                    // more boxes: added to the last line, at that line's price and pieces per box
                    $last = $lines->last();
                    $pcs = (int) $last->box_qty > 0 ? (float) $last->quantity / (int) $last->box_qty : 0;
                    if ($pcs <= 0) {
                        $pcs = $rules->piecesPerBox($last->product_id, $last->color_id, $last->size_set_id, null,
                            \App\Models\MasterSizeMeasurement::find($last->size_set_id));
                    }
                    $add = $qty - $have;
                    $last->box_qty = (int) $last->box_qty + $add;
                    $last->quantity = (float) $last->quantity + $add * $pcs;
                    $last->save();
                    $need[$key] = ['product_id' => $first->product_id, 'color_id' => $first->color_id, 'size_set_id' => $first->size_set_id,
                        'boxes' => $qty - $picked, 'label' => $label];
                } elseif ($qty < $have) {
                    // fewer boxes: only boxes not picked yet come off, last line first
                    $remove = $have - $qty;
                    foreach ($lines->reverse() as $line) {
                        if ($remove <= 0) break;
                        $n = min((int) $line->box_qty - (int) $line->scanned_box_qty, $remove);
                        if ($n <= 0) continue;
                        $pcs = (int) $line->box_qty > 0 ? (float) $line->quantity / (int) $line->box_qty : 0;
                        if ((int) $line->box_qty - $n <= 0 && (int) $line->scanned_box_qty === 0) {
                            $line->delete();
                        } else {
                            $line->box_qty = (int) $line->box_qty - $n;
                            $line->quantity = max(0, (float) $line->quantity - $n * $pcs);
                            $line->save();
                        }
                        $remove -= $n;
                    }
                }
            }

            // 3. Only boxes added beyond what was on the order need free stock
            $rules->checkStock($need, $isSampleSet, $order->id);

            // 4. Totals from the order's lines; discount and other charges stay as on the order (the app has no box for them)
            $all = AgentOrderItem::where('agent_order_id', $order->id)->get();
            $value = $all->sum(fn($l) => (float) $l->quantity * (float) $l->selling_price);
            $totals = $rules->totals($value, min((float) $order->discount_amount, ceil(round($value, 4))), $order->other_charges);

            $salesManId = (string) $request->input('sales_man_id', '') === (string) $order->sales_man_id
                ? $order->sales_man_id
                : $rules->salesManId($request->sales_man_id);

            $order->update([
                'sales_man_id' => $salesManId,
                'total_qty' => $all->sum('quantity'),
                'expected_dispatch_date' => $date,
                'is_sample_set' => $isSampleSet ? 1 : 0,
                'remark' => $request->has('remark') ? $request->remark : $order->remark,
                'booking_station' => $request->has('booking_station') ? $request->booking_station : $order->booking_station,
                'transport' => $request->has('transport') ? $request->transport : $order->transport,
            ] + $totals);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order updated successfully!', 'redirect_url' => route('agent.orders.show', $order->id)]);
        } catch (AgentOrderException $e) {
            DB::rollBack();
            return $this->refuse($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->refuse('Failed to update order: ' . $e->getMessage(), 500);
        }
    }

    public function myOrders()
    {
        // All of the agent's orders, newest first, 20 per page
        $agent = Auth::guard('sales_agent')->user();
        $orders = $this->agentOrders($agent)
            ->with(['shop', 'vendor'])
            ->latest()
            ->paginate(20);
        return view('sales_agent.orders.index', compact('orders'));
    }

    public function orderDetails($id)
    {
        $agent = Auth::guard('sales_agent')->user();
        $order = $this->agentOrders($agent)->where('id', $id)->with(['shop', 'vendor', 'items', 'agent'])->firstOrFail();

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

    /**
     * Order sheet PDF of one of this agent's orders (other agents' orders are never given out).
     * Prices: 0 = none, 1 = all, 2 = unit price only; an agent not allowed to see prices always gets 0.
     * @return array|null [order row, pdf, file name]
     */
    private function orderSheet(Request $request, $id): ?array
    {
        $agent = Auth::guard('sales_agent')->user();
        if (!$this->agentOrders($agent)->where('id', $id)->exists()) {
            return null;
        }

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
            return null;
        }

        if (!$agent->see_price) {
            $order->see_price = 0;
        } elseif ($request->has('see_price') && in_array((string) $request->see_price, ['0', '1', '2'], true)) {
            $order->see_price = (int) $request->see_price;
        }

        $settings = DB::table('settings')->first();

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

            $pdf = Pdf::loadView('admin.agent_orders.order-pdf-fabric', compact('order', 'items', 'settings'));
            return [$order, $pdf, "Order_Sheet_Fabric_{$order->id}.pdf"];
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

        $pdf = Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings'))
            ->setPaper('a4', 'portrait');
        return [$order, $pdf, 'Order_Sheet_ORD_' . $order->id . '.pdf'];
    }

    public function downloadOrder(Request $request, $id)
    {
        $sheet = $this->orderSheet($request, $id);
        if (!$sheet) {
            abort(404);
        }
        [, $pdf, $fileName] = $sheet;
        return $pdf->download($fileName);
    }

    public function sendWhatsappOrder(Request $request, $id)
    {
        $sheet = $this->orderSheet($request, $id);
        if (!$sheet) {
            return back()->with('error', 'Order not found.');
        }
        [$order, $pdf, $fileName] = $sheet;

        $whatsappPhone = $request->filled('phone') ? $request->phone : $order->shop_phone;
        $digits = preg_replace('/[^0-9]/', '', (string) $whatsappPhone);
        if (empty($digits)) {
            return back()->with('error', 'No phone number available for this party.');
        }
        if (strlen($digits) < 10 || strlen($digits) > 12) {
            return back()->with('error', 'Please enter a valid 10-digit WhatsApp number.');
        }

        // saved in private storage only while it is sent
        $physicalPath = whatsapp_pdf_path($fileName);
        $pdf->save($physicalPath);

        $msg = "Dear {$order->shop_name},\n\nYour Order #ORD-{$order->id} has been generated.\nPlease find your order sheet attached.\n\nThank you!";

        $status = send_whatsapp_attachment($whatsappPhone, $msg, $physicalPath, $fileName);
        @unlink($physicalPath);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp message sent to ' . $whatsappPhone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
    }

    public function getVariationByBarcode(Request $request)
    {
        try {
            $barcode = trim($request->get('barcode', ''));
            if (!$barcode) return response()->json(['success' => false, 'message' => 'No barcode provided.']);

            // If the barcode is a URL, extract the last segment (the actual barcode)
            if (strpos($barcode, '/fc/') !== false) {
                $parts = explode('/fc/', $barcode);
                $clean = explode('/', $parts[1])[0];
                $clean = explode('?', $clean)[0];
                $clean = explode('#', $clean)[0];
                $barcode = trim($clean);
            } elseif (strpos($barcode, '/sc/') !== false) {
                $parts = explode('/sc/', $barcode);
                $clean = explode('/', $parts[1])[0];
                $clean = explode('?', $clean)[0];
                $clean = explode('#', $clean)[0];
                $barcode = trim($clean);
            } elseif (strpos($barcode, 'http://') === 0 || strpos($barcode, 'https://') === 0) {
                $clean = explode('?', $barcode)[0];
                $clean = explode('#', $clean)[0];
                $parts = explode('/', $clean);
                $barcode = trim(end($parts));
            }

            $productId = null;
            $scannedSizeSetId = null;
            $colorId = null;
            $isFairBarcode = false;
            $fairProduct = null;
            $agent_id = Auth::guard('sales_agent')->id();
            $agent = Auth::guard('sales_agent')->user();
            // the order being edited (only one of the agent's own orders)
            $ownOrder = $request->filled('order_id') ? $this->agentOrders($agent)->where('id', $request->order_id)->first() : null;

            // A sample-set barcode only works while its batch is active and given to this agent
            $fairBatchError = function ($fairProduct) use ($agent, $agent_id) {
                $batch = $fairProduct ? DB::table('fair_batches')->where('id', $fairProduct->fair_batch_id)->first() : null;
                if (!$batch) return null;
                if ((int) $batch->status === 0) return 'This sample set is inactive and cannot be scanned.';
                if ($agent && !$agent->is_master_agent && !AgentOrderRules::batchIsForAgent($batch->sales_agent_ids, $agent_id)) {
                    return 'This sample set barcode is not assigned to your account.';
                }
                return null;
            };

            // 1. Format: BX-{{ boxNo }} (box number printed on a stock box label)
            if (strpos($barcode, 'BX-') === 0) {
                $boxRow = DB::table('domestic_inventories')->where('box_no', $barcode)->orderByDesc('total_boxes')->first();
                if ($boxRow) {
                    $barcode = 'D' . $boxRow->product_id . 'S' . $boxRow->size_set_id . 'C' . $boxRow->color_id;
                } elseif (Schema::hasTable('packing_boxes') && ($box = DB::table('packing_boxes')->where('box_no', $barcode)->first()) && $box->barcode) {
                    $barcode = $box->barcode;
                } else {
                    return response()->json(['success' => false, 'message' => 'Box not found in records.']);
                }
            }

            // 2. Format: D{productId}S{sizeSetId}C{colorId}P{patternId}F{fittingId}
            if (strpos($barcode, 'D') === 0 && strpos($barcode, 'S') !== false) {
                if (preg_match('/^D(\d+)S(\d+)C(\d+)(?:P(\d+)F(\d+))?$/i', $barcode, $matches)) {
                    $productId = (int) $matches[1];
                    $scannedSizeSetId = (int) $matches[2];
                    $colorId = (int) $matches[3];
                    $isFairBarcode = false;
                    $fairProduct = null;
                    \Log::info("Scanning Domestic Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $scannedSizeSetId, 'colorId' => $colorId]);
                }
            }

            // 3. Format: FAIR-{{ productId }}-{{ sizeSetId }}-{{ timestamp }} OR F{{ id_base36 }}
            if (!$productId && (strpos($barcode, 'FAIR-') === 0 || preg_match('/^F[A-Z0-9]+$/i', $barcode))) {
                $fairProduct = \App\Models\FairProduct::where('barcode', $barcode)->first();
                if ($fairProduct && ($err = $fairBatchError($fairProduct))) {
                    return response()->json(['success' => false, 'message' => $err]);
                }

                if (strpos($barcode, 'FAIR-') === 0) {
                    $parts = explode('-', $barcode);
                    if (count($parts) >= 3 && is_numeric($parts[1]) && is_numeric($parts[2])) {
                        $productId = (int) $parts[1];
                        $scannedSizeSetId = (int) $parts[2];
                    }
                } elseif ($fairProduct) {
                    $productId = (int) $fairProduct->product_id;
                    $scannedSizeSetId = (int) $fairProduct->size_set_id;
                }
                
                if ($productId) {
                    \Log::info("Scanning Fair Barcode", ['barcode' => $barcode, 'productId' => $productId, 'sizeSetId' => $scannedSizeSetId]);
                    $isFairBarcode = true;
                }
            }

            // 4. Format: SMP-{{ productId }}-{{ sizeSetId }}-{{ timestamp }} OR SAMPLE-...
            if (!$productId && (strpos($barcode, 'SMP-') === 0 || strpos($barcode, 'SAMPLE-') === 0)) {
                $parts = explode('-', $barcode);
                if (count($parts) >= 3 && is_numeric($parts[1]) && is_numeric($parts[2])) {
                    $productId = (int) $parts[1];
                    $scannedSizeSetId = (int) $parts[2];
                } else if (Schema::hasTable('sample_products')) {
                    $sample = DB::table('sample_products')->where('barcode', $barcode)->first();
                    if ($sample) {
                        $productId = (int) $sample->product_id;
                        $scannedSizeSetId = (int) $sample->size_set_id;
                    }
                }
            }

            // 5. Direct lookup in production_goods_variant_colors by barcode
            if (!$productId && Schema::hasTable('production_goods_variant_colors')) {
                $vcRow = DB::table('production_goods_variant_colors')
                    ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                    ->where('production_goods_variant_colors.barcode', $barcode)
                    ->select(
                        'production_goods_variants.production_goods_id as product_id',
                        'production_goods_variants.master_size_measurement_id as size_set_id',
                        'production_goods_variant_colors.master_color_id as color_id'
                    )
                    ->first();
                if ($vcRow) {
                    $productId = (int) $vcRow->product_id;
                    $scannedSizeSetId = (int) $vcRow->size_set_id;
                    $colorId = (int) $vcRow->color_id;
                }
            }

            // 6. Direct lookup in sample_products by barcode
            if (!$productId && Schema::hasTable('sample_products')) {
                $sp = DB::table('sample_products')->where('barcode', $barcode)->first();
                if ($sp) {
                    $productId = (int) $sp->product_id;
                    $scannedSizeSetId = (int) $sp->size_set_id;
                }
            }

            // 7. Direct lookup in fair_products by barcode
            if (!$productId && Schema::hasTable('fair_products')) {
                $fp = DB::table('fair_products')->where('barcode', $barcode)->first();
                if ($fp && ($err = $fairBatchError($fp))) {
                    return response()->json(['success' => false, 'message' => $err]);
                }
                if ($fp) {
                    $productId = (int) $fp->product_id;
                    $scannedSizeSetId = (int) $fp->size_set_id;
                    $isFairBarcode = true;
                    $fairProduct = $fp;
                }
            }

            // 8. Lookup by Design Number or SKU (e.g. 26069, #26069, D26069)
            if (!$productId) {
                $cleanDesign = preg_replace('/^[#dD]/', '', $barcode);
                $prodRow = DB::table('production_goods')
                    ->where(function($q) use ($barcode, $cleanDesign) {
                        $q->where('design_number', $barcode)
                          ->orWhere('sku', $barcode);
                        if (!empty($cleanDesign)) {
                            $q->orWhere('design_number', $cleanDesign);
                        }
                    })
                    ->first();
                if ($prodRow) {
                    $productId = (int) $prodRow->id;
                    $scannedSizeSetId = DB::table('domestic_inventories')
                        ->where('product_id', $productId)
                        ->where('status', 1)
                        ->value('size_set_id') ?? DB::table('production_goods_variants')->where('production_goods_id', $productId)->value('master_size_measurement_id');
                }
            }

            if (isset($productId)) {
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

                    // Customer's own brand discount: from the order being edited, else the shop sent by the page
                    // (only the agent's own orders / shops)
                    $scanPartyType = $request->get('party_type', 'customer') === 'vendor' ? 'vendor' : 'customer';
                    $scanCustomerId = ($scanPartyType === 'customer' && $this->rules()->customerFor($agent, $request->get('shop_id')))
                        ? $request->get('shop_id') : null;
                    if ($ownOrder) {
                        $scanPartyType = $ownOrder->party_type;
                        $scanCustomerId = $ownOrder->master_customer_id;
                    }
                    $discount_percentage = $this->customerBrandDiscount($scanPartyType, $scanCustomerId, $product->brand_id);
                }

                if (!empty($scannedSizeSetId) && !in_array($scannedSizeSetId, $availableSizeSetIds)) {
                    $availableSizeSetIds[] = (int) $scannedSizeSetId;
                }

                if (empty($availableSizeSetIds)) {
                    // Fallback to variants size sets if no inventory recorded
                    $availableSizeSetIds = $product->variants->pluck('master_size_measurement_id')->unique()->filter()->toArray();
                }

                // "no stock limit" is decided per colour + size set (item kept in an "order more than stock" storeroom)
                $isAdvanceSample = false;

                $sizeSetsData = [];

                // Intelligent Hero Image resolution:
                $main_image_base = null;
                $vWithImg = $product->variants->first(function($v) {
                    return !empty($v->image);
                });
                if ($vWithImg) {
                    $main_image_base = $vWithImg->image;
                }
                if (!$main_image_base) {
                    $vcImg = DB::table('production_goods_variant_colors')
                        ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                        ->where('production_goods_variants.production_goods_id', $productId)
                        ->whereNotNull('production_goods_variant_colors.image')
                        ->where('production_goods_variant_colors.image', '!=', '')
                        ->value('production_goods_variant_colors.image');
                    if ($vcImg) {
                        $main_image_base = $vcImg;
                    }
                }
                if (!$main_image_base && method_exists($product, 'mainImage') && $product->mainImage) {
                    $main_image_base = $product->mainImage->image;
                }

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

                    // every colour in stock is listed; only boxes in storerooms that take orders count as available
                    $availableColors = $availableColorsQuery
                        ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                        ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->select('master_colors.id', 'master_colors.name',
                            DB::raw("SUM(CASE WHEN storerooms.id IS NULL OR storerooms.order_taken = 'Yes' THEN domestic_inventories.total_boxes ELSE 0 END) as available_boxes"),
                            DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                            DB::raw('MAX(CASE WHEN ' . \App\Models\Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) as is_advance_sample'))
                        ->groupBy('master_colors.id', 'master_colors.name')
                        ->get();

                    // boxes open orders still have to take (this order's picked boxes count as its own)
                    $allocations = $this->openOrderBoxes($ownOrder->id ?? null)
                        ->where('agent_order_items.product_id', $productId)
                        ->where('agent_order_items.size_set_id', $sizeSetId)
                        ->pluck('total_allocated', 'color_id');

                    $variant = $product->variants->where('master_size_measurement_id', $sizeSetId)->first();
                    if (!$variant) {
                        $variant = $product->variants->first();
                    }
                    
                    $mrp = $variant->mrp ?? 0;
                    $unit_price = ceil($mrp - ($mrp * $discount_percentage / 100));
                    
                    $main_image = ($variant && !empty($variant->image)) ? $variant->image : $main_image_base;

                    foreach ($availableColors as $color) {
                        $color->is_advance_sample = (bool) $color->is_advance_sample;
                        if ($color->is_advance_sample) {
                            $color->available_boxes = 99999;
                            $isAdvanceSample = true;
                        } else {
                            $allocated = (int) ($allocations->get($color->id) ?? 0);
                            $color->available_boxes = max(0, (int) $color->available_boxes - $allocated);
                        }

                        $cImg = null;
                        if ($variant) {
                            $cImg = DB::table('production_goods_variant_colors')
                                ->where('variant_id', $variant->id)
                                ->where('master_color_id', $color->id)
                                ->whereNotNull('image')
                                ->where('image', '!=', '')
                                ->value('image');
                        }

                        if (!$cImg) {
                            // Fallback to any variant color for this product and color
                            $cImg = DB::table('production_goods_variant_colors')
                                ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                                ->where('production_goods_variants.production_goods_id', $productId)
                                ->where('production_goods_variant_colors.master_color_id', $color->id)
                                ->whereNotNull('production_goods_variant_colors.image')
                                ->where('production_goods_variant_colors.image', '!=', '')
                                ->value('production_goods_variant_colors.image');
                        }
                        
                        $color->image = $cImg ? asset('assets/products/' . $cImg) : ($main_image ? asset('assets/products/' . $main_image) : null);
                    }
                    
                    $sizeSetsData[] = [
                        'size_set_id' => (int)$sizeSetId,
                        'size_set_name' => DB::table('master_size_measurements')->where('id', $sizeSetId)->value('name') ?? ('Size ' . $sizeSetId),
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
                        'series_name' => $product->series->name ?? '',
                        'image' => $main_image_base ? asset('assets/products/' . $main_image_base) : null,
                    ],
                    'scanned_size_set_id' => $scannedSizeSetId ? (int)$scannedSizeSetId : null,
                    'scanned_color_id' => $colorId ? (int)$colorId : null,
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

    /** Rack an order line is expected to be picked from (shared with the offline sync). */
    public function determineRackId($product_id, $color_id, $size_set_id, $barcode, $exclude_order_id = null)
    {
        return $this->rules()->rackFor($product_id, $color_id, $size_set_id, $barcode, $exclude_order_id);
    }
}
