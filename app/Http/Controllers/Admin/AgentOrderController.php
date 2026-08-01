<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\AgentOrderFabricItem;
use App\Models\DomesticInventory;
use App\Models\Fabric;
use App\Models\FabricReceiptDetail;
use App\Models\AgentOrderReturn;
use App\Models\AgentOrderReturnItem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AgentOrderController extends Controller
{
    public function create(Request $request)
    {
        $agent_id = $request->get('sales_agent_id');
        $shop_id = $request->get('master_customer_id');
        $vendor_id = $request->get('master_vendor_id');
        $party_type = $request->get('party_type', 'customer');

        $is_selected = ($party_type === 'customer' && $shop_id) || ($party_type === 'vendor' && $vendor_id);

        if (!$agent_id || !$is_selected) {
            $agents = DB::table('sales_agents')->select('id', 'name')->where('status', 1)->get();
            $shops = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get();
            $vendors = DB::table('vendors')->select('id', 'name')->where('status', 1)->get();
            $salesMen = \App\Models\SalesMan::where('status', 1)->get();
            return view('admin.agent_orders.create', compact('agents', 'shops', 'vendors', 'salesMen'));
        }

        if ($agent_id === 'direct') {
            $agent = (object) ['id' => 'direct', 'name' => 'Direct (No Agent)', 'discount_percentage' => 0];
        } else {
            $agent = DB::table('sales_agents')->where('id', $agent_id)->first();
        }

        if ($party_type === 'vendor') {
            $shop = DB::table('vendors')->where('id', $vendor_id)->first();
        } else {
            $shop = DB::table('master_customers')->where('id', $shop_id)->first();
        }

        if (!$agent || !$shop) {
            return redirect()->route('admin.agent-orders.create')->with('error', 'Invalid Agent or Party selected.');
        }

        $sale_type = $request->get('sale_type', 'item');

        if ($sale_type === 'fabric') {
            $fabrics = Fabric::where('status', 1)
                ->withSum([
                    'rolls as total_meters' => function ($query) {
                        $query->where('remaining_quantity', '>', 0);
                    }
                ], 'remaining_quantity')
                ->get();

            $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

            return view('admin.agent_orders.create_fabric', compact('agent', 'shop', 'fabrics', 'gst_percentage'));
        }

        $designs = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->where('design_number', '!=', '')
            ->distinct()->pluck('design_number');

        $product_names = \App\Models\ProductionGoods::where('production_goods.status', 1)
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->whereRaw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, ""))) != ""')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))) as full_name'))
            ->pluck('full_name');

        $colors = \App\Models\MasterColor::where('status', 1)
            ->select(DB::raw("CONCAT(name, ' (', id, ')') as full_name"))
            ->distinct()->pluck('full_name');

        $size_sets = \App\Models\MasterSizeMeasurement::where('status', 1)
            ->distinct()->pluck('name');

        $patterns = \App\Models\MasterDesignPattern::where('status', 1)->pluck('name', 'id');
        $fittings = \App\Models\MasterProductFitting::where('status', 1)->pluck('name', 'id');
        $product_natures = \App\Models\ProductNature::pluck('name', 'id');
        $fabric_types = \App\Models\FabricType::pluck('name', 'id');

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
            // ->where(function ($q) {
            //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            // })
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')

            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        $isSampleSet = $request->query('sample_set') == '1';

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
        }

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->whereRaw("CONCAT(master_colors.name, ' (', master_colors.id, ')') = ?", [$request->color_name]);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }
        if ($request->filled('pattern_id')) {
            $query->where('production_goods.master_pattern_id', $request->pattern_id);
        }
        if ($request->filled('fitting_id')) {
            $query->where('production_goods.master_product_fitting_id', $request->fitting_id);
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
                DB::raw("CONCAT(master_colors.name, ' (', master_colors.id, ')') as color_name"),
                'master_size_measurements.name as size_set_name',
                'master_product_fittings.name as fitting_name',
                'master_design_patterns.name as pattern_name',

                DB::raw('(SUM(domestic_inventories.total_boxes) - MAX(COALESCE(alloc.total_allocated, 0))) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
                DB::raw('MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) as is_advance_sample')
            );

        $boxes = $query->groupBy(
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'production_goods.design_number',
            'production_goods.name_of_garment',
            'master_series.name',
            'master_colors.id',
            'master_colors.name',
            'master_size_measurements.name',
            'master_product_fittings.name',
            'master_design_patterns.name',

            DB::raw($discount_col)
        )
            ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
            ->havingRaw('(SUM(domestic_inventories.total_boxes) > MAX(COALESCE(alloc.total_allocated, 0))) OR (MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)')
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
        }

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
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null,
                'total_count' => $boxes->total()
            ]);
        }

        return view('admin.agent_orders.create', compact('agent', 'shop', 'designs', 'product_names', 'colors', 'size_sets', 'patterns', 'fittings', 'product_natures', 'fabric_types', 'boxes', 'boxImages', 'gst_percentage'));
    }

    public function getFabricRolls($id)
    {
        $rolls = FabricReceiptDetail::where('fabric_id', $id)
            ->where('remaining_quantity', '>', 0)
            // ->where('status', 1)
            ->select('id', 'roll_number', 'batch_no', 'remaining_quantity', 'price_per_meter')
            ->get();
        return response()->json($rolls);
    }

    public function store(Request $request)
    {
        // Check if it's the Step 1 form or the Step 2 AJAX
        if (!$request->ajax()) {
            $request->validate([
                'order_type' => 'required|in:normal,direct',
                'sale_type' => 'required|string',
                'party_type' => 'required|in:customer,vendor',
                'master_customer_id' => 'required_if:party_type,customer',
                'master_vendor_id' => 'required_if:party_type,vendor',
                'order_date' => 'required|date',
            ]);

            $agent_id = 'direct';
            if ($request->order_type === 'normal') {
                if ($request->party_type === 'customer') {
                    $customer = DB::table('master_customers')->where('id', $request->master_customer_id)->first();
                    $agent_id = ($customer && $customer->sales_agent_id) ? $customer->sales_agent_id : 'direct';
                } else {
                    $vendor = DB::table('vendors')->where('id', $request->master_vendor_id)->first();
                    $agent_id = 'direct'; // Vendors usually don't have sales agents in this schema?
                }
            }

            return redirect()->route('admin.agent-orders.create', [
                'order_type' => $request->order_type,
                'sale_type' => $request->sale_type,
                'party_type' => $request->party_type,
                'sales_agent_id' => $agent_id,
                'master_customer_id' => $request->master_customer_id,
                'master_vendor_id' => $request->master_vendor_id,
                'sales_man_id' => $request->sales_man_id,
                'order_date' => $request->order_date
            ]);
        }

        // AJAX Store Logic
        $sale_type = $request->sale_type ?: 'item';
        $order_type = $request->order_type ?: ($request->sales_agent_id === 'direct' ? 'direct' : 'normal');

        if ($sale_type === 'fabric') {
            $request->validate([
                'rolls' => 'required|array|min:1',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        } else {
            $request->validate([
                'variations' => 'required|array|min:1',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        }

        if ($request->party_type === 'vendor') {
            $customer = \App\Models\Vendor::findOrFail($request->master_vendor_id);
        } else {
            $customer = \App\Models\MasterCustomer::findOrFail($request->master_customer_id);
        }

        if ($order_type === 'direct') {
            $agent_id_to_save = 0;
        } else {
            $agent_id_to_save = $customer->sales_agent_id ?: 0;
        }

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];
        $fabric_items_to_create = [];

        if ($sale_type === 'fabric') {
            foreach ($request->rolls as $r) {
                $roll = FabricReceiptDetail::find($r['roll_id']);
                if (!$roll)
                    continue;

                $fabric_items_to_create[] = [
                    'fabric_id' => $r['fabric_id'] ?? $roll->fabric_id,
                    'fabric_receipt_detail_id' => $r['roll_id'],
                    'meter' => (float) $r['meter'],
                    'selling_price' => (float) $r['price'],
                ];
                $total_qty += (float) $r['meter'];
                $total_amount += ((float) $r['meter'] * (float) $r['price']);
            }
        } else {
            foreach ($request->variations as $var) {
                $product = \App\Models\ProductionGoods::with('series', 'brand')->find($var['product_id']);
                $color = \App\Models\MasterColor::find($var['color_id']);
                $sizeSet = \App\Models\MasterSizeMeasurement::find($var['size_set_id']);

                if (!$product || !$color || !$sizeSet)
                    continue;

                // Fetch Brand-based Discount
                $brand_discount = 0;
                if ($request->sales_agent_id === 'direct') {
                    if ($request->party_type === 'vendor') {
                        $brand_discount = 0; // Vendors don't have brand discounts in this schema yet
                    } else {
                        $brand_discount = DB::table('customer_brand_discounts')
                            ->where('customer_id', $request->master_customer_id)
                            ->where('brand_id', $product->brand_id)
                            ->value('discount_percentage') ?? 0;
                    }
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

                $max_stock_rack = \App\Models\DomesticInventory::where('barcode', $barcode)
                    ->where('total_boxes', '>', 0)
                    ->orderBy('total_boxes', 'desc')
                    ->first();
                if (!$max_stock_rack) {
                    $max_stock_rack = \App\Models\DomesticInventory::where('barcode', $barcode)
                        ->orderBy('total_boxes', 'desc')
                        ->first();
                }
                if (!$max_stock_rack) {
                    $max_stock_rack = \App\Models\DomesticInventory::where('product_id', $var['product_id'])
                        ->orderBy('total_boxes', 'desc')
                        ->first();
                }
                $rack_id = $max_stock_rack ? $max_stock_rack->rack_id : null;

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
                ];
                $total_qty += $total_pcs;
                $total_amount += ($total_pcs * $selling_price);
            }
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

        $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

        $status = strtolower($order_type) === 'direct' ? 'dispatched' : ($request->status ?? 'pending');
        DB::beginTransaction();
        try {
            $order = AgentOrder::create([
                'sales_agent_id' => $agent_id_to_save,
                'sales_man_id' => $request->sales_man_id,
                'party_type' => $request->party_type ?? 'customer',
                'master_customer_id' => $request->party_type === 'vendor' ? null : $request->master_customer_id,
                'master_vendor_id' => $request->party_type === 'vendor' ? $request->master_vendor_id : null,
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
                'order_date' => $request->order_date ?? date('Y-m-d'),
                'created_by' => Auth::id(),
                'remark' => $request->remark,
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
            ]);

            $dispatch = null;
            if ($order_type === 'direct') {
                $dispatch = \App\Models\AgentOrderDispatch::create([
                    'party_type' => $request->party_type ?? 'customer',
                    'master_customer_id' => $request->party_type === 'vendor' ? null : $request->master_customer_id,
                    'master_vendor_id' => $request->party_type === 'vendor' ? $request->master_vendor_id : null,
                    'sales_agent_id' => $agent_id_to_save,
                    'status' => 'dispatched',
                    'created_by' => Auth::id(),
                    'dispatch_date' => $request->order_date ?? date('Y-m-d'),
                    'total_amount' => $total_amount,
                    'discount_amount' => $discount_amount,
                    'gst_amount' => $gst_amount,
                    'gst_percentage' => $gst_percentage,
                    'other_charges' => $other_charges,
                    'grand_total' => $grand_total,
                    'remark' => $request->remark,
                ]);

                \App\Models\AgentOrderDispatchItem::create([
                    'agent_order_dispatch_id' => $dispatch->id,
                    'agent_order_id' => $order->id,
                ]);
            }

            if ($sale_type === 'fabric') {
                foreach ($fabric_items_to_create as $f_item) {
                    $f_item['agent_order_id'] = $order->id;
                    if ($dispatch) {
                        $f_item['agent_order_dispatch_id'] = $dispatch->id;
                        $f_item['status'] = 'dispatched';
                        $f_item['dispatched_at'] = now();
                    }
                    AgentOrderFabricItem::create($f_item);

                    // ALWAYS Deduct from fabric_receipt_details for fabric orders
                    $roll = FabricReceiptDetail::find($f_item['fabric_receipt_detail_id']);
                    if ($roll) {
                        $roll->decrement('remaining_quantity', $f_item['meter']);
                    }
                }
            } else {
                foreach ($items_to_create as $item) {
                    $item['agent_order_id'] = $order->id;
                    if ($dispatch) {
                        $item['agent_order_dispatch_id'] = $dispatch->id;
                    }
                    AgentOrderItem::create($item);

                    if ($order_type === 'direct') {
                        // Find inventory to deduct
                        $inventories = DomesticInventory::where('barcode', $item['barcode'])
                            ->where('total_boxes', '>', 0)
                            ->get();

                        $remainingToDeduct = $item['box_qty'];
                        foreach ($inventories as $inv) {
                            if ($remainingToDeduct <= 0)
                                break;
                            $deduct = min($inv->total_boxes, $remainingToDeduct);

                            \App\Models\DomesticInventoryHistory::create([
                                'domestic_inventory_id' => $inv->id,
                                'user_id' => Auth::id(),
                                'type' => 'stock_consume',
                                'old_product_id' => $inv->product_id,
                                'old_color_id' => $inv->color_id,
                                'old_size_set_id' => $inv->size_set_id,

                                'old_rack_id' => $inv->warehouse_rack_id,
                                'box_quantity' => $deduct,
                            ]);

                            $inv->decrement('total_boxes', $deduct);
                            if ($inv->total_boxes <= 0)
                                $inv->delete();
                            $remainingToDeduct -= $deduct;
                        }
                    }
                }
            }

            if ($order_type === 'direct') {
                if ($order->party_type === 'vendor') {
                    $vendor = \App\Models\Vendor::find($order->master_vendor_id);
                    if ($vendor)
                        $vendor->decrement('balance', $grand_total);
                } else {
                    $customer = \App\Models\MasterCustomer::find($order->master_customer_id);
                    if ($customer)
                        $customer->decrement('balance', $grand_total);
                }
            }

            DB::commit();

            $redirect_url = $order_type === 'direct'
                ? route('admin.agent-orders.dispatches.show', $dispatch->id)
                : route('admin.agent-orders.show', $order->id);

            return response()->json(['success' => true, 'message' => 'Order created successfully!', 'redirect_url' => $redirect_url]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    public function getShops(Request $request)
    {
        $agent_id = $request->get('agent_id');
        $is_direct = $request->get('is_direct');

        $customersQuery = DB::table('master_customers')
            ->select('id', 'name')
            ->where('status', 1);

        if ($is_direct == 1 || $agent_id === 'direct') {
            $customersQuery->where('subtype', 'direct');
        } elseif (!empty($agent_id)) {
            $customersQuery->where('sales_agent_id', $agent_id);
        }

        $customers = $customersQuery->get()->map(function ($c) {
            $c->type = 'customer';
            return $c;
        });

        $res = $customers;

        // Vendors are usually direct, so only show if not filtering by a specific non-direct agent
        if (empty($agent_id) || $agent_id === 'direct' || $is_direct == 1) {
            $vendors = DB::table('vendors')
                ->select('id', 'name')
                ->where('status', 1)
                ->get()
                ->map(function ($v) {
                    $v->type = 'vendor';
                    return $v;
                });
            $res = $res->concat($vendors);
        }

        return response()->json($res);
    }

    public function index(Request $request)
    {
        $query = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'),
                DB::raw('(SELECT COALESCE(SUM(box_qty), 0) FROM agent_order_items WHERE agent_order_id = agent_orders.id) + (SELECT COALESCE(COUNT(id), 0) FROM agent_order_fabric_items WHERE agent_order_id = agent_orders.id) as total_boxes'),
                DB::raw('(SELECT COALESCE(SUM(scanned_box_qty), 0) FROM agent_order_items WHERE agent_order_id = agent_orders.id AND dispatched_at IS NULL) as scanned_count'),
                DB::raw('(SELECT COALESCE(SUM(scanned_quantity * selling_price), 0) FROM agent_order_items WHERE agent_order_id = agent_orders.id AND dispatched_at IS NULL) as scanned_amount'),
                DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") as total_paid')
            );

        if ($request->filled('agent_id')) {
            if ($request->agent_id === 'direct') {
                $query->where(function ($q) {
                    $q->whereNull('agent_orders.sales_agent_id')
                        ->orWhere('agent_orders.sales_agent_id', 0)
                        ->orWhere('agent_orders.sales_agent_id', 'direct');
                });
            } else {
                $query->where('agent_orders.sales_agent_id', $request->agent_id);
            }
        }
        if ($request->filled('party_id')) {
            $parts = explode('_', $request->party_id);
            if (count($parts) == 2) {
                $type = $parts[0];
                $pId = $parts[1];
                if ($type == 'vendor') {
                    $query->where('agent_orders.master_vendor_id', $pId);
                } else {
                    $query->where('agent_orders.master_customer_id', $pId);
                }
            }
        }
        if ($request->filled('shop_id')) {
            $query->where('agent_orders.master_customer_id', $request->shop_id);
        }
        if ($request->filled('vendor_id')) {
            $query->where('agent_orders.master_vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'delayed') {
                $query->where(function ($q) {
                    $q->where('agent_orders.status', 'delayed')
                      ->orWhere(function ($q2) {
                          $q2->where('agent_orders.status', 'pending')
                             ->whereNotNull('agent_orders.expected_dispatch_date')
                             ->where('agent_orders.expected_dispatch_date', '<', date('Y-m-d'));
                      });
                });
            } elseif ($request->status === 'pending') {
                $query->where('agent_orders.status', 'pending')
                      ->where(function ($q) {
                          $q->whereNull('agent_orders.expected_dispatch_date')
                            ->orWhere('agent_orders.expected_dispatch_date', '>=', date('Y-m-d'));
                      });
            } else {
                $query->where('agent_orders.status', $request->status);
            }
        }
        if ($request->filled('sale_type')) {
            $query->where('agent_orders.sale_type', $request->sale_type);
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status == 'paid') {
                $query->whereRaw('(SELECT SUM(amount) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") >= agent_orders.grand_total');
            } elseif ($request->payment_status == 'unpaid') {
                $query->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") < agent_orders.grand_total');
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('agent_orders.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('agent_orders.created_at', '<=', $request->to_date);
        }

        // ── Aggregate totals (across ALL filtered rows, not just current page) ──
        $totalsQuery = clone $query;
        $totals = $totalsQuery->select(
            DB::raw('COALESCE(SUM(agent_orders.grand_total), 0) as total_grand_total'),
            DB::raw('COALESCE(SUM(agent_orders.total_qty), 0)   as total_pieces'),
            DB::raw('COUNT(agent_orders.id)                      as total_orders')
        )->first();

        $orders = $query->orderBy('agent_orders.id', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $agents = DB::table('sales_agents')->select('id', 'name')->get();

        $customers = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get()->map(function ($item) {
            $item->combined_id = 'customer_' . $item->id;
            $item->type = 'customer';
            return $item;
        });
        $vendors_list = DB::table('vendors')->select('id', 'name')->where('status', 1)->get()->map(function ($item) {
            $item->combined_id = 'vendor_' . $item->id;
            $item->type = 'vendor';
            return $item;
        });
        $parties = $customers->concat($vendors_list)->sortBy('name');

        return view('admin.agent_orders.index', compact('orders', 'agents', 'parties', 'totals'));
    }

    /* ─────────────────────────────────────────────────────────────
     * Shared helper: build filtered query for exports
     * ───────────────────────────────────────────────────────────── */
    private function buildOrdersQuery(Request $request)
    {
        $query = DB::table('agent_orders')
            ->leftJoin('sales_agents',    'agent_orders.sales_agent_id',    '=', 'sales_agents.id')
            ->leftJoin('master_customers','agent_orders.master_customer_id','=', 'master_customers.id')
            ->leftJoin('vendors',         'agent_orders.master_vendor_id',  '=', 'vendors.id')
            ->select(
                'agent_orders.id',
                'agent_orders.created_at',
                'agent_orders.status',
                'agent_orders.sale_type',
                'agent_orders.order_type',
                'agent_orders.grand_total',
                'agent_orders.total_qty',
                'agent_orders.order_date',
                'agent_orders.expected_dispatch_date',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name)    as shop_name')
            );

        if ($request->filled('agent_id')) {
            if ($request->agent_id === 'direct') {
                $query->where(function ($q) {
                    $q->whereNull('agent_orders.sales_agent_id')
                      ->orWhere('agent_orders.sales_agent_id', 0);
                });
            } else {
                $query->where('agent_orders.sales_agent_id', $request->agent_id);
            }
        }
        if ($request->filled('party_id')) {
            $parts = explode('_', $request->party_id);
            if (count($parts) == 2) {
                [$type, $pId] = $parts;
                if ($type == 'vendor') $query->where('agent_orders.master_vendor_id',  $pId);
                else                  $query->where('agent_orders.master_customer_id', $pId);
            }
        }
        if ($request->filled('status')) {
            if ($request->status === 'delayed') {
                $query->where(function ($q) {
                    $q->where('agent_orders.status', 'delayed')
                      ->orWhere(function ($q2) {
                          $q2->where('agent_orders.status', 'pending')
                             ->whereNotNull('agent_orders.expected_dispatch_date')
                             ->where('agent_orders.expected_dispatch_date', '<', date('Y-m-d'));
                      });
                });
            } elseif ($request->status === 'pending') {
                $query->where('agent_orders.status', 'pending')
                      ->where(function ($q) {
                          $q->whereNull('agent_orders.expected_dispatch_date')
                            ->orWhere('agent_orders.expected_dispatch_date', '>=', date('Y-m-d'));
                      });
            } else {
                $query->where('agent_orders.status', $request->status);
            }
        }
        if ($request->filled('sale_type')) $query->where('agent_orders.sale_type', $request->sale_type);
        if ($request->filled('payment_status')) {
            if ($request->payment_status == 'paid') {
                $query->whereRaw('(SELECT SUM(amount) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") >= agent_orders.grand_total');
            } elseif ($request->payment_status == 'unpaid') {
                $query->whereRaw('(SELECT COALESCE(SUM(amount),0) FROM payments WHERE paymentable_id = agent_orders.id AND paymentable_type = "App\\\\Models\\\\AgentOrder") < agent_orders.grand_total');
            }
        }
        if ($request->filled('from_date')) $query->whereDate('agent_orders.created_at', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('agent_orders.created_at', '<=', $request->to_date);

        return $query->orderBy('agent_orders.id', 'desc');
    }

    /* ─────────────────────────────────────────────────────────────
     * Export → PDF
     * ───────────────────────────────────────────────────────────── */
    public function exportPdf(Request $request)
    {
        $rows   = $this->buildOrdersQuery($request)->get();
        $totals = (object) [
            'total_orders'      => $rows->count(),
            'total_pieces'      => $rows->sum('total_qty'),
            'total_grand_total' => $rows->sum('grand_total'),
        ];
        $filters = array_filter($request->only(['agent_id','party_id','status','sale_type','payment_status','from_date','to_date']));

        $pdf = Pdf::loadView('admin.agent_orders.export_pdf', compact('rows', 'totals', 'filters'))
            ->setPaper('A4', 'landscape');

        $filename = 'Agent_Orders_' . now()->format('d-m-Y') . '.pdf';
        return $pdf->download($filename);
    }

    /* ─────────────────────────────────────────────────────────────
     * Export → Excel
     * ───────────────────────────────────────────────────────────── */
    public function exportExcel(Request $request)
    {
        $rows = $this->buildOrdersQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Agent Orders');

        // ── Header row ──
        $headers = ['#', 'Order ID', 'Order Date', 'Agent', 'Shop / Party', 'Order Type', 'Sale Type', 'Total Pcs', 'Grand Total (₹)', 'Status', 'Delivery Date'];
        $cols    = range('A', 'K');
        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('1e293b');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // ── Data rows ──
        $row = 2;
        foreach ($rows as $i => $o) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, '#ORD-' . str_pad($o->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($o->order_date)->format('d M Y'));
            $sheet->setCellValue('D' . $row, $o->agent_name);
            $sheet->setCellValue('E' . $row, $o->shop_name);
            $sheet->setCellValue('F' . $row, strtoupper($o->order_type ?? 'normal'));
            $sheet->setCellValue('G' . $row, ucfirst($o->sale_type ?? 'item'));
            $sheet->setCellValue('H' . $row, $o->total_qty);
            $sheet->setCellValue('I' . $row, $o->grand_total);
            
            $isDelayed = ($o->status == 'delayed') || ($o->status == 'pending' && $o->expected_dispatch_date && $o->expected_dispatch_date < date('Y-m-d'));
            $sheet->setCellValue('J' . $row, $isDelayed ? 'DELAYED' : strtoupper($o->status ?? ''));
            
            $sheet->setCellValue('K' . $row, $o->expected_dispatch_date ? \Carbon\Carbon::parse($o->expected_dispatch_date)->format('d M Y') : '-');

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('f8fafc');
            }
            $row++;
        }

        // ── Totals row ──
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $rows->sum('total_qty'));
        $sheet->setCellValue('I' . $row, $rows->sum('grand_total'));
        $sheet->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setRGB('fef9c3');

        // ── Column widths & borders ──
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle("A1:K{$row}")->getBorders()->getAllBorders()
              ->setBorderStyle(Border::BORDER_THIN);

        $filename = 'Agent_Orders_' . now()->format('d-m-Y_H-i') . '.xlsx';
        $path     = storage_path('app/public/' . $filename);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function show($id)
    {
        $order = AgentOrder::with(['shop', 'vendor', 'agent', 'dispatches'])->findOrFail($id);

        $party = $order->party_type === 'vendor' ? $order->vendor : $order->shop;

        // Map expected attributes for standard view usage
        $order->agent_name = $order->agent->name ?? "Direct (No Agent)";
        $order->shop_name = $party->name ?? "N/A";
        $order->shop_email = $party->email ?? "";
        $order->shop_phone = $party->phone ?? "";
        $order->shop_address = $party->address ?? "";

        // Sum total paid
        $order->total_paid = DB::table('payments')
            ->where('paymentable_id', $order->id)
            ->where('paymentable_type', 'App\Models\AgentOrder')
            ->sum('amount');

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
        } else {
            $itemsRaw = DB::table('agent_order_items')
                ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
                ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
                ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_items.*',
                    'agent_order_items.rack_id as item_rack_id',
                    'master_design_patterns.name as db_pattern_name',
                    'master_product_fittings.name as db_fitting_name'
                )
                ->get();

            $items = $itemsRaw->map(function ($item) {
                $itemRackId = $item->item_rack_id ?? null;
                $inventoryInfo = null;
                if ($itemRackId) {
                    $inventoryInfo = DB::table('racks')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->where('racks.id', $itemRackId)
                        ->select('racks.id as rack_id', 'racks.name as rack_name', 'storerooms.name as warehouse_name')
                        ->first();
                }

                if (!$inventoryInfo) {
                    $inventoryInfo = DB::table('domestic_inventories')
                        ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->where('domestic_inventories.product_id', $item->product_id)
                        ->where('domestic_inventories.color_id', $item->color_id)
                        ->where('domestic_inventories.size_set_id', $item->size_set_id)
                        ->where('domestic_inventories.total_boxes', '>', 0)
                        ->select('racks.id as rack_id', 'racks.name as rack_name', 'storerooms.name as warehouse_name')
                        ->orderByRaw("CASE WHEN LOWER(storerooms.name) = 'advance sample' THEN 1 ELSE 0 END")
                        ->first();
                        
                    if ($inventoryInfo && isset($inventoryInfo->rack_id)) {
                        DB::table('agent_order_items')
                            ->where('id', $item->id)
                            ->update(['rack_id' => $inventoryInfo->rack_id]);
                    }
                }

                $availableLocations = DB::table('domestic_inventories')
                    ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $item->product_id)
                    ->where('domestic_inventories.color_id', $item->color_id)
                    ->where('domestic_inventories.size_set_id', $item->size_set_id)
                    ->where('domestic_inventories.total_boxes', '>', 0)
                    ->select('racks.id as rack_id', 'racks.name as rack_name', 'storerooms.name as warehouse_name', 'domestic_inventories.total_boxes as boxes')
                    ->orderByRaw("CASE WHEN LOWER(storerooms.name) = 'advance sample' THEN 1 ELSE 0 END")
                    ->get();

                return (object) [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
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
                    'barcode' => $item->barcode,
                    'warehouse_name' => $inventoryInfo->warehouse_name ?? 'N/A',
                    'rack_name' => $inventoryInfo->rack_name ?? 'N/A',
                    'rack_id' => $inventoryInfo->rack_id ?? null,
                    'available_locations' => $availableLocations,
                ];
            });
        }

        return view('admin.agent_orders.show', compact('order', 'items'));
    }

    public function updateItemLocation(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'rack_id' => 'required|integer'
        ]);

        DB::table('agent_order_items')
            ->where('id', $request->item_id)
            ->update(['rack_id' => $request->rack_id]);

        return response()->json(['success' => true, 'message' => 'Location updated successfully!']);
    }

    public function destroy($id)
    {
        $order = AgentOrder::with(['items', 'fabricItems', 'dispatches'])->findOrFail($id);

        // Check if any items are dispatched or scanned
        $hasScanned = $order->items()->where('scanned_box_qty', '>', 0)->exists();
        $hasDispatch = $order->dispatches()->exists() ||
            $order->items()->whereNotNull('dispatched_at')->exists() ||
            $order->fabricItems()->whereNotNull('agent_order_dispatch_id')->exists() ||
            $order->status === 'dispatched';

        if ($hasDispatch || $hasScanned) {
            return redirect()->back()->with('error', 'Cannot delete order as some items have already been scanned or dispatched.');
        }

        // if ($order->paid_amount > 0) {
        //     return redirect()->back()->with('error', 'Cannot delete order as payments have already been recorded.');
        // }

        DB::beginTransaction();
        try {
            if ($order->sale_type === 'fabric') {
                foreach ($order->fabricItems as $item) {
                    FabricReceiptDetail::where('id', $item->fabric_receipt_detail_id)
                        ->increment('remaining_quantity', $item->meter);
                }
                $order->fabricItems()->delete();
            } else {
                // foreach ($order->items as $item) {
                //     if ($item->scanned_box_qty > 0) {
                //         // Restore inventory for scanned items
                //         $inventory = DB::table('domestic_inventories')
                //             ->where('barcode', $item->barcode)
                //             ->first();

                //         if ($inventory) {
                //             DB::table('domestic_inventories')
                //                 ->where('id', $inventory->id)
                //                 ->increment('total_boxes', $item->scanned_box_qty);
                //         }
                //     }
                // }
                $order->items()->delete();
            }

            $order->delete();

            DB::commit();
            return redirect()->route('admin.agent-orders.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete order: ' . $e->getMessage());
        }
    }

    public function edit($id, Request $request)
    {
        $order = AgentOrder::where('id', $id)->firstOrFail();

        if ($order->status != 'pending' && $order->status != 'delayed') {
            return redirect()->back()->with('error', 'Only pending or delayed orders can be edited.');
        }

        $shop = $order->party_type === 'vendor' ? $order->vendor : $order->shop;

        if (strtolower(trim($order->sale_type)) === 'fabric') {
            $fabrics = Fabric::where('status', 1)
                ->withSum([
                    'rolls as total_meters' => function ($query) {
                        $query->where('remaining_quantity', '>', 0);
                    }
                ], 'remaining_quantity')
                ->get();

            $existing_items = DB::table('agent_order_fabric_items')
                ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
                ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_fabric_items.*',
                    'fabrics.name as fabric_name',
                    'fabric_receipt_details.roll_number',
                    'fabric_receipt_details.batch_no',
                    'fabric_receipt_details.remaining_quantity as avail_now'
                )
                ->get();

            $gst_percentage = $order->gst_percentage ?? (DB::table('settings')->value('gst_order') ?? 5.00);

            $agent_id = $order->sales_agent_id;
            if ($agent_id == 0 || $agent_id === 'direct' || empty($agent_id)) {
                $agent = (object) ['id' => 'direct', 'name' => 'Direct'];
            } else {
                $agent = DB::table('sales_agents')->where('id', $agent_id)->first();
            }

            $agents = DB::table('sales_agents')->select('id', 'name')->where('status', 1)->get();
            $shops = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get();
            $vendors = DB::table('vendors')->select('id', 'name')->where('status', 1)->get();
            $salesMen = \App\Models\SalesMan::where('status', 1)->get();

            return view('admin.agent_orders.edit_fabric', compact('order', 'shop', 'agent', 'fabrics', 'existing_items', 'gst_percentage', 'agents', 'shops', 'vendors', 'salesMen'));
        }

        $designs = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->where('design_number', '!=', '')
            ->distinct()->pluck('design_number');

        $product_names = \App\Models\ProductionGoods::where('production_goods.status', 1)
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->whereRaw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, ""))) != ""')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, ""), " ", COALESCE(production_goods.name_of_garment, "")))) as full_name'))
            ->pluck('full_name');

        $colors = \App\Models\MasterColor::where('status', 1)
            ->select(DB::raw("CONCAT(name, ' (', id, ')') as full_name"))
            ->distinct()->pluck('full_name');

        $size_sets = \App\Models\MasterSizeMeasurement::where('status', 1)
            ->distinct()->pluck('name');

        $patterns = \App\Models\MasterDesignPattern::where('status', 1)->pluck('name', 'id');
        $fittings = \App\Models\MasterProductFitting::where('status', 1)->pluck('name', 'id');
        $product_natures = \App\Models\ProductNature::pluck('name', 'id');
        $fabric_types = \App\Models\FabricType::pluck('name', 'id');

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
                'product_id',
                'color_id',
                'size_set_id',
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
            ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        if ($request->has('product_name')) {
            $isSampleSet = $request->input('sample_set') == '1';
        } else {
            $isSampleSet = $order->is_sample_set == 1;
        }

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
            
            if ($isSampleSet) {
                $agentBatches = DB::table('fair_batches')
                    ->whereJsonContains('sales_agent_ids', (string)$order->sales_agent_id)
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
        if ($request->filled('pattern_id')) {
            $query->where('production_goods.master_pattern_id', $request->pattern_id);
        }
        if ($request->filled('fitting_id')) {
            $query->where('production_goods.master_product_fitting_id', $request->fitting_id);
        }
        if ($request->filled('product_nature_id')) {
            $query->where('production_goods.product_nature_id', $request->product_nature_id);
        }
        if ($request->filled('fabric_type_id')) {
            $query->where('production_goods.fabric_type_id', $request->fabric_type_id);
        }
        $boxes = $query->leftJoinSub($allocated, 'alloc', function ($join) {
            $join->on('domestic_inventories.product_id', '=', 'alloc.product_id')
                ->on('domestic_inventories.color_id', '=', 'alloc.color_id')
                ->on('domestic_inventories.size_set_id', '=', 'alloc.size_set_id');
        })
            ->leftJoin('agent_order_items as current_items', function ($join) use ($order) {
                $join->on('domestic_inventories.product_id', '=', 'current_items.product_id')
                    ->on('domestic_inventories.color_id', '=', 'current_items.color_id')
                    ->on('domestic_inventories.size_set_id', '=', 'current_items.size_set_id')
                    ->where('current_items.agent_order_id', '=', $order->id);
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

                DB::raw('(SUM(domestic_inventories.total_boxes) - MAX(COALESCE(alloc.total_allocated, 0))) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
                DB::raw('MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) as is_advance_sample'),
                DB::raw('MAX(current_items.box_qty) as current_order_qty')
            )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'master_colors.name',
                'master_size_measurements.name',

                DB::raw($discount_col)
            )
            ->havingRaw('(SUM(domestic_inventories.total_boxes) > MAX(COALESCE(alloc.total_allocated, 0)) OR MAX(current_items.box_qty) > 0) OR (MAX(CASE WHEN storerooms.name = \'ADVANCE SAMPLE\' THEN 1 ELSE 0 END) > 0)')
            ->orderByRaw('current_order_qty DESC')
            ->orderBy('production_goods.design_number');

        // Clone the builder before applying pagination, so we can fetch all selected items
        $queryForSelected = $query->clone();

        $boxes = $query->paginate(20)
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

        // Selected quantities for existing order with updated prices
        $existing_items_with_prices = $queryForSelected
            ->havingRaw('MAX(current_items.box_qty) > 0')
            ->get();

        $selected_quantities = $existing_items_with_prices->keyBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id;
            })
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
                    'qty' => $item->current_order_qty,
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
                'next_page' => $boxes->nextPageUrl() ? $boxes->currentPage() + 1 : null,
                'total_count' => $boxes->total()
            ]);
        }

        $agents = DB::table('sales_agents')->select('id', 'name')->where('status', 1)->get();
        $shops = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get();
        $vendors = DB::table('vendors')->select('id', 'name')->where('status', 1)->get();
        $salesMen = \App\Models\SalesMan::where('status', 1)->get();

        return view('admin.agent_orders.edit', compact('order', 'shop', 'designs', 'product_names', 'colors', 'size_sets', 'patterns', 'fittings', 'product_natures', 'fabric_types', 'boxes', 'boxImages', 'selected_quantities', 'gst_percentage', 'agents', 'shops', 'vendors', 'salesMen'));
    }

    public function update(Request $request, $id)
    {
        $order = AgentOrder::where('id', $id)->firstOrFail();

        if ($order->status != 'pending' && $order->status != 'delayed') {
            return response()->json(['success' => false, 'message' => 'Only pending or delayed orders can be edited.'], 403);
        }

        $sale_type = $request->sale_type ?: $order->sale_type;

        if ($sale_type === 'fabric') {
            $request->validate([
                'rolls' => 'required|array|min:1',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        } else {
            $request->validate([
                'variations' => 'required|array|min:1',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'gst_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        }

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];
        $fabric_items_to_create = [];

        if ($sale_type === 'fabric') {
            foreach ($request->rolls as $rollData) {
                $roll = DB::table('fabric_receipt_details')->where('id', $rollData['roll_id'])->first();
                if (!$roll)
                    continue;

                $meter = (float) $rollData['meter'];
                $price = (float) $rollData['price'];
                $total_amount += $meter * $price;
                $total_qty += $meter;

                $fabric_items_to_create[] = [
                    'agent_order_id' => $order->id,
                    'fabric_id' => $rollData['fabric_id'],
                    'fabric_receipt_detail_id' => $rollData['roll_id'],
                    'meter' => $meter,
                    'selling_price' => $price,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        } else {
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
                $selling_price = isset($var['unit_price']) ? (float) $var['unit_price'] : ($mrp - ($mrp * $brand_discount / 100));
                $selling_price = ceil($selling_price);
                $seriesName = ($product->series) ? $product->series->name : '';
                $product_name = trim($seriesName . ' ' . $product->name_of_garment);

                $fitting = $product->master_product_fitting_id ? \App\Models\MasterProductFitting::find($product->master_product_fitting_id) : null;
                $pattern = $product->master_pattern_id ? \App\Models\MasterDesignPattern::find($product->master_pattern_id) : null;

                // PCS per Box (Source of Truth: Front-end > Current Inventory > Master Config)
                $pcs_per_box = (float) DomesticInventory::where('status', 1)->where('product_id', $var['product_id'])
                    ->where('color_id', $var['color_id'])
                    ->where('size_set_id', $var['size_set_id'])
                    ->avg('quantity') ?? ($sizeSet->total_pieces ?? 0);

                $total_pcs = $var['qty'] * $pcs_per_box;

                $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'];

                $max_stock_rack = \App\Models\DomesticInventory::where('barcode', $barcode)
                    ->where('total_boxes', '>', 0)
                    ->orderBy('total_boxes', 'desc')
                    ->first();
                if (!$max_stock_rack) {
                    $max_stock_rack = \App\Models\DomesticInventory::where('barcode', $barcode)
                        ->orderBy('total_boxes', 'desc')
                        ->first();
                }
                if (!$max_stock_rack) {
                    $max_stock_rack = \App\Models\DomesticInventory::where('product_id', $var['product_id'])
                        ->orderBy('total_boxes', 'desc')
                        ->first();
                }
                $rack_id = $max_stock_rack ? $max_stock_rack->rack_id : null;

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
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $total_qty += $total_pcs;
                $total_amount += ($total_pcs * $selling_price);
            }
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
            $gst_percentage = $request->has('gst_percentage') ? $request->gst_percentage : ($order->gst_percentage ?: 5.00);
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
        }

        $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

        $expected_dispatch_date = $request->expected_dispatch_date ?: $order->expected_dispatch_date;

        DB::beginTransaction();
        try {
            $updateData = [
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'gst_percentage' => $gst_percentage,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'expected_dispatch_date' => $expected_dispatch_date,
                'status' => $request->status ?? $order->status,
                'is_sample_set' => $request->input('is_sample_set', 0),
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
                'remark' => $request->remark,
                'updated_at' => now()
            ];

            if ($request->filled('party_type')) {
                $updateData['party_type'] = $request->party_type;
                if ($request->party_type === 'vendor') {
                    $updateData['master_vendor_id'] = $request->master_vendor_id;
                    $updateData['master_customer_id'] = null;
                } else {
                    $updateData['master_customer_id'] = $request->master_customer_id;
                    $updateData['master_vendor_id'] = null;
                }
            }

            $order->update($updateData);

            // DELETE EXISTING ITEMS
            if ($sale_type === 'fabric') {
                // Restore inventory before deleting
                $oldItems = AgentOrderFabricItem::where('agent_order_id', $order->id)->get();
                foreach ($oldItems as $old) {
                    FabricReceiptDetail::where('id', $old->fabric_receipt_detail_id)->increment('remaining_quantity', $old->meter);
                }

                AgentOrderFabricItem::where('agent_order_id', $order->id)->delete();

                foreach ($fabric_items_to_create as $item) {
                    AgentOrderFabricItem::create($item);
                    // Deduct new inventory
                    FabricReceiptDetail::where('id', $item['fabric_receipt_detail_id'])->decrement('remaining_quantity', $item['meter']);
                }
            } else {
                AgentOrderItem::where('agent_order_id', $order->id)->delete();
                foreach ($items_to_create as $item) {
                    $item['agent_order_id'] = $order->id;
                    AgentOrderItem::create($item);
                }
            }

            $order->syncDispatchStatus();
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
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'),
                DB::raw('COALESCE(master_customers.email, vendors.email) as shop_email'),
                DB::raw('COALESCE(master_customers.phone, vendors.phone) as shop_phone'),
                DB::raw('COALESCE(master_customers.address, vendors.address) as shop_address')
            )
            ->first();

        if (!$order)
            abort(404);

        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')

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
            'order',
            'items',
            'settings',
            'selectedBrand',
            'type',
            'filteredSubtotal',
            'filteredGst',
            'filteredGrandTotal'
        ))
            ->setPaper('a4', 'portrait');

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

        // Override see_price if provided in request
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
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->where('agent_order_id', $id);
            
        if ($request->get('only_pending') == 1) {
            $itemsRaw->whereNull('agent_order_items.agent_order_dispatch_id');
        }

        $itemsRaw = $itemsRaw->select(
                'agent_order_items.*',
                'agent_order_items.rack_id as item_rack_id',
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

            $itemRackId = $first->item_rack_id ?? null;
            $inventoryInfo = null;

            if ($itemRackId) {
                $inventoryInfo = DB::table('racks')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('racks.id', $itemRackId)
                    ->select('racks.name as rack_name', 'storerooms.name as warehouse_name')
                    ->first();
            }

            if (!$inventoryInfo) {
                // Find rack info separately to avoid multiplying the order items rows
                $inventoryInfo = DB::table('domestic_inventories')
                    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $first->product_id)
                    ->where('domestic_inventories.color_id', $first->color_id)
                    ->where('domestic_inventories.size_set_id', $first->size_set_id)
                    ->where('domestic_inventories.total_boxes', '>', 0)
                    ->select('racks.name as rack_name', 'storerooms.name as warehouse_name')
                    ->first();
            }

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
            ];
        })->values();

        $settings = DB::table('settings')->first();
        $withWarehouse = ($request->get('with_warehouse') == 1);

        $pdf = Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings', 'withWarehouse'))
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

        $settings = DB::table('settings')->first();
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

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.order-pdf-fabric', compact('order', 'items', 'settings'));
            $fileName = "Order_Sheet_Fabric_{$order->id}.pdf";
        } else {
            $itemsRaw = DB::table('agent_order_items')
                ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
                ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
                ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
                ->where('agent_order_id', $id)
                ->select(
                    'agent_order_items.*',
                    'master_design_patterns.name as db_pattern_name',
                    'master_product_fittings.name as db_fitting_name'
                )
                ->get();

            $items = $itemsRaw->groupBy(function ($item) {
                return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
            })->map(function ($group) {
                $first = $group->first();

                // Find rack info separately to avoid multiplying the order items rows
                $inventoryInfo = DB::table('domestic_inventories')
                    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                    ->where('domestic_inventories.product_id', $first->product_id)
                    ->where('domestic_inventories.color_id', $first->color_id)
                    ->where('domestic_inventories.size_set_id', $first->size_set_id)
                    ->where('domestic_inventories.total_boxes', '>', 0)
                    ->select('racks.name as rack_name', 'storerooms.name as warehouse_name')
                    ->first();

                return (object) [
                    'product_name' => $first->product_name,
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
                ];
            })->values();

            $withWarehouse = ($request->get('with_warehouse') == 1);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.order-pdf', compact('order', 'items', 'settings', 'withWarehouse'))
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

    public function downloadPackingSlip(Request $request, $id)
    {
        $order = DB::table('agent_orders')
            ->leftJoin('sales_agents', 'agent_orders.sales_agent_id', '=', 'sales_agents.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name'),
                DB::raw('COALESCE(master_customers.email, vendors.email) as shop_email'),
                DB::raw('COALESCE(master_customers.phone, vendors.phone) as shop_phone'),
                DB::raw('COALESCE(master_customers.address, vendors.address) as shop_address')
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

            // 2. Update Party Balance (Decrease because they now owe this amount)
            if ($order->party_type === 'vendor') {
                $vendor = \App\Models\Vendor::find($order->master_vendor_id);
                if ($vendor) {
                    $vendor->balance -= $dispatchTotal;
                    $vendor->save();
                }
            } else {
                $customer = \App\Models\MasterCustomer::find($order->master_customer_id);
                if ($customer) {
                    $customer->balance -= $dispatchTotal;
                    $customer->save();
                }
            }

            // 3. Delete boxes from DomesticInventory (remove from stock)
            DB::table('domestic_inventories')->whereIn('box_no', $box_nos)->delete();

            // Create Dispatch Log Entry
            $dispatch = \App\Models\AgentOrderDispatch::create([
                'party_type' => $order->party_type ?? 'customer',
                'master_customer_id' => $order->master_customer_id,
                'master_vendor_id' => $order->master_vendor_id,
                'sales_agent_id' => $order->sales_agent_id,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => now(),
                'total_amount' => $dispatchSubtotal,
                'gst_amount' => $dispatchGst,
                'gst_percentage' => $gstPercentage,
                'grand_total' => $dispatchTotal,
                'remark' => request()->remark,
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
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->where('agent_orders.id', $id)
            ->select(
                'agent_orders.*',
                DB::raw('COALESCE(sales_agents.name, "Direct (No Agent)") as agent_name'),
                DB::raw('COALESCE(master_customers.name, vendors.name) as shop_name')
            )
            ->first();

        if (!$order)
            abort(404);

        if (strtolower(trim($order->sale_type)) === 'fabric') {
            $items = DB::table('agent_order_fabric_items')
                ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
                ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
                ->where('agent_order_id', $id)
                ->where('agent_order_fabric_items.status', '!=', 'dispatched')
                ->select(
                    'agent_order_fabric_items.*',
                    'fabrics.name as fabric_name',
                    'fabric_receipt_details.roll_number',
                    'fabric_receipt_details.batch_no'
                )
                ->get();
            return view('admin.agent_orders.dispatch_fabric', compact('order', 'items'));
        }

        // Only show items that are NOT yet dispatched
        $items = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->where('agent_order_id', $id)
            ->whereNull('dispatched_at')
            ->select(
                'agent_order_items.*',
                'agent_order_items.rack_id as item_rack_id',
                'master_design_patterns.name as db_pattern_name',
                'master_product_fittings.name as db_fitting_name'
            )
            ->get();

        $groupedItems = [];
        foreach ($items as $item) {
            $key = $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id;
            if (!isset($groupedItems[$key])) {
                $itemRackId = $item->item_rack_id ?? null;
                $inventoryInfo = null;
                
                if ($itemRackId) {
                    $inventoryInfo = DB::table('racks')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->where('racks.id', $itemRackId)
                        ->select('racks.id as rack_id', 'racks.name as rack_name', 'storerooms.name as warehouse_name')
                        ->first();
                }

                if (!$inventoryInfo) {
                    $inventoryInfo = DB::table('domestic_inventories')
                        ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->where('domestic_inventories.product_id', $item->product_id)
                        ->where('domestic_inventories.color_id', $item->color_id)
                        ->where('domestic_inventories.size_set_id', $item->size_set_id)
                        ->where('domestic_inventories.total_boxes', '>', 0)
                        ->select('racks.id as rack_id', 'racks.name as rack_name', 'storerooms.name as warehouse_name')
                        ->first();
                }

                $groupedItems[$key] = [
                    'product_name' => $item->product_name,
                    'design_number' => $item->design_number,
                    'color_name' => $item->color_name,
                    'size_set_name' => $item->size_set_name,
                    'pattern_name' => $item->db_pattern_name ?? $item->pattern_name,
                    'fitting_name' => $item->db_fitting_name ?? $item->fitting_name,
                    'warehouse_name' => $inventoryInfo->warehouse_name ?? 'N/A',
                    'rack_name' => $inventoryInfo->rack_name ?? 'N/A',
                    'rack_id' => $inventoryInfo->rack_id ?? null,
                    'required' => 0,
                    'scanned' => 0,
                    'barcode' => "D{$item->product_id}S{$item->size_set_id}C{$item->color_id}",
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

        $scannedTotal = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->whereNull('dispatched_at')
            ->sum(DB::raw('scanned_quantity * selling_price'));
        $companies = \App\Models\Company::where('status', 1)->get();

        return view('admin.agent_orders.dispatch_scan', compact('order', 'groupedItems', 'scannedBoxes', 'scannedTotal', 'companies'));
    }

    public function processScan(Request $request, $id)
    {
        $input = trim($request->barcode);
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
        $input = parseCompactBarcode($input);

        if (empty($input)) {
            return response()->json(['success' => false, 'message' => 'No barcode received.']);
        }

        // 1. Find the inventory record by barcode (Inventory is now consolidated)
        // Check if input is a compact barcode (D1S1C1P1F1) or a unique box_no
        // $inventory = DB::table('domestic_inventories')
        //     ->where(function ($q) use ($input) {
        //         $q->where('barcode', $input)->orWhere('box_no', $input);
        //     })
        //     // ->where('order_main_id', 0)
        //     ->where('total_boxes', '>', 0)
        //     ->first();

        if (preg_match('/^D(\d+)S(\d+)C(\d+)/', $input, $matches)) {
            $inventory = DB::table('domestic_inventories')
                ->where('product_id', $matches[1])
                ->where('size_set_id', $matches[2])
                ->where('color_id', $matches[3])
                ->where('total_boxes', '>', 0)
                ->first();
        } else {
            $inventory = DB::table('domestic_inventories')
                ->where('barcode', $input)
                ->where('total_boxes', '>', 0)
                ->first();
        }

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'No available stock found in inventory for: ' . $input]);
        }

        // 2. Find the pending order item for this design
        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('product_id', $inventory->product_id)
            ->where('color_id', $inventory->color_id)
            ->where('size_set_id', $inventory->size_set_id)
            ->whereNull('dispatched_at')
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

            $scannedTotal = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->whereNull('dispatched_at')
                ->sum(DB::raw('scanned_quantity * selling_price'));

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
                'barcode' => $item->barcode,
                'scanned_total' => $scannedTotal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function removeScan(Request $request, $id)
    {
        $barcode = trim($request->barcode);
        $barcode = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode);
        $barcode = parseCompactBarcode($barcode);

        // 1. Find the summary inventory record for this barcode
        if (preg_match('/^D(\d+)S(\d+)C(\d+)/', $barcode, $matches)) {
            $inventory = DB::table('domestic_inventories')
                ->where('product_id', $matches[1])
                ->where('size_set_id', $matches[2])
                ->where('color_id', $matches[3])
                ->where('order_main_id', 0)
                ->first();
        } else {
            $inventory = DB::table('domestic_inventories')
                ->where('barcode', $barcode)
                ->where('order_main_id', 0)
                ->first();
        }

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'Inventory record for this box not found.']);
        }

        // 2. Find the aggregate order item for this design in this order
        $item = DB::table('agent_order_items')
            ->where('agent_order_id', $id)
            ->where('product_id', $inventory->product_id)
            ->where('color_id', $inventory->color_id)
            ->where('size_set_id', $inventory->size_set_id)
            ->whereNull('dispatched_at')
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

            $scannedTotal = DB::table('agent_order_items')
                ->where('agent_order_id', $id)
                ->whereNull('dispatched_at')
                ->sum(DB::raw('scanned_quantity * selling_price'));

            return response()->json([
                'success' => true,
                'message' => 'Scan removed successfully!',
                'variation_key' => "{$item->product_id}_{$item->color_id}_{$item->size_set_id}",
                'scanned' => $item->scanned_box_qty - 1,
                'required' => $item->box_qty,
                'design_number' => $item->design_number,
                'product_name' => $item->product_name,
                'color_name' => $item->color_name,
                'barcode' => $item->barcode,
                'scanned_total' => $scannedTotal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function dispatchFabric(Request $request, $id)
    {
        $selectedItems = $request->input('fabric_item_ids');
        if (empty($selectedItems)) {
            return redirect()->back()->with('error', 'No rolls selected for dispatch.');
        }

        $order = AgentOrder::findOrFail($id);

        DB::beginTransaction();
        try {
            // Create dispatch header
            $dispatch = \App\Models\AgentOrderDispatch::create([
                'party_type' => $order->party_type ?? 'customer',
                'master_customer_id' => $order->master_customer_id,
                'master_vendor_id' => $order->master_vendor_id,
                'sales_agent_id' => $order->sales_agent_id,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => now(),
                'remark' => $request->remark,
            ]);

            $subtotal = 0;
            $itemsToUpdate = DB::table('agent_order_fabric_items')
                ->whereIn('id', $selectedItems)
                ->get();

            foreach ($itemsToUpdate as $item) {
                $subtotal += ($item->meter * $item->selling_price);

                DB::table('agent_order_fabric_items')->where('id', $item->id)->update([
                    'status' => 'dispatched',
                    'dispatched_at' => now(),
                    'agent_order_dispatch_id' => $dispatch->id
                ]);
            }

            $gst = $subtotal * (($order->gst_percentage ?? 5) / 100);

            $dispatch->total_amount = $subtotal;
            $dispatch->gst_amount = $gst;
            $dispatch->grand_total = ceil($subtotal + $gst);
            $dispatch->save();

            // Update Party Balance (Decrease on dispatch)
            if ($order->party_type === 'vendor') {
                $vendor = \App\Models\Vendor::find($order->master_vendor_id);
                if ($vendor) {
                    $vendor->balance -= $dispatch->grand_total;
                    $vendor->save();
                }
            } else {
                $customer = \App\Models\MasterCustomer::find($order->master_customer_id);
                if ($customer) {
                    $customer->balance -= $dispatch->grand_total;
                    $customer->save();
                }
            }

            // Link to Dispatch record (pivot-like join for historical tracking)
            \App\Models\AgentOrderDispatchItem::create([
                'agent_order_dispatch_id' => $dispatch->id,
                'agent_order_id' => $order->id
            ]);

            // Update order status
            $remaining = DB::table('agent_order_fabric_items')
                ->where('agent_order_id', $order->id)
                ->where('status', '!=', 'dispatched')
                ->count();

            $order->status = ($remaining > 0) ? 'partially_dispatched' : 'dispatched';
            $order->save();

            DB::commit();
            return redirect()->route('admin.agent-orders.dispatches.show', $dispatch->id)->with('success', 'Fabric rolls dispatched successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function indexDispatches(Request $request)
    {
        $query = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])
            ->latest();

        if ($request->filled('shop_id')) {
            $query->where('master_customer_id', $request->shop_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('master_vendor_id', $request->vendor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('dispatch_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('dispatch_date', '<=', $request->to_date);
        }

        if ($request->filled('bill_no')) {
            $query->where('bill_no', 'like', '%' . $request->bill_no . '%');
        }

        if ($request->filled('dispatch_type')) {
            $dispatchType = $request->dispatch_type;
            $query->whereHas('orders', function ($q) use ($dispatchType) {
                $q->where(function ($sub) use ($dispatchType) {
                    $sub->where('sale_type', $dispatchType)
                        ->orWhere('order_type', $dispatchType);
                });
            });
        }

        $totalGrandTotal = $query->sum('grand_total');

        $dispatches = $query->paginate(20);
        $shops = DB::table('master_customers')->select('id', 'name')->get();
        $vendors = DB::table('vendors')->select('id', 'name')->get();

        return view('admin.agent_orders.dispatches.index', compact('dispatches', 'shops', 'vendors', 'totalGrandTotal'));
    }

    public function dispatchShow($id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent', 'orders.items'])->findOrFail($id);

        $items = DB::table('agent_order_items')
            ->leftJoin('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->leftJoin('brands', 'production_goods.brand_id', '=', 'brands.id')
            ->where('agent_order_dispatch_id', '=', $id)
            ->select('agent_order_items.*', 'brands.name as brand_name', 'brands.id as brand_id')
            ->get();

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->color_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'group_key' => $first->product_id . '_' . $first->color_id . '_' . $first->size_set_id,
                'product_id' => $first->product_id,
                'color_id' => $first->color_id,
                'size_set_id' => $first->size_set_id,
                'brand_id' => $first->brand_id ?? 'unknown',
                'brand_name' => $first->brand_name ?? 'Unknown Brand',
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

        $fabricItems = DB::table('agent_order_fabric_items')
            ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
            ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
            ->where('agent_order_dispatch_id', $id)
            ->select(
                'agent_order_fabric_items.*',
                'fabrics.name as fabric_name',
                'fabric_receipt_details.roll_number',
                'fabric_receipt_details.batch_no'
            )
            ->get();

        $isFabric = $dispatch->orders->contains('sale_type', 'fabric') || $fabricItems->isNotEmpty();
        $companies = \App\Models\Company::where('status', 1)->get();

        return view('admin.agent_orders.dispatches.show', compact('dispatch', 'groupedItems', 'fabricItems', 'isFabric', 'companies'));
    }

    public function dispatchSelected(Request $request)
    {
        $orderIds = $request->input('order_ids');
        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'No orders selected.');
        }

        $orders = \App\Models\AgentOrder::whereIn('id', $orderIds)->get();

        // Validation: Verify all orders belong to the same shop/party
        $firstOrder = $orders->first();
        $partyType = $firstOrder->party_type;
        $partyId = $partyType == 'vendor' ? $firstOrder->master_vendor_id : $firstOrder->master_customer_id;

        foreach ($orders as $order) {
            if ($order->party_type !== $partyType) {
                return redirect()->back()->with('error', 'Cannot mix customer and vendor orders in one dispatch.');
            }
            $currentId = $partyType == 'vendor' ? $order->master_vendor_id : $order->master_customer_id;
            if ($currentId !== $partyId) {
                return redirect()->back()->with('error', 'Please select orders for the same shop/party.');
            }
        }

        // Ensure at least one item is scanned for dispatch
        $hasScanned = DB::table('agent_order_items')
            ->whereIn('agent_order_id', $orderIds)
            ->where('scanned_box_qty', '>', 0)
            ->whereNull('dispatched_at')
            ->exists();

        if (!$hasScanned) {
            return redirect()->back()->with('error', 'No items have been scanned for dispatch.');
        }

        DB::beginTransaction();
        try {
            // Create dispatch header
            $dispatch = \App\Models\AgentOrderDispatch::create([
                'party_type' => $firstOrder->party_type ?? 'customer',
                'master_customer_id' => $firstOrder->master_customer_id,
                'master_vendor_id' => $firstOrder->master_vendor_id,
                'sales_agent_id' => $firstOrder->sales_agent_id,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => $request->dispatch_date ? date('Y-m-d H:i:s', strtotime($request->dispatch_date)) : now(),
                'remark' => $request->remark,
                'company_id' => $request->company_id,
            ]);

            $calculatedSubtotal = 0;

            foreach ($orders as $order) {
                if ($order->status == 'dispatched')
                    continue;

                $itemsToDispatch = DB::table('agent_order_items')
                    ->where('agent_order_id', $order->id)
                    ->where('scanned_box_qty', '>', 0)
                    ->whereNull('dispatched_at')
                    ->get();

                if ($itemsToDispatch->isEmpty())
                    continue;

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

                $calculatedSubtotal += $subtotal;

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

            // Update Dispatch Totals from manual overrides or calculation
            $finalSubtotal = (float) ($request->total_amount ?? $calculatedSubtotal);
            $discount_amount = (float) ($request->discount_amount ?? 0);
            $gst_percentage = (float) ($request->gst_percentage ?? 5);
            $other_charges = (float) ($request->other_charges ?? 0);

            $taxable_amount = $finalSubtotal - $discount_amount;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
            $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

            $dispatch->update([
                'total_amount' => $finalSubtotal,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
            ]);

            // Finally: Update Party Balance (Decrease model)
            if ($dispatch->party_type === 'vendor') {
                $vendor = \App\Models\Vendor::find($dispatch->master_vendor_id);
                if ($vendor) {
                    $vendor->balance -= $dispatch->grand_total;
                    $vendor->save();
                }
            } else {
                $customer = \App\Models\MasterCustomer::find($dispatch->master_customer_id);
                if ($customer) {
                    $customer->balance -= $dispatch->grand_total;
                    $customer->save();
                }
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
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();

        $brandId = $request->get('brand_id');
        $type = $request->get('type'); // 'actual' if selected

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $fabricItems = DB::table('agent_order_fabric_items')
            ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
            ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
            ->where('agent_order_dispatch_id', $id)
            ->select(
                'agent_order_fabric_items.*',
                'fabrics.name as fabric_name',
                'fabric_receipt_details.roll_number',
                'fabric_receipt_details.batch_no'
            )
            ->get();

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();

        if ($fabricItems->isNotEmpty() && $items->isEmpty()) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.dispatches.invoice-pdf-fabric', compact(
                'dispatch',
                'fabricItems',
                'settings',
                'brandId'
            ));
            return $pdf->download("Dispatch_Invoice_Fabric_{$dispatch->id}.pdf");
        }
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
            'dispatch',
            'groupedItems',
            'settings',
            'selectedBrand',
            'type',
            'filteredSubtotal',
            'filteredGst',
            'filteredGrandTotal',
            'brandCount',
            'discountAmt',
            'brandId'
        ));
        return $pdf->download('Dispatch_Invoice_' . $dispatch->id . '.pdf');
    }

    public function downloadDispatchRetailInvoice(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
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
            $filteredSubtotal = $dispatch->total_amount;
            $discountAmt = $dispatch->discount_amount ?? 0;
            $filteredGst = $dispatch->gst_amount;
            $filteredGrandTotal = $dispatch->grand_total;
        } else {
            $filteredGst = $filteredSubtotal * ($gstPercent / 100);
            $filteredGrandTotal = $filteredSubtotal + $filteredGst;
        }

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            $colors = $group->pluck('color_name')->unique()->filter(function($c){ return !empty(trim($c)); })->implode(', ');
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $colors,
                'size_set_name' => $first->size_set_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->sum('box_qty'),
            ];
        })->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.dispatches.retail-invoice-pdf', compact(
            'dispatch',
            'groupedItems',
            'settings',
            'selectedBrand',
            'type',
            'filteredSubtotal',
            'filteredGst',
            'filteredGrandTotal',
            'brandCount',
            'discountAmt',
            'brandId'
        ));
        return $pdf->download('Dispatch_Retail_Invoice_' . $dispatch->id . '.pdf');
    }

    public function downloadDispatchRetailInvoiceExcel(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
        
        $brandId = $request->get('brand_id');
        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();

        $filteredSubtotal = 0;
        foreach ($items as $item) {
            $filteredSubtotal += ($item->quantity * $item->selling_price);
        }

        $gstPercent = $dispatch->gst_percentage ?? 5;
        $discountAmt = 0;

        if (!$brandId) {
            $filteredSubtotal = $dispatch->total_amount;
            $discountAmt = $dispatch->discount_amount ?? 0;
            $filteredGst = $dispatch->gst_amount;
            $filteredGrandTotal = $dispatch->grand_total;
        } else {
            $filteredGst = $filteredSubtotal * ($gstPercent / 100);
            $filteredGrandTotal = $filteredSubtotal + $filteredGst;
        }

        $groupedItems = $items->groupBy(function ($item) {
            return $item->product_id . '_' . $item->size_set_id . '_' . $item->mrp . '_' . $item->selling_price;
        })->map(function ($group) {
            $first = $group->first();
            $colors = $group->pluck('color_name')->unique()->filter(function($c){ return !empty(trim($c)); })->implode(', ');
            return (object) [
                'product_name' => $first->product_name,
                'design_number' => $first->design_number,
                'color_name' => $colors,
                'size_set_name' => $first->size_set_name,
                'mrp' => $first->mrp,
                'selling_price' => $first->selling_price,
                'total_qty' => $group->sum('quantity'),
                'box_count' => $group->sum('box_qty'),
            ];
        })->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Retail Invoice');

        $headers = ['S.N.', 'Description of Goods', 'PCs Qty.', 'BoX Qty.', 'Unit', 'MRP', 'Disc. %', 'Price', 'Total Amount'];
        $cols    = range('A', 'I');
        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        $tP = 0;
        $tB = 0;
        foreach ($groupedItems as $index => $item) {
            $disc = ($item->mrp > 0 && $item->mrp > $item->selling_price) ? round((($item->mrp - $item->selling_price) / $item->mrp) * 100, 2) . '%' : '-';
            
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, strtoupper($item->product_name . ' ' . $item->size_set_name));
            $sheet->setCellValue('C' . $row, $item->total_qty);
            $sheet->setCellValue('D' . $row, $item->box_count);
            $sheet->setCellValue('E' . $row, 'BOX');
            $sheet->setCellValue('F' . $row, $item->mrp);
            $sheet->setCellValue('G' . $row, $disc);
            $sheet->setCellValue('H' . $row, $item->selling_price);
            $sheet->setCellValue('I' . $row, $item->total_qty * $item->selling_price);
            
            $tP += $item->total_qty;
            $tB += $item->box_count;
            $row++;
        }

        $sheet->setCellValue('C' . $row, $tP . ' Pcs');
        $sheet->setCellValue('D' . $row, $tB . ' Box');
        $sheet->setCellValue('I' . $row, $filteredSubtotal);
        $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
        $row++;

        if ($discountAmt > 0) {
            $sheet->setCellValue('H' . $row, 'Extra Discount');
            $sheet->setCellValue('I' . $row, '-' . $discountAmt);
            $row++;
        }

        $sheet->setCellValue('H' . $row, 'GST');
        $sheet->setCellValue('I' . $row, $filteredGst);
        $row++;

        if ($dispatch->other_charges > 0 && !$brandId) {
            $sheet->setCellValue('H' . $row, 'Other Charges');
            $sheet->setCellValue('I' . $row, $dispatch->other_charges);
            $row++;
        }

        $sheet->setCellValue('H' . $row, 'Grand Total');
        $sheet->setCellValue('I' . $row, $filteredGrandTotal);
        $sheet->getStyle('H' . $row . ':I' . $row)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Dispatch_Retail_Invoice_' . $dispatch->id . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }


    public function updateDispatchItems(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::findOrFail($id);
        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            $oldGrandTotal = $dispatch->grand_total;
            
            // Loop through submitted items to update prices
            foreach ($items as $itemData) {
                if (isset($itemData['product_id'], $itemData['color_id'], $itemData['size_set_id'], $itemData['selling_price'])) {
                    DB::table('agent_order_items')
                        ->where('agent_order_dispatch_id', $id)
                        ->where('product_id', $itemData['product_id'])
                        ->where('color_id', $itemData['color_id'])
                        ->where('size_set_id', $itemData['size_set_id'])
                        ->update(['selling_price' => $itemData['selling_price']]);
                }
            }

            // Recalculate Subtotal
            $dispatchItems = DB::table('agent_order_items')->where('agent_order_dispatch_id', $id)->get();
            $newTotalAmount = $dispatchItems->sum(function ($item) {
                return $item->quantity * $item->selling_price;
            });
            
            // Recalculate Fabric Subtotal if any
            $fabricItems = DB::table('agent_order_fabric_items')->where('agent_order_dispatch_id', $id)->get();
            $newTotalAmount += $fabricItems->sum(function ($item) {
                return $item->meter * $item->selling_price;
            });

            $taxable_amount = $newTotalAmount - ($dispatch->discount_amount ?? 0);
            $gst_amount = $taxable_amount * (($dispatch->gst_percentage ?? 0) / 100);
            $grandTotal = $taxable_amount + $gst_amount + ($dispatch->other_charges ?? 0);

            $dispatch->update([
                'total_amount' => $newTotalAmount,
                'gst_amount' => $gst_amount,
                'grand_total' => $grandTotal,
            ]);

            // Adjust Party Balance
            if ($dispatch->party_type === 'vendor') {
                $vendor = \App\Models\Vendor::find($dispatch->master_vendor_id);
                if ($vendor) {
                    $vendor->balance = $vendor->balance + $oldGrandTotal - $grandTotal;
                    $vendor->save();
                }
            } else {
                $shop = \App\Models\MasterCustomer::find($dispatch->master_customer_id);
                if ($shop) {
                    $shop->balance = $shop->balance + $oldGrandTotal - $grandTotal;
                    $shop->save();
                }
            }

            // Also recalculate AgentOrder total_amount for all related orders
            $orderIds = $dispatchItems->pluck('agent_order_id')->merge($fabricItems->pluck('agent_order_id'))->unique();
            foreach ($orderIds as $orderId) {
                $orderTotal = DB::table('agent_order_items')->where('agent_order_id', $orderId)->sum(DB::raw('quantity * selling_price'));
                $orderTotal += DB::table('agent_order_fabric_items')->where('agent_order_id', $orderId)->sum(DB::raw('meter * selling_price'));
                \App\Models\AgentOrder::where('id', $orderId)->update(['total_amount' => $orderTotal]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Items updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateDispatchInvoice(Request $request, $id)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'other_charges' => 'nullable|numeric|min:0',
            'company_id' => 'nullable|integer',
            'bill_no' => 'nullable|string',
        ]);

        $dispatch = \App\Models\AgentOrderDispatch::findOrFail($id);

        $oldGrandTotal = $dispatch->grand_total;

        $total_amount = $request->total_amount;
        $discount_amount = $request->discount_amount ?? 0;
        $gst_percentage = $request->gst_percentage ?? 5;
        $other_charges = $request->other_charges ?? 0;

        $taxable_amount = $total_amount - $discount_amount;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grandTotal = $taxable_amount + $gst_amount + $other_charges;

        DB::beginTransaction();
        try {
            $dispatch->update([
                'dispatch_date' => $request->dispatch_date ?? $dispatch->dispatch_date,
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grandTotal,
                'remark' => $request->remark,
                'company_id' => $request->company_id,
                'bill_no' => $request->bill_no,
            ]);

            // Adjust Party Balance (Decrease model)
            if ($dispatch->party_type === 'vendor') {
                $vendor = \App\Models\Vendor::find($dispatch->master_vendor_id);
                if ($vendor) {
                    // Add old and subtract new to adjust the decrease
                    $vendor->balance = $vendor->balance + $oldGrandTotal - $grandTotal;
                    $vendor->save();
                }
            } else {
                $customer = \App\Models\MasterCustomer::find($dispatch->master_customer_id);
                if ($customer) {
                    // Add old and subtract new to adjust the decrease
                    $customer->balance = $customer->balance + $oldGrandTotal - $grandTotal;
                    $customer->save();
                }
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
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();

        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $fabricItems = DB::table('agent_order_fabric_items')
            ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
            ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
            ->where('agent_order_dispatch_id', $id)
            ->select(
                'agent_order_fabric_items.*',
                'fabrics.name as fabric_name',
                'fabric_receipt_details.roll_number',
                'fabric_receipt_details.batch_no'
            )
            ->get();

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();

        if ($fabricItems->isNotEmpty() && $items->isEmpty()) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.dispatches.packing-slip-pdf-fabric', compact(
                'dispatch',
                'fabricItems',
                'settings'
            ));
            return $pdf->download("Packing_Slip_Fabric_{$dispatch->id}.pdf");
        }
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

    public function sendWhatsappDispatchInvoice(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();
        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $party = $dispatch->party_type === 'vendor' ? $dispatch->vendor : $dispatch->shop;
        $phone = $party->phone ?? '';

        if ($request->has('phone') && !empty($request->phone)) {
            $phone = $request->phone;
        }

        if (empty($phone)) {
            return back()->with('error', 'No phone number available for this party.');
        }

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $fabricItems = DB::table('agent_order_fabric_items')
            ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
            ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
            ->where('agent_order_dispatch_id', $id)
            ->select(
                'agent_order_fabric_items.*',
                'fabrics.name as fabric_name',
                'fabric_receipt_details.roll_number',
                'fabric_receipt_details.batch_no'
            )
            ->get();

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();

        if ($fabricItems->isNotEmpty() && $items->isEmpty()) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.dispatches.invoice-pdf-fabric', compact(
                'dispatch', 'fabricItems', 'settings', 'brandId'
            ));
            $fileName = "Dispatch_Invoice_Fabric_{$dispatch->id}.pdf";
        } else {
            $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;
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
                $filteredSubtotal = $dispatch->total_amount;
                $discountAmt = $dispatch->discount_amount ?? 0;
                $filteredGst = $dispatch->gst_amount;
                $filteredGrandTotal = $dispatch->grand_total;
            } else {
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
                'filteredSubtotal', 'filteredGst', 'filteredGrandTotal', 'brandCount', 'discountAmt', 'brandId'
            ));
            $fileName = "Dispatch_Invoice_{$dispatch->id}.pdf";
        }

        $dir = public_path('whatsapp_pdfs');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdf->save($dir . '/' . $fileName);
        $physicalPath = $dir . '/' . $fileName;

        $msg = "Dear {$party->name},\n\nYour Dispatch Invoice #{$dispatch->id} has been generated.\nPlease find it attached.\n\nThank you!";
        $status = send_whatsapp_attachment($phone, $msg, $physicalPath, $fileName);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp Invoice sent to ' . $phone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
    }

    public function sendWhatsappDispatchPackingSlip(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);
        $settings = DB::table('settings')->first();
        $brandId = $request->get('brand_id');
        $type = $request->get('type');

        $party = $dispatch->party_type === 'vendor' ? $dispatch->vendor : $dispatch->shop;
        $phone = $party->phone ?? '';

        if ($request->has('phone') && !empty($request->phone)) {
            $phone = $request->phone;
        }

        if (empty($phone)) {
            return back()->with('error', 'No phone number available for this party.');
        }

        $query = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('agent_order_items.agent_order_dispatch_id', $id);

        if ($brandId) {
            $query->where('production_goods.brand_id', $brandId);
        }

        $fabricItems = DB::table('agent_order_fabric_items')
            ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
            ->join('fabric_receipt_details', 'agent_order_fabric_items.fabric_receipt_detail_id', '=', 'fabric_receipt_details.id')
            ->where('agent_order_dispatch_id', $id)
            ->select(
                'agent_order_fabric_items.*',
                'fabrics.name as fabric_name',
                'fabric_receipt_details.roll_number',
                'fabric_receipt_details.batch_no'
            )
            ->get();

        $items = $query->select('agent_order_items.*', 'production_goods.brand_id')->get();

        if ($fabricItems->isNotEmpty() && $items->isEmpty()) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.dispatches.packing-slip-pdf-fabric', compact(
                'dispatch', 'fabricItems', 'settings'
            ));
            $fileName = "Packing_Slip_Fabric_{$dispatch->id}.pdf";
        } else {
            $selectedBrand = $brandId ? \App\Models\Brand::find($brandId) : null;

            $query = DB::table('agent_order_items')
                ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
                ->where('agent_order_id', $dispatch->agent_order_id)
                ->whereNotNull('box_no');

            if ($brandId) {
                $query->where('production_goods.brand_id', $brandId);
            }

            $itemsRaw = $query->select('agent_order_items.*')->get();
            $uniqueBrandIds = [];
            foreach ($itemsRaw as $item) {
                if ($item->brand_id) {
                    $uniqueBrandIds[$item->brand_id] = true;
                }
            }
            $brandCount = count($uniqueBrandIds);

            $groupedItems = $itemsRaw->groupBy(function ($item) {
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
            $fileName = "Packing_Slip_{$dispatch->id}.pdf";
        }

        $dir = public_path('whatsapp_pdfs');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdf->save($dir . '/' . $fileName);
        $physicalPath = $dir . '/' . $fileName;

        $msg = "Dear {$party->name},\n\nYour Dispatch Packing Slip #{$dispatch->id} has been generated.\nPlease find it attached.\n\nThank you!";
        $status = send_whatsapp_attachment($phone, $msg, $physicalPath, $fileName);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp Packing Slip sent to ' . $phone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
    }

    public function indexReturns(Request $request)
    {
        $query = AgentOrderReturn::with(['dispatch.vendor', 'dispatch.shop', 'dispatch.agent', 'creator']);

        if ($request->filled('shop_id')) {
            $query->whereHas('dispatch', function($q) use ($request) {
                $q->where('master_customer_id', $request->shop_id);
            });
        }
        if ($request->filled('vendor_id')) {
            $query->whereHas('dispatch', function($q) use ($request) {
                $q->where('master_vendor_id', $request->vendor_id);
            });
        }
        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->to_date);
        }

        $returns = $query->latest()->paginate(20)->appends($request->query());
        
        $shops = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get();
        $vendors = DB::table('vendors')->select('id', 'name')->where('status', 1)->get();

        return view('admin.agent_orders.returns.index', compact('returns', 'shops', 'vendors'));
    }

    public function returnCreate($id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])->findOrFail($id);

        $items = AgentOrderItem::where('agent_order_dispatch_id', $id)->get();
        $fabricItems = AgentOrderFabricItem::with('fabric', 'roll')->where('agent_order_dispatch_id', $id)->get();

        // Calculate already returned quantities
        $returnedQuantities = DB::table('agent_order_return_items')
            ->join('agent_order_returns', 'agent_order_return_items.agent_order_return_id', '=', 'agent_order_returns.id')
            ->where('agent_order_returns.agent_order_dispatch_id', $id)
            ->select('item_type', 'item_id', DB::raw('SUM(quantity) as total_returned'))
            ->groupBy('item_type', 'item_id')
            ->get()
            ->groupBy('item_type');

        return view('admin.agent_orders.returns.create', compact('dispatch', 'items', 'fabricItems', 'returnedQuantities'));
    }

    public function returnStore(Request $request, $id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::findOrFail($id);
        $returns_data = $request->input('returns'); // Array of {item_type, item_id, quantity}

        if (empty($returns_data)) {
            return response()->json(['success' => false, 'message' => 'No items selected for return.'], 422);
        }

        DB::beginTransaction();
        try {
            $total_amount = 0;
            $items_to_save = [];

            foreach ($returns_data as $data) {
                $qty = (float) $data['quantity'];
                if ($qty <= 0)
                    continue;

                if ($data['item_type'] === 'standard') {
                    $item = AgentOrderItem::findOrFail($data['item_id']);
                    $max_qty = $item->scanned_box_qty;
                } else {
                    $item = AgentOrderFabricItem::findOrFail($data['item_id']);
                    $max_qty = $item->meter;
                }

                // Check already returned
                $alreadyReturned = DB::table('agent_order_return_items')
                    ->join('agent_order_returns', 'agent_order_return_items.agent_order_return_id', '=', 'agent_order_returns.id')
                    ->where('agent_order_returns.agent_order_dispatch_id', $id)
                    ->where('item_type', $data['item_type'])
                    ->where('item_id', $data['item_id'])
                    ->sum('quantity');

                if (($qty + $alreadyReturned) > $max_qty) {
                    throw new \Exception("Return quantity exceeds dispatched quantity for one or more items.");
                }

                $price = (float) ($data['price'] ?? $item->selling_price);

                // For standard items, we need to calculate PCS if quantity is boxes
                $row_pcs = $qty;
                if ($data['item_type'] === 'standard') {
                    $pcs_per_box = $item->scanned_quantity / ($item->scanned_box_qty ?: 1);
                    $row_pcs = $qty * $pcs_per_box;
                }

                $subtotal = $row_pcs * $price;
                $total_amount += $subtotal;

                $items_to_save[] = [
                    'item_type' => $data['item_type'],
                    'item_id' => $data['item_id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0, // We will calculate tax at header level or distributed
                    'total' => $subtotal, // Placeholder, will update if needed
                ];

                // Restore Inventory
                if ($data['item_type'] === 'standard') {
                    $inv = DomesticInventory::where('barcode', $item->barcode)->first();
                    if ($inv) {
                        $inv->increment('total_boxes', $qty);
                    } else {
                        DomesticInventory::create([
                            'product_id' => $item->product_id,
                            'color_id' => $item->color_id,
                            'size_set_id' => $item->size_set_id,

                            'quantity' => ($item->scanned_quantity / ($item->scanned_box_qty ?: 1)),
                            'total_boxes' => $qty,
                            'barcode' => $item->barcode,
                            'box_no' => $item->box_no,
                            'carton_no' => $item->carton_no,
                            'status' => 'available'
                        ]);
                    }
                } else {
                    $roll = FabricReceiptDetail::find($item->fabric_receipt_detail_id);
                    if ($roll) {
                        $roll->increment('remaining_quantity', $qty);
                    }
                }
            }

            if (empty($items_to_save)) {
                throw new \Exception("Please enter valid return quantities.");
            }

            // Calculations based on manual inputs
            $gst_percentage = (float) ($request->gst_percentage ?? ($dispatch->gst_percentage ?? 5.00));
            $discount_percentage = (float) ($request->discount_percentage ?? 0);
            $other_charges = (float) ($request->other_charges ?? 0);

            $discount_amount = ($total_amount * $discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
            $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

            $return = AgentOrderReturn::create([
                'agent_order_dispatch_id' => $id,
                'return_date' => $request->return_date ?? date('Y-m-d'),
                'total_amount' => $total_amount,
                'gst_percentage' => $gst_percentage,
                'discount_amount' => $discount_amount,
                'discount_percentage' => $discount_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'remark' => $request->remark,
                'created_by' => Auth::id()
            ]);

            foreach ($items_to_save as $item_data) {
                $item_data['agent_order_return_id'] = $return->id;
                // Distribute tax and discount proportionally if needed, or just save subtotal
                AgentOrderReturnItem::create($item_data);
            }

            // Adjust Party Balance
            $party = $dispatch->party();
            if ($party) {
                // Return increases balance (Credit)
                $party->increment('balance', $grand_total);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sales return processed successfully.', 'redirect_url' => route('admin.agent-orders.returns.show', $return->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function returnShow($id)
    {
        $return = AgentOrderReturn::with(['dispatch.vendor', 'dispatch.shop', 'dispatch.agent', 'items', 'creator'])->findOrFail($id);

        foreach ($return->items as $item) {
            if ($item->item_type === 'standard') {
                $original = AgentOrderItem::find($item->item_id);
                $item->product_name = $original->product_name ?? 'N/A';
                $item->design_number = $original->design_number ?? 'N/A';
                $item->color_name = $original->color_name ?? 'N/A';
                $item->size_set_name = $original->size_set_name ?? 'N/A';
                $item->unit = 'Boxes';
            } else {
                $original = AgentOrderFabricItem::with('fabric')->find($item->item_id);
                $item->product_name = $original->fabric->name ?? 'Fabric';
                $item->design_number = 'N/A';
                $item->color_name = 'N/A';
                $item->size_set_name = 'N/A';
                $item->unit = 'm';
            }
        }

        return view('admin.agent_orders.returns.show', compact('return'));
    }

    public function returnEdit($id)
    {
        $return = AgentOrderReturn::with('items')->findOrFail($id);
        $dispatch = \App\Models\AgentOrderDispatch::with(['vendor', 'shop', 'orders.items', 'orders.fabricItems.fabric', 'orders.fabricItems.roll'])->findOrFail($return->agent_order_dispatch_id);

        $items = $dispatch->orders->flatMap->items->where('agent_order_dispatch_id', $dispatch->id);
        $fabricItems = $dispatch->orders->flatMap->fabricItems->where('agent_order_dispatch_id', $dispatch->id);

        // Pre-calculate already returned quantities for this dispatch (excluding current return)
        $returnedQuantities = [
            'standard' => DB::table('agent_order_return_items')
                ->join('agent_order_returns', 'agent_order_return_items.agent_order_return_id', '=', 'agent_order_returns.id')
                ->where('agent_order_returns.agent_order_dispatch_id', $dispatch->id)
                ->where('agent_order_returns.id', '!=', $id)
                ->where('item_type', 'standard')
                ->select('item_id', DB::raw('SUM(quantity) as total_returned'))
                ->groupBy('item_id')
                ->get(),
            'fabric' => DB::table('agent_order_return_items')
                ->join('agent_order_returns', 'agent_order_return_items.agent_order_return_id', '=', 'agent_order_returns.id')
                ->where('agent_order_returns.agent_order_dispatch_id', $dispatch->id)
                ->where('agent_order_returns.id', '!=', $id)
                ->where('item_type', 'fabric')
                ->select('item_id', DB::raw('SUM(quantity) as total_returned'))
                ->groupBy('item_id')
                ->get(),
        ];

        return view('admin.agent_orders.returns.edit', compact('return', 'dispatch', 'items', 'fabricItems', 'returnedQuantities'));
    }

    public function downloadReturnPdf($id)
    {
        $return = AgentOrderReturn::with(['dispatch.vendor', 'dispatch.shop', 'dispatch.agent', 'items', 'creator'])->findOrFail($id);
        $settings = DB::table('settings')->first();

        foreach ($return->items as $item) {
            if ($item->item_type === 'standard') {
                $original = AgentOrderItem::find($item->item_id);
                $item->product_name = $original->product_name ?? 'N/A';
                $item->design_number = $original->design_number ?? 'N/A';
                $item->color_name = $original->color_name ?? 'N/A';
                $item->size_set_name = $original->size_set_name ?? 'N/A';
                $item->unit = 'Boxes';
            } else {
                $original = AgentOrderFabricItem::with('fabric')->find($item->item_id);
                $item->product_name = $original->fabric->name ?? 'Fabric';
                $item->design_number = 'N/A';
                $item->color_name = 'N/A';
                $item->size_set_name = 'N/A';
                $item->unit = 'm';
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.returns.return-pdf', compact('return', 'settings'));
        return $pdf->download('Sales_Return_SR_' . $return->id . '.pdf');
    }

    public function sendWhatsappReturnPdf(Request $request, $id)
    {
        $return = AgentOrderReturn::with(['dispatch.vendor', 'dispatch.shop', 'dispatch.agent', 'items', 'creator'])->findOrFail($id);
        $settings = DB::table('settings')->first();
        
        $party = $return->dispatch->party_type === 'vendor' ? $return->dispatch->vendor : $return->dispatch->shop;
        $phone = $party->phone ?? '';

        if ($request->has('phone') && !empty($request->phone)) {
            $phone = $request->phone;
        }

        if (empty($phone)) {
            return back()->with('error', 'No phone number available for this party.');
        }

        foreach ($return->items as $item) {
            if ($item->item_type === 'standard') {
                $original = AgentOrderItem::find($item->item_id);
                $item->product_name = $original->product_name ?? 'N/A';
                $item->design_number = $original->design_number ?? 'N/A';
                $item->color_name = $original->color_name ?? 'N/A';
                $item->size_set_name = $original->size_set_name ?? 'N/A';
                $item->unit = 'Boxes';
            } else {
                $original = AgentOrderFabricItem::with('fabric')->find($item->item_id);
                $item->product_name = $original->fabric->name ?? 'Fabric';
                $item->design_number = 'N/A';
                $item->color_name = 'N/A';
                $item->size_set_name = 'N/A';
                $item->unit = 'm';
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agent_orders.returns.return-pdf', compact('return', 'settings'));
        $fileName = 'Sales_Return_SR_' . $return->id . '.pdf';

        $dir = public_path('whatsapp_pdfs');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdf->save($dir . '/' . $fileName);
        $physicalPath = $dir . '/' . $fileName;

        $msg = "Dear {$party->name},\n\nYour Sales Return #SR-{$return->id} has been generated.\nPlease find it attached.\n\nThank you!";
        $status = send_whatsapp_attachment($phone, $msg, $physicalPath, $fileName);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp Sales Return sent to ' . $phone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
    }

    public function returnUpdate(Request $request, $id)
    {
        $return = AgentOrderReturn::with('items')->findOrFail($id);
        $dispatch = \App\Models\AgentOrderDispatch::findOrFail($return->agent_order_dispatch_id);
        $returns_data = $request->input('returns');

        if (empty($returns_data)) {
            return response()->json(['success' => false, 'message' => 'No items selected for return.'], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Reverse the current return's inventory and balance impact
            foreach ($return->items as $item) {
                if ($item->item_type === 'standard') {
                    $original = AgentOrderItem::find($item->item_id);
                    if ($original) {
                        $inv = DomesticInventory::where('barcode', $original->barcode)->first();
                        if ($inv) {
                            $inv->decrement('total_boxes', $item->quantity);
                        }
                    }
                } else {
                    $original = AgentOrderFabricItem::find($item->item_id);
                    if ($original) {
                        $roll = FabricReceiptDetail::find($original->fabric_receipt_detail_id);
                        if ($roll) {
                            $roll->decrement('remaining_quantity', $item->quantity);
                        }
                    }
                }
            }

            $party = $dispatch->party();
            if ($party) {
                // Reverse old return (decreases balance back)
                $party->decrement('balance', $return->grand_total);
            }

            // 2. Delete old return items
            $return->items()->delete();

            // 3. Process new return data
            $total_amount = 0;
            $items_to_save = [];

            foreach ($returns_data as $data) {
                $qty = (float) $data['quantity'];
                if ($qty <= 0)
                    continue;

                if ($data['item_type'] === 'standard') {
                    $item = AgentOrderItem::findOrFail($data['item_id']);
                    $max_qty = $item->scanned_box_qty;
                } else {
                    $item = AgentOrderFabricItem::findOrFail($data['item_id']);
                    $max_qty = $item->meter;
                }

                $alreadyReturned = DB::table('agent_order_return_items')
                    ->join('agent_order_returns', 'agent_order_return_items.agent_order_return_id', '=', 'agent_order_returns.id')
                    ->where('agent_order_returns.agent_order_dispatch_id', $dispatch->id)
                    ->where('agent_order_returns.id', '!=', $id)
                    ->where('item_type', $data['item_type'])
                    ->where('item_id', $data['item_id'])
                    ->sum('quantity');

                if (($qty + $alreadyReturned) > $max_qty) {
                    throw new \Exception("Return quantity exceeds dispatched quantity for one or more items.");
                }

                $price = (float) ($data['price'] ?? $item->selling_price);
                $row_pcs = $qty;
                if ($data['item_type'] === 'standard') {
                    $pcs_per_box = $item->scanned_quantity / ($item->scanned_box_qty ?: 1);
                    $row_pcs = $qty * $pcs_per_box;
                }

                $subtotal = $row_pcs * $price;
                $total_amount += $subtotal;

                $items_to_save[] = [
                    'agent_order_return_id' => $return->id,
                    'item_type' => $data['item_type'],
                    'item_id' => $data['item_id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'total' => $subtotal,
                ];

                // Restore Inventory
                if ($data['item_type'] === 'standard') {
                    $inv = DomesticInventory::where('barcode', $item->barcode)->first();
                    if ($inv) {
                        $inv->increment('total_boxes', $qty);
                    } else {
                        DomesticInventory::create([
                            'product_id' => $item->product_id,
                            'color_id' => $item->color_id,
                            'size_set_id' => $item->size_set_id,

                            'quantity' => ($item->scanned_quantity / ($item->scanned_box_qty ?: 1)),
                            'total_boxes' => $qty,
                            'barcode' => $item->barcode,
                            'box_no' => $item->box_no,
                            'carton_no' => $item->carton_no,
                            'status' => 'available'
                        ]);
                    }
                } else {
                    $roll = FabricReceiptDetail::find($item->fabric_receipt_detail_id);
                    if ($roll) {
                        $roll->increment('remaining_quantity', $qty);
                    }
                }
            }

            // 4. Update return header
            $gst_percentage = (float) ($request->gst_percentage ?? 5.00);
            $discount_percentage = (float) ($request->discount_percentage ?? 0);
            $other_charges = (float) ($request->other_charges ?? 0);

            $discount_amount = ($total_amount * $discount_percentage / 100);
            $taxable_amount = $total_amount - $discount_amount;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
            $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

            $return->update([
                'return_date' => $request->return_date ?? date('Y-m-d'),
                'total_amount' => $total_amount,
                'gst_percentage' => $gst_percentage,
                'discount_amount' => $discount_amount,
                'discount_percentage' => $discount_percentage,
                'gst_amount' => $gst_amount,
                'other_charges' => $other_charges,
                'grand_total' => $grand_total,
                'remark' => $request->remark,
            ]);

            foreach ($items_to_save as $item_data) {
                AgentOrderReturnItem::create($item_data);
            }

            // 5. Re-apply new Party Balance
            if ($party) {
                // Apply new return (increases balance)
                $party->increment('balance', $grand_total);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sales Return updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error updating return: ' . $e->getMessage()], 500);
        }
    }

    public function returnDestroy($id)
    {
        DB::beginTransaction();
        try {
            $return = AgentOrderReturn::with('items')->findOrFail($id);
            $dispatch = \App\Models\AgentOrderDispatch::findOrFail($return->agent_order_dispatch_id);

            foreach ($return->items as $item) {
                // Reverse Inventory
                if ($item->item_type === 'standard') {
                    $original = AgentOrderItem::find($item->item_id);
                    if ($original) {
                        $inv = DomesticInventory::where('barcode', $original->barcode)->first();
                        if ($inv) {
                            $inv->decrement('total_boxes', $item->quantity);
                        }
                    }
                } else {
                    $original = AgentOrderFabricItem::find($item->item_id);
                    if ($original) {
                        $roll = FabricReceiptDetail::find($original->fabric_receipt_detail_id);
                        if ($roll) {
                            $roll->decrement('remaining_quantity', $item->quantity);
                        }
                    }
                }
            }

            // Reverse Party Balance
            $party = $dispatch->party();
            if ($party) {
                // Reverse return (decreases balance back)
                $party->decrement('balance', $return->grand_total);
            }

            $return->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Sales Return deleted and inventory/balance reversed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error deleting return: ' . $e->getMessage()], 500);
        }
    }

    public function destroyDispatch($id)
    {
        $dispatch = \App\Models\AgentOrderDispatch::with(['orders'])->findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Restore Item Inventory
            $items = DB::table('agent_order_items')->where('agent_order_dispatch_id', $id)->get();
            foreach ($items as $item) {
                // Find or create inventory record to restore
                $inventory = \App\Models\DomesticInventory::where('barcode', $item->barcode)->first();
                if ($inventory) {
                    $inventory->increment('total_boxes', $item->box_qty);
                } else {
                    // If deleted, recreate it
                    \App\Models\DomesticInventory::create([
                        'product_id' => $item->product_id,
                        'color_id' => $item->color_id,
                        'size_set_id' => $item->size_set_id,
                        'total_boxes' => $item->box_qty,
                        'quantity' => ($item->box_qty > 0) ? ($item->quantity / $item->box_qty) : 0,
                        'box_no' => $item->box_no,
                        'carton_no' => $item->carton_no,
                        'barcode' => $item->barcode,
                        'status' => 1
                    ]);
                }

                // Reset item to scanned but not dispatched
                DB::table('agent_order_items')->where('id', $item->id)->update([
                    'dispatched_at' => null,
                    'agent_order_dispatch_id' => null
                ]);
            }

            // 2. Restore Fabric Inventory
            $fabricItems = DB::table('agent_order_fabric_items')->where('agent_order_dispatch_id', $id)->get();
            foreach ($fabricItems as $fItem) {
                $roll = \App\Models\FabricReceiptDetail::find($fItem->fabric_receipt_detail_id);
                if ($roll) {
                    // DO NOT INCREMENT HERE! Pending orders should still reserve their stock.
                    // $roll->increment('remaining_quantity', $fItem->meter);
                }

                DB::table('agent_order_fabric_items')->where('id', $fItem->id)->update([
                    'status' => 'pending',
                    'dispatched_at' => null,
                    'agent_order_dispatch_id' => null
                ]);
            }

            // 3. Reverse Party Balance (Decrease model)
            if ($dispatch->party_type === 'vendor') {
                $party = \App\Models\Vendor::find($dispatch->master_vendor_id);
            } else {
                $party = \App\Models\MasterCustomer::find($dispatch->master_customer_id);
            }

            if ($party) {
                $party->increment('balance', $dispatch->grand_total);
            }

            // 4. Update Order Statuses
            foreach ($dispatch->orders as $order) {
                $totalItems = DB::table('agent_order_items')->where('agent_order_id', $order->id)->count();
                $dispatchedItems = DB::table('agent_order_items')->where('agent_order_id', $order->id)->whereNotNull('dispatched_at')->count();

                $totalFabric = DB::table('agent_order_fabric_items')->where('agent_order_id', $order->id)->count();
                $dispatchedFabric = DB::table('agent_order_fabric_items')->where('agent_order_id', $order->id)->whereNotNull('dispatched_at')->count();

                if ($dispatchedItems == 0 && $dispatchedFabric == 0) {
                    $order->status = 'pending';
                } else if (($dispatchedItems + $dispatchedFabric) < ($totalItems + $totalFabric)) {
                    $order->status = 'partially_dispatched';
                }
                $order->save();
            }

            // 5. Delete Dispatch and Dispatch Items
            DB::table('agent_order_dispatch_items')->where('agent_order_dispatch_id', $id)->delete();
            $dispatch->delete();

            DB::commit();
            return redirect()->route('admin.agent-orders.dispatches.index')->with('success', 'Dispatch deleted and inventory/balance reversed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting dispatch: ' . $e->getMessage());
        }
    }
}
