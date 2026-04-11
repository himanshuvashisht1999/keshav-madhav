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
            'storerooms'
        ));
    }

    public function indexList(Request $request)
    {
        if ($request->ajax()) {
            // Group by Product Name, Design Number, Size Set, MRP, Selling Price
            $query = DomesticInventory::select(
                'domestic_inventories.size_set_id',
                'domestic_inventories.color_id',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                'domestic_inventories.product_id',
                'domestic_inventories.rack_id',
                'racks.name as rack_name',
                'racks.storeroom_id',
                'storerooms.name as storeroom_name',
                'products.design_number',
                'products.name_of_garment as product_name',
                'series.name as series_name',
                'colors.name as color_name',
                'sizes.name as size_set_name',
                'fittings.name as fitting_name',
                'patterns.name as pattern_name',
                'variants.mrp as mrp',
                DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
                DB::raw('MAX(domestic_inventories.created_at) as recent_date')
            )
                ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
                ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
                ->leftJoin('master_colors as colors', 'domestic_inventories.color_id', '=', 'colors.id')
                ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
                ->leftJoin('master_product_fittings as fittings', 'domestic_inventories.fitting_id', '=', 'fittings.id')
                ->leftJoin('master_design_patterns as patterns', 'domestic_inventories.pattern_id', '=', 'patterns.id')
                ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                ->leftJoin('production_goods_variants as variants', function ($join) {
                    $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                        ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
                });

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

            $data = $query->groupBy(
                'domestic_inventories.size_set_id',
                'domestic_inventories.color_id',
                'domestic_inventories.fitting_id',
                'domestic_inventories.pattern_id',
                'domestic_inventories.product_id',
                'products.design_number',
                'products.name_of_garment',
                'series.name',
                'colors.name',
                'sizes.name',
                'fittings.name',
                'patterns.name',
                'variants.mrp'
            )->get();

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('product_name_display', function ($row) {
                    return trim($row->product_name) ?: $row->design_number;
                })
                ->addColumn('mrp_display', function ($row) {
                    return '₹' . number_format($row->mrp ?? 0, 2);
                })
                ->addColumn('total_order', function ($row) {
                    $agentOrderBoxes = \App\Models\AgentOrderItem::whereHas(
                        'order',
                        function ($q) {
                            $q->where('status', '!=', 'dispatched');
                        }
                    )
                        ->where('design_number', $row->design_number)
                        ->where('color_id', $row->color_id)
                        ->where('size_set_id', $row->size_set_id)
                        ->when(
                            $row->fitting_id,
                            function ($q, $val) {
                                return $q->where('fitting_id', $val);
                            }
                            ,
                            function ($q) {
                                return $q->whereNull('fitting_id');
                            }
                        )
                        ->when(
                            $row->pattern_id,
                            function ($q, $val) {
                                return $q->where('pattern_id', $val);
                            }
                            ,
                            function ($q) {
                                return $q->whereNull('pattern_id');
                            }
                        )
                        ->sum('box_qty');

                    return (int) $agentOrderBoxes;
                })
                ->addColumn('available_boxes', function ($row) {
                    $total_boxes = $row->total_boxes ?? 0;
                    $agentOrderBoxes = \App\Models\AgentOrderItem::whereHas(
                        'order',
                        function ($q) {
                            $q->where('status', '!=', 'dispatched');
                        }
                    )
                        ->where('design_number', $row->design_number)
                        ->where('color_id', $row->color_id)
                        ->where('size_set_id', $row->size_set_id)
                        ->when(
                            $row->fitting_id,
                            function ($q, $val) {
                                return $q->where('fitting_id', $val);
                            },
                            function ($q) {
                                return $q->whereNull('fitting_id');
                            }
                        )
                        ->when(
                            $row->pattern_id,
                            function ($q, $val) {
                                return $q->where('pattern_id', $val);
                            },
                            function ($q) {
                                return $q->whereNull('pattern_id');
                            }
                        )
                        ->sum(DB::raw('box_qty - IFNULL(scanned_box_qty, 0)'));

                    return max(0, (int) ($total_boxes - $agentOrderBoxes));
                })
                ->addColumn('action', function ($row) {
                    $params = [
                        'product_id' => $row->product_id,
                        'size_set_id' => $row->size_set_id,
                        'color_id' => $row->color_id,
                        'fitting_id' => $row->fitting_id,
                        'pattern_id' => $row->pattern_id,
                    ];
                    $btn = '<a href="' . route('admin.inventory.show', $params) . '" class="btn btn-primary btn-sm btn-icon mb-1" title="View Details"><i class="fas fa-eye"></i></a>';

                    $availableBoxes = DomesticInventory::where('product_id', $row->product_id)
                        ->where('size_set_id', $row->size_set_id)
                        ->where('color_id', $row->color_id)
                        ->where(function ($q) use ($row) {
                            if ($row->fitting_id)
                                $q->where('fitting_id', $row->fitting_id);
                            else
                                $q->whereNull('fitting_id');
                        })
                        ->where(function ($q) use ($row) {
                            if ($row->pattern_id)
                                $q->where('pattern_id', $row->pattern_id);
                            else
                                $q->whereNull('pattern_id');
                        })
                        // ->where(function ($q) {
                        //     $q->whereNull('order_main_id')->orWhere('order_main_id', 0);
                        // })
                        ->sum('total_boxes');

                    $seriesId = \App\Models\ProductionGoods::find($row->product_id)->master_series_id ?? '';

                    // $btn .= ' <button type="button" class="btn btn-warning btn-sm btn-icon mb-1 text-dark font-weight-bold" 
                    //          data-toggle="modal" 
                    //          data-target="#editAttributesModal"
                    //          data-product-id="' . $row->product_id . '"
                    //          data-series-id="' . $seriesId . '"
                    //          data-design-no="' . $row->design_number . '"
                    //          data-size-set-id="' . $row->size_set_id . '"
                    //          data-color-id="' . $row->color_id . '"
                    //          data-fitting-id="' . ($row->fitting_id ?? '') . '"
                    //          data-pattern-id="' . ($row->pattern_id ?? '') . '"
                    //          data-total-boxes="' . $row->total_boxes . '"
                    //          data-available-boxes="' . $availableBoxes . '"
                    //          title="Change Product Attributes"
                    //      ><i class="fas fa-edit"></i></button>';
    
                    // $btn .= ' <button type="button" class="btn btn-danger btn-sm btn-icon mb-1 text-white font-weight-bold" 
                    //          data-toggle="modal" 
                    //          data-target="#deleteBoxesModal"
                    //          data-product-id="' . $row->product_id . '"
                    //          data-design-no="' . $row->design_number . '"
                    //          data-size-set-id="' . $row->size_set_id . '"
                    //          data-color-id="' . $row->color_id . '"
                    //          data-fitting-id="' . ($row->fitting_id ?? '') . '"
                    //          data-pattern-id="' . ($row->pattern_id ?? '') . '"
                    //          data-total-boxes="' . $row->total_boxes . '"
                    //          data-available-boxes="' . $availableBoxes . '"
                    //          title="Delete Boxes"
                    //      ><i class="fas fa-trash"></i></button>';
    
                    return $btn;
                })
                ->rawColumns(['action', 'product_name_display'])
                ->make(true);
        }
    }

    public function show(Request $request)
    {
        $query = DomesticInventory::query()
            ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
            ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
            ->leftJoin('master_colors as colors', 'domestic_inventories.color_id', '=', 'colors.id')
            ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
            ->leftJoin('master_product_fittings as fittings', 'domestic_inventories.fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'domestic_inventories.pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
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
                'variants.mrp as mrp'
            );

        if ($request->has('product_id'))
            $query->where('domestic_inventories.product_id', $request->product_id);
        if ($request->has('size_set_id'))
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        if ($request->has('color_id'))
            $query->where('domestic_inventories.color_id', $request->color_id);
        if ($request->has('fitting_id'))
            $query->where('domestic_inventories.fitting_id', $request->fitting_id);
        if ($request->has('pattern_id'))
            $query->where('domestic_inventories.pattern_id', $request->pattern_id);

        $items = $query->get();
        $group_info = $items->first();

        if (!$group_info) {
            return redirect()->route('admin.inventory.index')->with('error', 'Inventory group info not found.');
        }

        return view('admin.inventory.show', compact('items', 'group_info'));
    }
    public function create()
    {
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $storerooms = \App\Models\Storeroom::where('status', '1')->get();

        return view('admin.inventory.create', compact('products', 'colors', 'fittings', 'patterns', 'size_sets', 'storerooms'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'products.*.product_id' => 'required',
            'products.*.color_id' => 'required',
            'products.*.size_set_id' => 'required',
            'products.*.fitting_id' => 'required|exists:master_product_fittings,id',
            'products.*.pattern_id' => 'required',
            'products.*.total_boxes' => 'required|integer|min:1',
            'products.*.pieces_per_box' => 'required|integer|min:1',
            'products.*.mrp' => 'required|numeric|min:0',
            'products.*.rack_id' => 'nullable|exists:racks,id',
        ]);

        DB::beginTransaction();
        try {
            $inventoryIds = [];

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

            foreach ($request->products as $item) {
                $barcode = 'D' . $item['product_id'] . 'S' . $item['size_set_id'] . 'C' . $item['color_id'] . 'P' . $item['pattern_id'] . 'F' . $item['fitting_id'];                // Consolidated Inventory Logic: Manage stock as aggregate counts per barcode
                $inventory = DomesticInventory::where('barcode', $barcode)
                    ->where('rack_id', $item['rack_id'] ?? null)
                    // ->where('order_main_id', 0) // Unassigned stock
                    ->first();

                if ($inventory) {
                    $inventory->total_boxes += $item['total_boxes'];
                    $inventory->save();
                } else {
                    $inventory = DomesticInventory::create([
                        'product_id' => $item['product_id'],
                        'color_id' => $item['color_id'],
                        'size_set_id' => $item['size_set_id'],
                        'fitting_id' => $item['fitting_id'],
                        'pattern_id' => $item['pattern_id'],
                        'quantity' => $item['pieces_per_box'],
                        'total_boxes' => $item['total_boxes'],
                        'barcode' => $barcode,
                        'packing_main_id' => $packingMain->id,
                        'rack_id' => $item['rack_id'] ?? null,
                        'order_main_id' => 0,
                        'status' => 1
                    ]);
                }

                $inventoryIds[] = $inventory->id;

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
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock added successfully. Generating barcodes...',
                    'ids' => $inventoryIds
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
            ->where(function ($q) use ($request) {
                if ($request->fitting_id)
                    $q->where('fitting_id', $request->fitting_id);
                else
                    $q->whereNull('fitting_id');
            })
            ->where(function ($q) use ($request) {
                if ($request->pattern_id)
                    $q->where('pattern_id', $request->pattern_id);
                else
                    $q->whereNull('pattern_id');
            })
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

            // Match optional fields if provided initially
            if ($request->filled('old_fitting_id')) {
                $query->where('fitting_id', $request->old_fitting_id);
            } else {
                $query->whereNull('fitting_id');
            }

            if ($request->filled('old_pattern_id')) {
                $query->where('pattern_id', $request->old_pattern_id);
            } else {
                $query->whereNull('pattern_id');
            }

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

            $new_fitting_id = $request->new_fitting_id ?: null;
            $new_pattern_id = $request->new_pattern_id ?: null;
            $new_rack_id = $request->new_rack_id;

            // Consistent Barcode Format: D{id}S{id}C{id}P{id}F{id} (using 0 for nulls)
            $new_barcode = 'D' . $request->new_product_id . 'S' . $request->new_size_set_id . 'C' . $request->new_color_id . 'P' . ($new_pattern_id ?: 0) . 'F' . ($new_fitting_id ?: 0);            // Perform Update with Splitting Logic
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
                    $item->fitting_id == $new_fitting_id &&
                    $item->pattern_id == $new_pattern_id &&
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
                    ->where(function ($q) use ($new_fitting_id) {
                        return $new_fitting_id ? $q->where('fitting_id', $new_fitting_id) : $q->whereNull('fitting_id');
                    })
                    ->where(function ($q) use ($new_pattern_id) {
                        return $new_pattern_id ? $q->where('pattern_id', $new_pattern_id) : $q->whereNull('pattern_id');
                    })
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
                    $new_item->fitting_id = $new_fitting_id;
                    $new_item->pattern_id = $new_pattern_id;
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
                'old_fitting_id' => $request->old_fitting_id ?: null,
                'old_pattern_id' => $request->old_pattern_id ?: null,
                'old_rack_id' => $request->old_rack_id ?: null,
                'new_product_id' => $request->new_product_id,
                'new_size_set_id' => $request->new_size_set_id,
                'new_color_id' => $request->new_color_id,
                'new_fitting_id' => $new_fitting_id,
                'new_pattern_id' => $new_pattern_id,
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

            if ($request->filled('modal_fitting_id')) {
                $query->where('fitting_id', $request->modal_fitting_id);
            } else {
                $query->whereNull('fitting_id');
            }

            if ($request->filled('modal_pattern_id')) {
                $query->where('pattern_id', $request->modal_pattern_id);
            } else {
                $query->whereNull('pattern_id');
            }

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
}
