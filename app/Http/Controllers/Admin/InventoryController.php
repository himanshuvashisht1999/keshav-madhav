<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use App\Models\ProductionGoods;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterColor;
use App\Models\MasterProductFitting;
use App\Models\MasterDesignPattern;
use App\Models\MasterSeries;
use App\Models\ProductionGoodVariant;
use App\Models\ProductionGoodVariantItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    public function index()
    {
        // Fetch unique size sets and products for filters
        $size_sets = DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->select('domestic_inventories.size_set_id', 'master_size_measurements.name as size_set_name')
            ->distinct()->get();

        $products = DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->select('domestic_inventories.product_id', 'production_goods.name_of_garment as product_name')
            ->distinct()->get();

        $colors = DomesticInventory::join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->select('domestic_inventories.color_id', 'master_colors.name as color_name')
            ->distinct()->get();

        $master_products = ProductionGoods::with('series')->get();
        $master_sizes = MasterSizeMeasurement::all();
        $master_colors = MasterColor::all();
        $master_fittings = MasterProductFitting::all();
        $master_patterns = MasterDesignPattern::all();
        $master_series = MasterSeries::where('status', 1)->get();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();
        $brands = \App\Models\Brand::all();
        $natures = \App\Models\ProductNature::all();
        $fabric_types = \App\Models\FabricType::all();

        return view('admin.inventory.index', compact(
            'size_sets',
            'products',
            'colors',
            'master_products',
            'master_sizes',
            'master_colors',
            'master_fittings',
            'master_patterns',
            'master_series',
            'storerooms',
            'brands',
            'natures',
            'fabric_types'
        ));
    }

    public function indexList(Request $request)
    {
        // Group by Product Name, Design Number, Size Set, MRP, Selling Price
        // Build order totals dynamically to inject color filter if present
        $colorFilter = '';
        if ($request->has('color_id') && !empty($request->color_id)) {
            $color_id = (int) $request->color_id;
            $colorFilter = " AND aoi.color_id = {$color_id}";
        }

        $query = DomesticInventory::select(
            'domestic_inventories.size_set_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment as product_name',
            'series.name as series_name',
            'sizes.name as size_set_name',
            'fittings.name as fitting_name',
            'patterns.name as pattern_name',
            'variants.mrp as mrp',
            'variants.image as product_image',
            'variants.id as variant_id',
            DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
            DB::raw('COALESCE(MAX(order_totals.total_qty), 0) as total_order')
        )
            ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
            ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
            ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
            ->leftJoin('master_product_fittings as fittings', 'products.master_product_fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'products.master_pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            })
            ->leftJoin(DB::raw("(
                SELECT aoi.product_id, 
                       aoi.size_set_id, 
                       SUM(aoi.box_qty) as total_qty
                FROM agent_order_items aoi
                JOIN agent_orders ao ON aoi.agent_order_id = ao.id
                WHERE ao.status != 'dispatched' $colorFilter
                GROUP BY aoi.product_id, aoi.size_set_id
            ) as order_totals"), function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'order_totals.product_id')
                     ->on('domestic_inventories.size_set_id', '=', 'order_totals.size_set_id');
            });

        if ($request->has('min_total_boxes') && $request->min_total_boxes !== null) {
            $query->having('total_boxes', '>=', $request->min_total_boxes);
        }

        if ($request->has('min_total_order') && $request->min_total_order !== null) {
            $query->having('total_order', '>=', $request->min_total_order);
        }

        if ($request->has('stock_status') && !empty($request->stock_status)) {
            if ($request->stock_status === 'shortage') {
                $query->havingRaw('total_boxes < total_order');
            } elseif ($request->stock_status === 'in_stock') {
                $query->havingRaw('total_boxes >= total_order');
            }
        }

        // Filter by Size Set
        if ($request->has('size_set_id') && !empty($request->size_set_id)) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }

        // Filter by Product
        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }

        // Filter by Color
        if ($request->has('color_id') && !empty($request->color_id)) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }

        // Filter by Design Number
        if ($request->has('design_number') && !empty($request->design_number)) {
            $query->where('products.design_number', 'LIKE', '%' . $request->design_number . '%');
        }

        // Filter by MRP
        if ($request->has('mrp') && !empty($request->mrp)) {
            $query->where('variants.mrp', '>=', $request->mrp);
        }

        // Filter by Brand
        if ($request->has('brand_id') && !empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        // Filter by Series
        if ($request->has('series_id') && !empty($request->series_id)) {
            $query->where('products.master_series_id', $request->series_id);
        }

        // Filter by Fitting
        if ($request->has('fitting_id') && !empty($request->fitting_id)) {
            $query->where('products.master_product_fitting_id', $request->fitting_id);
        }

        // Filter by Pattern
        if ($request->has('pattern_id') && !empty($request->pattern_id)) {
            $query->where('products.master_pattern_id', $request->pattern_id);
        }

        // Filter by Product Nature
        if ($request->has('nature_id') && !empty($request->nature_id)) {
            $query->where('products.product_nature_id', $request->nature_id);
        }

        // Filter by Fabric Type
        if ($request->has('fabric_type_id') && !empty($request->fabric_type_id)) {
            $query->where('products.fabric_type_id', $request->fabric_type_id);
        }

        $query->groupBy(
            'domestic_inventories.size_set_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment',
            'series.name',
            'sizes.name',
            'fittings.name',
            'patterns.name',
            'variants.mrp',
            'variants.image',
            'variants.id'
        )->orderBy('products.design_number', 'asc');

        if ($request->has('load_more')) {
            $perPage = 20;
            
            // Calculate grand totals BEFORE pagination mutates the query
            $grand_totals = null;
            $page = $request->get('page', 1);
            if ($page == 1) {
                $totalsQuery = clone $query;
                $sql = $totalsQuery->toSql();
                $totals = DB::table(DB::raw("($sql) as sub"))
                    ->mergeBindings($totalsQuery->getQuery())
                    ->selectRaw('SUM(total_boxes) as sum_total_boxes, SUM(total_order) as sum_total_order')
                    ->first();
                    
                $grand_totals = [
                    'boxes' => (int)($totals->sum_total_boxes ?? 0),
                    'orders' => (int)($totals->sum_total_order ?? 0)
                ];
            }

            $results = $query->paginate($perPage);

            $html = '';
            $start = ($results->currentPage() - 1) * $perPage + 1;
            foreach ($results as $index => $row) {
                $html .= view('admin.inventory.partials.row', [
                    'row' => $row,
                    'index' => $start + $index
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null,
                'grand_totals' => $grand_totals
            ]);
        }

        // Fallback for DataTables if still used by other parts
        $data = $query->get();
        return Datatables::of($data)->addIndexColumn()->make(true);
    }

    public function getVariantImages($id)
    {
        $variant = \App\Models\ProductionGoodVariant::with('items.color')->find($id);
        if (!$variant) {
            return response()->json(['success' => false]);
        }

        $images = [];
        // Add size set image as the first image
        if ($variant->image) {
            $images[] = [
                'type' => 'size_set',
                'url' => asset('assets/products/' . $variant->image),
                'color' => 'Size Set'
            ];
        }

        // Add color images
        foreach ($variant->items as $item) {
            if ($item->image) {
                $images[] = [
                    'type' => 'color',
                    'url' => asset('assets/products/' . $item->image),
                    'color' => $item->color ? $item->color->name : 'Color'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'images' => $images
        ]);
    }

    public function export(Request $request)
    {
        // Group by Product Name, Design Number, Size Set, MRP, Selling Price
        $query = DomesticInventory::select(
            'domestic_inventories.size_set_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment as product_name',
            'series.name as series_name',
            'sizes.name as size_set_name',
            'fittings.name as fitting_name',
            'patterns.name as pattern_name',
            'variants.mrp as mrp',
            DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
            DB::raw('COALESCE(MAX(order_totals.total_qty), 0) as total_order')
        )
            ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
            ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
            ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
            ->leftJoin('master_product_fittings as fittings', 'products.master_product_fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'products.master_pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            })
            ->leftJoin(DB::raw('(
                SELECT aoi.design_number COLLATE utf8mb4_unicode_ci as design_number, 
                       aoi.size_set_id, 
                       SUM(aoi.box_qty) as total_qty
                FROM agent_order_items aoi
                JOIN agent_orders ao ON aoi.agent_order_id = ao.id
                WHERE ao.status != "dispatched"
                GROUP BY aoi.design_number, aoi.size_set_id
            ) as order_totals'), function ($join) {
                $join->on('products.design_number', '=', 'order_totals.design_number')
                     ->on('domestic_inventories.size_set_id', '=', 'order_totals.size_set_id');
            });

        if ($request->has('min_total_boxes') && $request->min_total_boxes !== null) {
            $query->having('total_boxes', '>=', $request->min_total_boxes);
        }

        if ($request->has('min_total_order') && $request->min_total_order !== null) {
            $query->having('total_order', '>=', $request->min_total_order);
        }

        if ($request->has('stock_status') && !empty($request->stock_status)) {
            if ($request->stock_status === 'shortage') {
                $query->havingRaw('total_boxes < total_order');
            } elseif ($request->stock_status === 'in_stock') {
                $query->havingRaw('total_boxes >= total_order');
            }
        }

        // Filter by Size Set
        if ($request->has('size_set_id') && !empty($request->size_set_id)) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }

        // Filter by Product
        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }

        // Filter by Color
        if ($request->has('color_id') && !empty($request->color_id)) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }

        // Filter by Design Number
        if ($request->has('design_number') && !empty($request->design_number)) {
            $query->where('products.design_number', 'LIKE', '%' . $request->design_number . '%');
        }

        // Filter by MRP
        if ($request->has('mrp') && !empty($request->mrp)) {
            $query->where('variants.mrp', '>=', $request->mrp);
        }

        // Filter by Brand
        if ($request->has('brand_id') && !empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        // Filter by Series
        if ($request->has('series_id') && !empty($request->series_id)) {
            $query->where('products.master_series_id', $request->series_id);
        }

        // Filter by Fitting
        if ($request->has('fitting_id') && !empty($request->fitting_id)) {
            $query->where('products.master_product_fitting_id', $request->fitting_id);
        }

        // Filter by Pattern
        if ($request->has('pattern_id') && !empty($request->pattern_id)) {
            $query->where('products.master_pattern_id', $request->pattern_id);
        }

        // Filter by Product Nature
        if ($request->has('nature_id') && !empty($request->nature_id)) {
            $query->where('products.product_nature_id', $request->nature_id);
        }

        // Filter by Fabric Type
        if ($request->has('fabric_type_id') && !empty($request->fabric_type_id)) {
            $query->where('products.fabric_type_id', $request->fabric_type_id);
        }

        $query->groupBy(
            'domestic_inventories.size_set_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment',
            'series.name',
            'sizes.name',
            'fittings.name',
            'patterns.name',
            'variants.mrp'
        )->orderBy('products.design_number', 'asc');

        $data = $query->get();

        if ($request->type === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.inventory.export_pdf', compact('data'))
                      ->setPaper('A4', 'landscape');
            return $pdf->download('inventory-summary-' . now()->format('Y-m-d_H-i') . '.pdf');
        }

        return response()
            ->view('admin.inventory.export_excel', [
                'data' => $data,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="inventory-summary-' . now()->format('Y-m-d_H-i') . '.xls"');
    }

    public function show(Request $request)
    {
        $query = DomesticInventory::query()
            ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
            ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
            ->leftJoin('master_colors as colors', 'domestic_inventories.color_id', '=', 'colors.id')
            ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
            ->leftJoin('master_product_fittings as fittings', 'products.master_product_fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'products.master_pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            })
            ->leftJoin(DB::raw('(
                SELECT aoi.design_number COLLATE utf8mb4_unicode_ci as design_number, 
                       aoi.size_set_id, 
                       aoi.color_id,
                       aoi.rack_id,
                       SUM(aoi.box_qty) as color_total_order
                FROM agent_order_items aoi
                JOIN agent_orders ao ON aoi.agent_order_id = ao.id
                WHERE ao.status != "dispatched"
                GROUP BY aoi.design_number, aoi.size_set_id, aoi.color_id, aoi.rack_id
            ) as color_order_totals'), function ($join) {
                $join->on('products.design_number', '=', 'color_order_totals.design_number')
                     ->on('domestic_inventories.size_set_id', '=', 'color_order_totals.size_set_id')
                     ->on('domestic_inventories.color_id', '=', 'color_order_totals.color_id')
                     ->on('domestic_inventories.rack_id', '=', 'color_order_totals.rack_id');
            })
            ->select(
                'domestic_inventories.*',
                'products.design_number',
                'products.name_of_garment as product_name',
                'series.name as series_name',
                'colors.name as color_name',
                'sizes.name as size_set_name',
                'fittings.name as fitting_name',
                'patterns.name as pattern_name',
                'variants.mrp as mrp',
                'variants.image as product_image',
                'variants.id as variant_id',
                DB::raw('COALESCE(color_order_totals.color_total_order, 0) as color_total_order')
            );

        if ($request->has('product_id'))
            $query->where('domestic_inventories.product_id', $request->product_id);
        if ($request->has('size_set_id'))
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        if ($request->has('color_id'))
            $query->where('domestic_inventories.color_id', $request->color_id);


        $items = $query->with('rack.storeroom')->get();
        $group_info = $items->first();

        if (!$group_info) {
            return redirect()->route('admin.inventory.index')->with('error', 'Inventory group info not found.');
        }

        $total_order = 0;
        if ($group_info->design_number) {
            $total_order = DB::table('agent_order_items')
                ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
                ->where('agent_orders.status', '!=', 'dispatched')
                ->where('agent_order_items.design_number', $group_info->design_number)
                ->where('agent_order_items.size_set_id', $request->size_set_id)
                ->sum('agent_order_items.box_qty') ?? 0;
        }

        return view('admin.inventory.show', compact('items', 'group_info', 'total_order'));
    }
    public function create()
    {
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();

        $vendors = \App\Models\Vendor::where('status', '1')->get();
        $customers = \App\Models\MasterCustomer::where('status', '1')->get();

        return view('admin.inventory.create', compact('products', 'colors', 'fittings', 'patterns', 'size_sets', 'storerooms', 'vendors', 'customers'));
    }

    public function getMasterData()
    {
        $products = \App\Models\ProductionGoods::with('series')->get();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();

        return response()->json([
            'products' => $products,
            'storerooms' => $storerooms
        ]);
    }

    public function purchase()
    {
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();

        $vendors = \App\Models\Vendor::where('status', '1')->get();
        $customers = \App\Models\MasterCustomer::where('status', '1')->get();
        $productionPOs = \App\Models\ProductionPO::where('status', 1)->orderBy('id', 'desc')->get();

        return view('admin.inventory.purchase', compact('products', 'colors', 'fittings', 'patterns', 'size_sets', 'storerooms', 'vendors', 'customers', 'productionPOs'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'source_type' => 'required|in:production,sample,vendor,customer,consume',
            'vendor_id' => 'required_if:source_type,vendor',
            'customer_id' => 'required_if:source_type,customer',
            'purchase_date' => 'nullable|date',
            'products.*.product_id' => 'required',
            'products.*.color_id' => 'required',
            'products.*.size_set_id' => 'required',

            'products.*.total_boxes' => 'required|integer|min:1',
            'products.*.pieces_per_box' => 'required|integer|min:1',
            'products.*.mrp' => 'required|numeric|min:0',
            'products.*.purchase_rate' => 'nullable|numeric|min:0',
            'products.*.rack_id' => 'nullable|exists:racks,id',
            'sub_total' => 'nullable|numeric|min:0',
            'gst_type' => 'nullable|string',
            'gst_value' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'other_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $inventoryIds = [];
            $printData = [];

            // Initial counters for carton and box numbering
            $currentCartonNo = (int) DomesticInventory::max('carton_no') ?? 0;

            $lastBox = DomesticInventory::where('box_no', 'like', 'BX-%')
                ->orderByRaw('CAST(SUBSTRING(box_no, 4) AS UNSIGNED) DESC')
                ->first();
            $currentBoxNoInt = $lastBox ? (int) str_replace('BX-', '', $lastBox->box_no) : 0;

            // Create a single PackingMain for this entry session
            $packingMain = \App\Models\PackingMain::create([
                'order_main_id' => 0,
                'slip_id' => 0,
                'packing_date' => now(),
                'status' => 1 // Finalized
            ]);

            $source_type = $request->source_type ?? 'production';
            $vendor_id = $request->vendor_id ?? null;
            $customer_id = $request->customer_id ?? null;

            // Create Purchase Summary if Vendor/Customer
            $purchase = null;
            if ($source_type !== 'production' && $source_type !== 'sample' && $source_type !== 'consume') {
                $purchase = \App\Models\DomesticInventoryPurchase::create([
                    'vendor_id' => $vendor_id,
                    'customer_id' => $customer_id,
                    'production_po_id' => $request->production_po_id,
                    'user_id' => auth()->id(),
                    'purchase_date' => $request->purchase_date ?? now()->toDateString(),
                    'sub_total' => $request->sub_total ?? 0,
                    'gst_type' => $request->gst_type ?? 'percentage',
                    'gst_value' => $request->gst_value ?? 0,
                    'gst' => $request->gst ?? 0,
                    'other_amount' => $request->other_amount ?? 0,
                    'discount' => $request->discount ?? 0,
                    'total_amount' => $request->total_amount ?? 0,
                    'remarks' => $request->remarks ?? null,
                ]);
            }

            // Pre-calculate and deduct consumed sources
            $consumedSources = [];
            foreach ($request->products as $item) {
                if (isset($item['consume_source_id']) && !empty($item['consume_source_id'])) {
                    $sourceId = $item['consume_source_id'];
                    if (!isset($consumedSources[$sourceId])) {
                        $consumedSources[$sourceId] = [
                            'model' => DomesticInventory::find($sourceId),
                            'total_pieces_consumed' => 0
                        ];
                    }
                    $consumedSources[$sourceId]['total_pieces_consumed'] += ($item['total_boxes'] * $item['pieces_per_box']);
                }
            }

            // Deduct source boxes
            foreach ($consumedSources as $sourceId => $data) {
                $source = $data['model'];
                if ($source) {
                    $sourceQuantity = $source->quantity > 0 ? $source->quantity : 1;
                    $boxesToDeduct = ceil($data['total_pieces_consumed'] / $sourceQuantity);
                    
                    if ($source->total_boxes < $boxesToDeduct) {
                        throw new \Exception("Insufficient stock in source for Design: " . $source->barcode . " (Needed: $boxesToDeduct, Available: {$source->total_boxes})");
                    }
                    
                    $source->total_boxes -= $boxesToDeduct;
                    if ($source->total_boxes <= 0) {
                        $source->delete();
                    } else {
                        $source->save();
                    }
                }
            }

            foreach ($request->products as $item) {
                // Handle Consumption Logic
                if (isset($item['consume_source_id']) && !empty($item['consume_source_id'])) {
                    $source = $consumedSources[$item['consume_source_id']]['model'] ?? null;
                    if ($source) {
                        // Add history for consumption
                        \App\Models\DomesticInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'old_product_id' => $source->product_id,
                            'old_size_set_id' => $source->size_set_id,
                            'old_color_id' => $source->color_id,
                            'old_rack_id' => $source->rack_id,
                            'new_product_id' => $item['product_id'],
                            'new_size_set_id' => $item['size_set_id'],
                            'new_color_id' => $item['color_id'],
                            'new_rack_id' => $item['rack_id'] ?? null,
                            'box_quantity' => $item['total_boxes'],
                            'type' => 'stock_consume'
                        ]);
                    }
                }

                // Consistent Barcode Format: D{id}S{id}C{id}
                $barcode = 'D' . $item['product_id'] . 'S' . $item['size_set_id'] . 'C' . $item['color_id'];

                $inventory = DomesticInventory::where('barcode', $barcode)
                    ->where('rack_id', $item['rack_id'] ?? null)
                    ->where('order_main_id', 0) // Unassigned stock
                    ->first();

                if ($inventory) {
                    $inventory->total_boxes += $item['total_boxes'];
                    $inventory->save();
                } else {
                    $inventory = DomesticInventory::create([
                        'product_id' => $item['product_id'],
                        'color_id' => $item['color_id'],
                        'size_set_id' => $item['size_set_id'],

                        'quantity' => $item['pieces_per_box'],
                        'total_boxes' => $item['total_boxes'],
                        'barcode' => $barcode,
                        'qrcode' => $barcode,
                        'packing_main_id' => $packingMain->id,
                        'rack_id' => $item['rack_id'] ?? null,
                        'order_main_id' => 0,
                        'status' => 1,
                    ]);
                }

                if ($source_type !== 'consume') {
                    // Log History for stock addition
                    \App\Models\DomesticInventoryHistory::create([
                        'user_id' => auth()->id(),
                        'purchase_id' => $purchase ? $purchase->id : null,
                        'vendor_id' => ($source_type == 'vendor') ? $vendor_id : null,
                        'customer_id' => ($source_type == 'customer') ? $customer_id : null,
                        'new_product_id' => $item['product_id'],
                        'new_size_set_id' => $item['size_set_id'],
                        'new_color_id' => $item['color_id'],

                        'new_rack_id' => $item['rack_id'] ?? null,
                        'box_quantity' => $item['total_boxes'],
                        'pieces_per_box' => $item['pieces_per_box'],
                        'mrp' => $item['mrp'] ?? 0,
                        'purchase_rate' => $item['purchase_rate'] ?? 0,
                        'type' => ($source_type == 'sample' ? 'sample' : 'creation')
                    ]);
                }

                $inventoryIds[] = $inventory->id;
                $printData[] = [
                    'id' => $inventory->id,
                    'qty' => $item['total_boxes']
                ];

                // Support legacy PackingCarton/PackingBox for secondary tracking if needed
                for ($i = 0; $i < $item['total_boxes']; $i++) {
                    $currentBoxNoInt++;
                    $box_no = 'BX-' . $currentBoxNoInt;

                    $carton = \App\Models\PackingCarton::create([
                        'packing_main_id' => $packingMain->id,
                        'carton_no' => $currentCartonNo,
                        'rack_id' => $item['rack_id'] ?? null,
                        'status' => 1
                    ]);

                    \App\Models\PackingBox::create([
                        'packing_main_id' => $packingMain->id,
                        'packing_carton_id' => $carton->id,
                        'box_no' => $box_no,
                        'box_type' => 'manual',
                        'barcode' => $barcode,
                        'domestic_inventory_id' => $inventory->id
                    ]);

                    $currentCartonNo++;
                }
            }

            // Update Party Balance
            if ($purchase) {
                if ($purchase->vendor_id) {
                    \App\Models\Vendor::find($purchase->vendor_id)->increment('balance', $purchase->total_amount);
                } elseif ($purchase->customer_id) {
                    \App\Models\MasterCustomer::find($purchase->customer_id)->increment('balance', $purchase->total_amount);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock added successfully. Generating barcodes...',
                    'ids' => $inventoryIds,
                    'print_data' => $printData
                ]);
            }
            return redirect()->route('admin.inventory.index')->with('success', 'Stock added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding stock: ' . $e->getMessage())->withInput();
        }
    }

    public function getSizeSetInfo($id)
    {
        $size_set = \App\Models\MasterSizeMeasurement::find($id);
        return response()->json([
            'no_of_pcs' => $size_set ? $size_set->no_of_pcs : 0
        ]);
    }

    public function getPricingInfo(Request $request)
    {
        $pricing = \App\Models\ProductionGoodVariant::where('production_goods_id', $request->product_id)
            ->where('master_size_measurement_id', $request->size_set_id)
            ->first();

        if ($pricing) {
            $product = \App\Models\ProductionGoods::with('series')->find($request->product_id);
            $autoName = trim(($product && $product->series ? $product->series->name : '') . ' ' . ($product ? $product->name_of_garment : ''));

            return response()->json([
                'success' => true,
                'mrp' => $pricing->mrp,
                'name' => $autoName
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function getLocations(Request $request)
    {
        $locations = DomesticInventory::where('product_id', $request->product_id)
            ->where('size_set_id', $request->size_set_id)
            ->where('color_id', $request->color_id)

            // ->where(function ($q) {
            //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            // })
            ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->select(
                'domestic_inventories.rack_id',
                'racks.name as rack_name',
                'storerooms.name as storeroom_name',
                DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes')
            )
            ->groupBy('domestic_inventories.rack_id', 'racks.name', 'storerooms.name')
            ->get();

        return response()->json($locations);
    }

    public function updateAttributes(Request $request)
    {
        $request->validate([
            'old_product_id' => 'required',
            'old_size_set_id' => 'required',
            'old_color_id' => 'required',
            'old_rack_id' => 'required',
            'new_product_id' => 'required',
            'new_size_set_id' => 'required',
            'new_color_id' => 'required',
            'new_rack_id' => 'required|exists:racks,id',
            'change_quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $query = DomesticInventory::where('product_id', $request->old_product_id)
                ->where('size_set_id', $request->old_size_set_id)
                ->where('color_id', $request->old_color_id)
                ->where('rack_id', $request->old_rack_id);



            // Exclude items already assigned to active orders via order_main_id (NULL or 0 means unassigned)
            // $query->where(function ($q) {
            //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            // });

            // Check if any loose inventory exists for this combination
            $inventoryItems = $query->get();
            if ($inventoryItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available inventory found matching these attributes.'
                ]);
            }

            $total_available = $inventoryItems->sum('total_boxes');
            if ($total_available < $request->change_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ' . $total_available . ' boxes available to update.'
                ]);
            }

            $to_change = (int) $request->change_quantity;


            $new_rack_id = $request->new_rack_id;

            // Consistent Barcode Format: D{id}S{id}C{id}
            $new_barcode = 'D' . $request->new_product_id . 'S' . $request->new_size_set_id . 'C' . $request->new_color_id;            // Perform Update with Splitting Logic
            foreach ($inventoryItems as $item) {
                if ($to_change <= 0)
                    break;

                $take = min($item->total_boxes, $to_change);
                $old_barcode = $item->barcode;

                // Optimization: If nothing is changing (attributes AND rack are same), 
                // and we are taking the WHOLE row, just update this row.
                $isSameAttributes = (
                    $item->product_id == $request->new_product_id &&
                    $item->size_set_id == $request->new_size_set_id &&
                    $item->color_id == $request->new_color_id &&
                    $item->rack_id == $new_rack_id
                );

                if ($isSameAttributes && $take == $item->total_boxes) {
                    $item->barcode = $new_barcode;
                    $item->qrcode = $new_barcode;
                    $item->save();
                    $to_change -= $take;
                    continue;
                }

                // 1. Update/Create NEW DomesticInventory row
                $new_item = DomesticInventory::where('product_id', $request->new_product_id)
                    ->where('size_set_id', $request->new_size_set_id)
                    ->where('color_id', $request->new_color_id)
                    ->where('rack_id', $new_rack_id)
                    ->where('id', '!=', $item->id) // CRITICAL: Don't find yourself!
                    // ->where(function ($q) {
                    //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
                    // })
                    ->first();

                if ($new_item) {
                    $new_item->total_boxes += $take;
                    $new_item->save();
                } else {
                    // Replication
                    $new_item = $item->replicate();
                    $new_item->product_id = $request->new_product_id;
                    $new_item->size_set_id = $request->new_size_set_id;
                    $new_item->color_id = $request->new_color_id;

                    $new_item->rack_id = $new_rack_id;
                    $new_item->total_boxes = $take;
                    $new_item->barcode = $new_barcode;
                    $new_item->qrcode = $new_barcode;
                    $new_item->save();
                }

                // 2. Decrement or Delete OLD DomesticInventory row
                $item->total_boxes -= $take;
                if ($item->total_boxes <= 0) {
                    $item->delete();
                } else {
                    $item->save();
                }
                // 3. Update the actual individual PackingBox records
                $assignedBoxNos = DB::table('agent_order_items')
                    ->whereNotNull('box_no')
                    ->pluck('box_no');

                $boxesToUpdateIds = DB::table('packing_boxes')
                    ->where('barcode', $old_barcode)
                    ->whereNotIn('box_no', $assignedBoxNos)
                    ->limit($take)
                    ->pluck('id');

                if ($boxesToUpdateIds->isNotEmpty()) {
                    DB::table('packing_boxes')
                        ->whereIn('id', $boxesToUpdateIds)
                        ->update(['barcode' => $new_barcode]);
                }
                $to_change -= $take;
            }

            // Log History
            \App\Models\DomesticInventoryHistory::create([
                'user_id' => auth()->id(),
                'old_product_id' => $request->old_product_id,
                'old_size_set_id' => $request->old_size_set_id,
                'old_color_id' => $request->old_color_id,

                'old_rack_id' => $request->old_rack_id ?: null,
                'new_product_id' => $request->new_product_id,
                'new_size_set_id' => $request->new_size_set_id,
                'new_color_id' => $request->new_color_id,

                'new_rack_id' => $new_rack_id,
                'box_quantity' => $request->change_quantity,
                'type' => 'attribute_change'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated ' . $request->change_quantity . ' boxes.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating attributes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProductsBySeries(Request $request)
    {
        $products = ProductionGoods::where('master_series_id', $request->series_id)
            ->where('status', 1)
            ->get(['id', 'name_of_garment']);

        return response()->json($products);
    }

    public function getProductFullDetails(Request $request)
    {
        $product = ProductionGoods::with(['variants.items', 'fitting', 'pattern'])->find($request->product_id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $variants = [];
        foreach ($product->variants as $variant) {
            $colors = [];
            foreach ($variant->items as $item) {
                $colors[] = [
                    'id' => $item->master_color_id,
                    'name' => $item->color->name ?? 'Unknown'
                ];
            }

            $variants[] = [
                'size_set_id' => $variant->master_size_measurement_id,
                'size_set_name' => $variant->sizeSet->name ?? 'Unknown',
                'size_group' => $variant->sizeSet->size_group ?? '',
                'mrp' => $variant->mrp,
                'colors' => $colors
            ];
        }

        return response()->json([
            'success' => true,
            'pattern_id' => $product->master_pattern_id,
            'pattern_name' => $product->pattern->name ?? '',
            'fitting_id' => $product->master_product_fitting_id,
            'fitting_name' => $product->fitting->name ?? '',
            'variants' => $variants
        ]);
    }

    public function getSizeSetsByProduct($product_id)
    {
        $product = ProductionGoods::with('variants.sizeSet')->find($product_id);
        if (!$product)
            return response()->json(['status' => 'error', 'message' => 'Product not found']);

        $size_sets = $product->variants->map(function ($v) {
            return [
                'id' => $v->master_size_measurement_id,
                'name' => $v->sizeSet->name ?? 'Unknown'
            ];
        })->unique('id')->values();

        return response()->json(['status' => 'success', 'size_sets' => $size_sets]);
    }

    public function getColorsByProductSize($product_id, $size_set_id)
    {
        $variant = ProductionGoodVariant::with('items.color')
            ->where('production_goods_id', $product_id)
            ->where('master_size_measurement_id', $size_set_id)
            ->first();

        if (!$variant)
            return response()->json(['status' => 'error', 'message' => 'Variant not found']);

        $colors = $variant->items->map(function ($item) {
            return [
                'id' => $item->master_color_id,
                'name' => $item->color->name ?? 'Unknown'
            ];
        })->unique('id')->values();

        return response()->json(['status' => 'success', 'colors' => $colors]);
    }
    public function deleteBoxes(Request $request)
    {
        $request->validate([
            'modal_product_id' => 'required',
            'modal_size_set_id' => 'required',
            'modal_color_id' => 'required',
            'modal_old_rack_id' => 'required',
            'delete_quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $query = DomesticInventory::where('product_id', $request->modal_product_id)
                ->where('size_set_id', $request->modal_size_set_id)
                ->where('color_id', $request->modal_color_id)
                ->where('rack_id', $request->modal_old_rack_id);



            // Exclude items already assigned to orders
            // $query->where(function ($q) {
            //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
            // });

            $inventoryItems = $query->get();

            $total_available = $inventoryItems->sum('total_boxes');
            if ($total_available < $request->delete_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ' . $total_available . ' boxes available to delete.'
                ]);
            }

            $to_delete = (int) $request->delete_quantity;
            foreach ($inventoryItems as $item) {
                if ($to_delete <= 0)
                    break;

                $take = min($item->total_boxes, $to_delete);

                $item->total_boxes -= $take;
                if ($item->total_boxes <= 0) {
                    $item->delete();
                } else {
                    $item->save();
                }

                $to_delete -= $take;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully deleted ' . $request->delete_quantity . ' boxes.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting boxes: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getDomesticInventoryForConsume(Request $request)
    {
        $query = DomesticInventory::where('product_id', $request->product_id)
            ->where('size_set_id', $request->size_set_id)
            ->where('color_id', $request->color_id);



        $query->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->where('racks.storeroom_id', $request->warehouse_id)
            ->where('domestic_inventories.rack_id', $request->rack_id);

        $inventory = $query->select(
            'domestic_inventories.*',
            DB::raw('SUM(domestic_inventories.total_boxes) as aggregate_boxes'),
            DB::raw('SUM(domestic_inventories.total_boxes * domestic_inventories.quantity) as aggregate_pieces')
        )->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.size_set_id',
                'domestic_inventories.color_id',
                'domestic_inventories.rack_id'
            )->first();

        if ($inventory) {
            // Get FULL details like getProductFullDetails to populate the primary card
            $product = ProductionGoods::with(['variants.items.color', 'fitting', 'pattern'])->find($request->product_id);

            $variants = [];
            $all_colors = [];
            if ($product) {
                foreach ($product->variants as $variant) {
                    $colors = [];
                    foreach ($variant->items as $item) {
                        $colorData = [
                            'id' => $item->master_color_id,
                            'name' => $item->color->name ?? 'Unknown'
                        ];
                        $colors[] = $colorData;

                        // If this is the selected size set, collect all colors for the primary dropdown
                        if ($variant->master_size_measurement_id == $request->size_set_id) {
                            $all_colors[] = $colorData;
                        }
                    }

                    $variants[] = [
                        'size_set_id' => $variant->master_size_measurement_id,
                        'size_set_name' => $variant->sizeSet->name ?? 'Unknown',
                        'size_group' => $variant->sizeSet->size_group ?? '',
                        'mrp' => $variant->mrp,
                        'colors' => $colors
                    ];
                }
            }

            $mrp = 0;
            $source_size_group = '';
            foreach ($variants as $v) {
                if ($v['size_set_id'] == $request->size_set_id) {
                    $mrp = $v['mrp'];
                    $source_size_group = $v['size_group'];
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'inventory_id' => $inventory->id,
                'total_boxes' => $inventory->aggregate_boxes,
                'total_pieces' => $inventory->aggregate_pieces,
                'pieces_per_box' => $inventory->quantity,
                'mrp' => $mrp,
                'source_size_group' => $source_size_group,
                'variants' => $variants,
                'all_colors' => $all_colors
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No matching inventory found']);
    }

    public function purchaseHistory()
    {
        $vendors = \App\Models\Vendor::where('status', '1')->get();
        $customers = \App\Models\MasterCustomer::where('status', '1')->get();
        return view('admin.inventory.purchase_history.index', compact('vendors', 'customers'));
    }

    public function purchaseHistoryList(Request $request)
    {
        $query = \App\Models\DomesticInventoryPurchase::with(['user', 'vendor', 'customer', 'productionPO'])
            ->withSum('items as total_boxes', 'box_quantity');

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->has('po_number') && $request->po_number) {
            $query->whereHas('productionPO', function($q) use ($request) {
                $q->where('po_number', 'like', "%{$request->po_number}%");
            });
        }

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('production_po', function ($row) {
                return $row->productionPO ? $row->productionPO->po_number : '<span class="text-muted small">Manual</span>';
            })
            ->addColumn('source', function ($row) {
                if ($row->vendor_id)
                    return 'Vendor: ' . ($row->vendor->company_name ?? $row->vendor->name ?? 'N/A');
                if ($row->customer_id)
                    return 'Customer: ' . ($row->customer->company_name ?? $row->customer->name ?? 'N/A');
                return 'N/A';
            })
            ->filterColumn('production_po', function($query, $keyword) {
                $query->whereHas('productionPO', function($q) use ($keyword) {
                    $q->where('po_number', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('source', function($query, $keyword) {
                $query->whereHas('vendor', function($q) use ($keyword) {
                    $q->where('company_name', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%");
                })->orWhereHas('customer', function($q) use ($keyword) {
                    $q->where('company_name', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('purchase_date', function($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(purchase_date, '%d %b %Y') like ?", ["%{$keyword}%"])
                      ->orWhereRaw("DATE_FORMAT(created_at, '%d %b %Y') like ?", ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group">
                            <a href="' . route('admin.inventory.purchase_history.show', $row->id) . '" class="btn btn-sm btn-soft-info mr-1" title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="' . route('admin.inventory.purchase_history.edit', $row->id) . '" class="btn btn-sm btn-soft-primary mr-1" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-soft-danger btn-delete-purchase" data-id="' . $row->id . '" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>';
            })
            ->rawColumns(['action', 'production_po'])
            ->make(true);
    }

    public function purchaseHistoryEdit($id)
    {
        $purchase = \App\Models\DomesticInventoryPurchase::with('items.newProduct', 'items.newSizeSet', 'items.newColor', 'items.newWarehouse', 'items.newRack')->findOrFail($id);
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();

        $vendors = \App\Models\Vendor::where('status', '1')->get();
        $customers = \App\Models\MasterCustomer::where('status', '1')->get();
        $productionPOs = \App\Models\ProductionPO::where('status', 1)->orderBy('id', 'desc')->get();

        return view('admin.inventory.purchase_history.edit', compact('purchase', 'products', 'colors', 'fittings', 'patterns', 'size_sets', 'storerooms', 'vendors', 'customers', 'productionPOs'));
    }

    public function purchaseHistoryShow($id)
    {
        $purchase = \App\Models\DomesticInventoryPurchase::with([
            'items.newProduct.series',
            'items.newSizeSet',
            'items.newColor',
            'items.newWarehouse',
            'items.newRack.storeroom',
            'items.newPattern',
            'items.newFitting',
            'vendor',
            'customer',
            'user',
            'productionPO'
        ])->findOrFail($id);

        return view('admin.inventory.purchase_history.show', compact('purchase'));
    }

    public function inboundHistoryIndex()
    {
        return view('admin.inventory.inbound_history.index');
    }

    public function inboundHistoryList(Request $request)
    {
        $query = \App\Models\PackingMain::where('order_main_id', 0)
            ->where('slip_id', 0)
            ->with(['creator'])
            ->withCount('boxes as total_boxes');

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('packing_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('packing_date', '<=', $request->end_date);
        }

        // Order by latest
        $query->orderBy('id', 'desc');

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return date('d M Y', strtotime($row->packing_date ?? $row->created_at));
            })
            ->filterColumn('date', function($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(packing_date, '%d %b %Y') like ?", ["%{$keyword}%"])
                      ->orWhereRaw("DATE_FORMAT(created_at, '%d %b %Y') like ?", ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group">
                            <a href="' . route('admin.inventory.inbound_history.show', $row->id) . '" class="btn btn-sm btn-soft-info mr-1" title="View Details"><i class="fas fa-eye"></i> View Detail</a>
                        </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function inboundHistoryShow($id)
    {
        $session = \App\Models\PackingMain::where('order_main_id', 0)
            ->where('slip_id', 0)
            ->findOrFail($id);
            
        $items = \App\Models\DomesticInventoryHistory::with([
            'newProduct.series',
            'newProduct.fitting',
            'newProduct.pattern',
            'newSizeSet',
            'newColor',
            'newRack.storeroom'
        ])->where('created_at', $session->created_at)
          ->whereIn('type', ['creation', 'sample'])
          ->get();

        return view('admin.inventory.inbound_history.show', compact('session', 'items'));
    }


    public function purchaseHistoryUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $purchase = \App\Models\DomesticInventoryPurchase::findOrFail($id);

            $request->validate([
                'purchase_date' => 'nullable|date',
                'sub_total' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'products' => 'required|array|min:1',
            ]);

            // Adjust Balance (Reverse Old)
            if ($purchase->vendor_id) {
                \App\Models\Vendor::find($purchase->vendor_id)->decrement('balance', $purchase->total_amount);
            } elseif ($purchase->customer_id) {
                \App\Models\MasterCustomer::find($purchase->customer_id)->decrement('balance', $purchase->total_amount);
            }

            // 1. Revert Old Stock
            $oldItems = \App\Models\DomesticInventoryHistory::where('purchase_id', $id)->get();
            foreach ($oldItems as $oldItem) {
                $barcode = 'D' . $oldItem->new_product_id . 'S' . $oldItem->new_size_set_id . 'C' . $oldItem->new_color_id;

                $inventory = \App\Models\DomesticInventory::where('barcode', $barcode)
                    ->where('rack_id', $oldItem->new_rack_id)
                    ->where('order_main_id', 0)
                    ->first();

                if ($inventory) {
                    $inventory->total_boxes -= $oldItem->box_quantity;
                    if ($inventory->total_boxes <= 0) {
                        $inventory->delete();
                    } else {
                        $inventory->save();
                    }
                }
                $oldItem->delete();
            }

            // 2. Process New Items
            foreach ($request->products as $item) {
                // Determine source fields
                $vendorId = $request->source_type == 'vendor' ? $request->vendor_id : null;
                $customerId = $request->source_type == 'customer' ? $request->customer_id : null;

                // Create/Update Inventory
                $barcode = 'D' . $item['product_id'] . 'S' . $item['size_set_id'] . 'C' . $item['color_id'];

                $inventory = \App\Models\DomesticInventory::updateOrCreate([
                    'barcode' => $barcode,
                    'rack_id' => $item['rack_id'],
                    // 'order_main_id' => 0, // Master stock
                ], [
                    'product_id' => $item['product_id'],
                    'size_set_id' => $item['size_set_id'],
                    'color_id' => $item['color_id'],

                    'quantity' => $item['pieces_per_box'], // Usually quantity per box
                ]);

                $inventory->total_boxes += $item['total_boxes'];
                $inventory->save();

                // Create History Record
                \App\Models\DomesticInventoryHistory::create([
                    'purchase_id' => $purchase->id,
                    'new_product_id' => $item['product_id'],
                    'new_size_set_id' => $item['size_set_id'],
                    'new_color_id' => $item['color_id'],

                    'new_warehouse_id' => $item['warehouse_id'],
                    'new_rack_id' => $item['rack_id'],
                    'box_quantity' => $item['total_boxes'],
                    'pieces_per_box' => $item['pieces_per_box'],
                    'mrp' => $item['mrp'],
                    'purchase_rate' => $item['purchase_rate'],
                ]);
            }

            // 3. Update Purchase Header
            $purchase->update([
                'vendor_id' => $request->source_type == 'vendor' ? $request->vendor_id : null,
                'customer_id' => $request->source_type == 'customer' ? $request->customer_id : null,
                'production_po_id' => $request->production_po_id,
                'purchase_date' => $request->purchase_date ?? now()->toDateString(),
                'sub_total' => $request->sub_total,
                'gst_type' => $request->gst_type ?? 'percentage',
                'gst_value' => $request->gst_value ?? 0,
                'gst' => $request->gst ?? 0,
                'other_amount' => $request->other_amount ?? 0,
                'discount' => $request->discount ?? 0,
                'total_amount' => $request->total_amount,
            ]);

            // Adjust Balance (Apply New)
            if ($purchase->vendor_id) {
                \App\Models\Vendor::find($purchase->vendor_id)->increment('balance', $purchase->total_amount);
            } elseif ($purchase->customer_id) {
                \App\Models\MasterCustomer::find($purchase->customer_id)->increment('balance', $purchase->total_amount);
            }

            DB::commit();
            return redirect()->route('admin.inventory.purchase_history.index')->with('success', 'Purchase updated and inventory reconciled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
    }

    public function purchaseHistoryDestroy($id)
    {
        DB::beginTransaction();
        try {
            $purchase = \App\Models\DomesticInventoryPurchase::findOrFail($id);
            $items = \App\Models\DomesticInventoryHistory::where('purchase_id', $id)->get();

            foreach ($items as $item) {
                // Reconstruct barcode (must match the logic in store)
                $barcode = 'D' . $item->new_product_id . 'S' . $item->new_size_set_id . 'C' . $item->new_color_id;

                $inventory = \App\Models\DomesticInventory::where('barcode', $barcode)
                    ->where('rack_id', $item->new_rack_id)
                    ->where('order_main_id', 0)
                    ->first();

                if ($inventory) {
                    $inventory->total_boxes -= $item->box_quantity;
                    if ($inventory->total_boxes <= 0) {
                        $inventory->delete();
                    } else {
                        $inventory->save();
                    }
                }
                $item->delete();
            }
            
            // Adjust Party Balance (Reverse)
            if ($purchase->vendor_id) {
                \App\Models\Vendor::find($purchase->vendor_id)->decrement('balance', $purchase->total_amount);
            } elseif ($purchase->customer_id) {
                \App\Models\MasterCustomer::find($purchase->customer_id)->decrement('balance', $purchase->total_amount);
            }

            $purchase->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase deleted and stock reverted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getProductionPOItems($id)
    {
        $po = \App\Models\ProductionPO::with(['items.productSet.product', 'items.productSet.size_measurement', 'items.productSet.colors', 'items.master_fitting', 'items.pattern'])->findOrFail($id);

        $items = $po->items->map(function ($item) {
            $productSet = $item->productSet;
            if (!$productSet)
                return null;

            $product = $productSet->product;
            $sizeSet = $productSet->size_measurement;
            $color = $productSet->colors;

            // Get MRP from ProductionGoodVariant if exists
            $mrp = 0;
            if ($product && $sizeSet) {
                $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $product->id)
                    ->where('master_size_measurement_id', $sizeSet->id)
                    ->first();
                $mrp = $variant ? $variant->mrp : 0;
            }

            return [
                'product_id' => $product->id ?? null,
                'product_name' => $product->name_of_garment ?? 'N/A',
                'design_number' => $product->design_number ?? 'N/A',
                'pattern_id' => $item->master_pattern_id,
                'pattern_name' => $item->pattern->name ?? 'N/A',
                'fitting_id' => $item->master_fitting_id,
                'fitting_name' => $item->master_fitting->name ?? 'N/A',
                'size_set_id' => $productSet->set_size,
                'size_set_name' => $sizeSet->name ?? 'N/A',
                'color_id' => $productSet->color_id,
                'color_name' => $color->name ?? 'N/A',
                'total_boxes' => $item->quantity, // Usually PO quantity is sets/boxes
                'pieces_per_box' => $productSet->no_of_pcs ?? 0,
                'mrp' => $mrp,
                'purchase_rate' => $item->rate ?? 0,
            ];
        })->filter();

        return response()->json([
            'success' => true,
            'items' => $items,
            'vendor_id' => $po->vendor_id,
            'customer_id' => $po->customer_id
        ]);
    }
}
