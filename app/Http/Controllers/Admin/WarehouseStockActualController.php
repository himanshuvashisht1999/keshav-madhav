<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DomesticInventory;
use App\Models\Rack;
use App\Models\Storeroom;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class WarehouseStockActualController extends Controller
{
    public function index()
    {
        $storerooms = Storeroom::where('status', '1')
            ->orderByRaw('CAST(IFNULL(NULLIF(order_priority, ""), "9999") AS UNSIGNED) ASC')
            ->orderBy('name', 'asc')
            ->get();
        $defaultStoreroomId = $storerooms->first() ? $storerooms->first()->id : '';

        // Master records for filters
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $designs = \App\Models\ProductionGoods::select('design_number')->distinct()->orderBy('design_number')->get();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $series = \App\Models\MasterSeries::all();
        $brands = \App\Models\Brand::all();
        $natures = \App\Models\ProductNature::all();
        $fabric_types = \App\Models\FabricType::all();

        return view('admin.inventory.warehouse_stock_actual.index', compact(
            'storerooms',
            'defaultStoreroomId',
            'size_sets',
            'products',
            'colors',
            'designs',
            'fittings',
            'patterns',
            'series',
            'brands',
            'natures',
            'fabric_types'
        ));
    }

    private function buildIndexQuery(Request $request)
    {
        // Physical inventory only: status = 1 and actual boxes in domestic_inventories
        $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom'])
            ->where('domestic_inventories.status', 1);

        if ($request->has('storeroom_id') && !empty($request->storeroom_id)) {
            $query->whereHas('rack', function ($q) use ($request) {
                $q->where('storeroom_id', $request->storeroom_id);
            });
        }

        if ($request->has('rack_id') && !empty($request->rack_id)) {
            $query->where('domestic_inventories.rack_id', $request->rack_id);
        }

        if ($request->has('size_set_id') && !empty($request->size_set_id)) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }

        if ($request->has('design_filter') && !empty($request->design_filter)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('design_number', $request->design_filter);
            });
        }

        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }

        if ($request->has('color_id') && !empty($request->color_id)) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }

        if ($request->has('series_id') && !empty($request->series_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('master_series_id', $request->series_id);
            });
        }

        if ($request->has('brand_id') && !empty($request->brand_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        if ($request->has('fitting_id') && !empty($request->fitting_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('master_product_fitting_id', $request->fitting_id);
            });
        }

        if ($request->has('pattern_id') && !empty($request->pattern_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('master_pattern_id', $request->pattern_id);
            });
        }

        if ($request->has('nature_id') && !empty($request->nature_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_nature_id', $request->nature_id);
            });
        }

        if ($request->has('fabric_type_id') && !empty($request->fabric_type_id)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('fabric_type_id', $request->fabric_type_id);
            });
        }

        if ($request->has('min_boxes') && $request->min_boxes !== null && $request->min_boxes !== '') {
            $query->havingRaw('SUM(domestic_inventories.total_boxes) >= ?', [$request->min_boxes]);
        }

        if ($request->has('max_boxes') && $request->max_boxes !== null && $request->max_boxes !== '') {
            $query->havingRaw('SUM(domestic_inventories.total_boxes) <= ?', [$request->max_boxes]);
        }

        $query->leftJoin('production_goods_variants as variants', function ($join) {
            $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                 ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
        });

        // ACTUAL physical stock without subtracting un-dispatched agent orders
        $query->select(
            'domestic_inventories.product_id', 
            'domestic_inventories.size_set_id', 
            'domestic_inventories.rack_id',
            DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as quantity'),
            'variants.image as product_image',
            'variants.id as variant_id'
        )
        ->groupBy('domestic_inventories.product_id', 'domestic_inventories.size_set_id', 'domestic_inventories.rack_id', 'variants.image', 'variants.id')
        ->havingRaw('SUM(domestic_inventories.total_boxes) > 0');

        return $query;
    }

    public function indexList(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        // Calculate total sum across all groups
        $totalsQuery = clone $query;
        $totalBoxes = DB::query()->fromSub($totalsQuery, 'sub')->sum('total_boxes') ?? 0;
        $totalPcs = DB::query()->fromSub($totalsQuery, 'sub')->sum(DB::raw('total_boxes * quantity')) ?? 0;

        if ($request->has('load_more')) {
            $perPage = 20;
            $results = $query->paginate($perPage);
            
            $html = '';
            $start = ($results->currentPage() - 1) * $perPage + 1;
            foreach ($results as $index => $row) {
                $row->loadMissing(['product.series', 'sizeSet', 'rack.storeroom']);
                $html .= view('admin.inventory.warehouse_stock_actual.partials.row', [
                    'row' => $row,
                    'index' => $start + $index
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null,
                'total_boxes' => $totalBoxes,
                'total_pcs' => $totalPcs
            ]);
        }
        
        return DataTables::of($query)
            ->with('total_boxes', $totalBoxes)
            ->with('total_pcs', $totalPcs)
            ->addIndexColumn()
            ->addColumn('product_name', function ($row) {
                return trim(($row->product->series->name ?? '') . ' ' . ($row->product->name_of_garment ?? 'N/A'));
            })
            ->addColumn('design_number', function ($row) {
                return $row->product->design_number ?? 'N/A';
            })
            ->addColumn('size_set_name', function ($row) {
                return $row->sizeSet->name ?? 'N/A';
            })
            ->addColumn('location', function ($row) {
                $wh = $row->rack->storeroom->name ?? 'N/A';
                $rk = $row->rack->name ?? 'N/A';
                return $wh . ' / ' . $rk;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('admin.inventory.warehouse_stock_actual.show', [$row->product_id, $row->size_set_id, $row->rack_id]) . '" class="btn btn-xs btn-primary mr-1" title="View"><i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($product_id, $size_set_id, $rack_id)
    {
        $product_id = $product_id == 0 ? null : $product_id;
        $size_set_id = $size_set_id == 0 ? null : $size_set_id;
        $rack_id = $rack_id == 0 ? null : $rack_id;

        $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom', 'carton'])
            ->where('domestic_inventories.status', 1);
        
        if ($product_id) {
            $query->where('domestic_inventories.product_id', $product_id);
        } else {
            $query->whereNull('domestic_inventories.product_id');
        }

        if ($size_set_id) {
            $query->where('domestic_inventories.size_set_id', $size_set_id);
        } else {
            $query->whereNull('domestic_inventories.size_set_id');
        }

        if ($rack_id) {
            $query->where('domestic_inventories.rack_id', $rack_id);
        } else {
            $query->whereNull('domestic_inventories.rack_id');
        }

        $query->leftJoin('production_goods_variants as variants', function ($join) {
            $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                 ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
        });

        // Group by color for the breakdown
        $items = (clone $query)->select(
            'domestic_inventories.color_id',
            'domestic_inventories.barcode',
            DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as quantity'),
            'variants.image as product_image',
            'variants.id as variant_id'
        )
        ->groupBy('domestic_inventories.color_id', 'domestic_inventories.barcode', 'variants.image', 'variants.id')
        ->havingRaw('SUM(domestic_inventories.total_boxes) > 0')
        ->get();

        // Individual boxes lying in this rack
        $boxes = (clone $query)->select(
            'domestic_inventories.id',
            'domestic_inventories.box_no',
            'domestic_inventories.carton_no',
            'domestic_inventories.color_id',
            'domestic_inventories.quantity',
            'domestic_inventories.total_boxes',
            'domestic_inventories.barcode',
            'domestic_inventories.created_at'
        )->orderBy('domestic_inventories.id', 'desc')->get();

        $product = \App\Models\ProductionGoods::with('series')->find($product_id);
        $sizeSet = \App\Models\MasterSizeMeasurement::find($size_set_id);
        $rack = Rack::with('storeroom')->find($rack_id);

        return view('admin.inventory.warehouse_stock_actual.show', compact(
            'product',
            'sizeSet',
            'rack',
            'items',
            'boxes'
        ));
    }

    public function export(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        $query->with(['product.series', 'sizeSet', 'rack.storeroom']);
        
        $data = $query->get();

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('admin.inventory.warehouse_stock_actual.export_pdf', compact('data'))
                      ->setPaper('A4', 'landscape');
            return $pdf->download('warehouse-stock-actual-' . now()->format('Y-m-d_H-i') . '.pdf');
        }

        $withPrice = $request->type === 'excel_price';

        return response()
            ->view('admin.inventory.warehouse_stock_actual.export_excel', [
                'data' => $data,
                'exportedAt' => now(),
                'withPrice' => $withPrice
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="warehouse-stock-actual-' . ($withPrice ? 'with-price-' : '') . now()->format('Y-m-d_H-i') . '.xls"'
            );
    }

    public function getRacksByStoreroom($id)
    {
        $racks = Rack::where('storeroom_id', $id)
            ->where('status', '1')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($racks);
    }
}
