<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DomesticInventory;
use App\Models\PackingCarton;
use App\Models\Rack;
use App\Models\Storeroom;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Admin\InventoryStockService;

class WarehouseInventoryController extends Controller
{
    public function index()
    {
        $storerooms = Storeroom::where('status', '1')
            ->orderByRaw('CAST(IFNULL(NULLIF(order_priority, ""), "9999") AS UNSIGNED) ASC')
            ->orderBy('name', 'asc')
            ->get();
        $defaultStoreroomId = $storerooms->first() ? $storerooms->first()->id : '';

        // Use all master records for filters to ensure they are visible
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

        return view('admin.inventory.warehouse_stock.index', compact('storerooms', 'defaultStoreroomId', 'size_sets', 'products', 'colors', 'designs', 'fittings', 'patterns', 'series', 'brands', 'natures', 'fabric_types'));
    }

    private function buildIndexQuery(Request $request)
    {
        $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom']);

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
            $query->havingRaw('SUM(domestic_inventories.total_boxes) - MAX(COALESCE(order_totals.total_ordered_boxes, 0)) >= ?', [$request->min_boxes]);
        }

        if ($request->has('max_boxes') && $request->max_boxes !== null && $request->max_boxes !== '') {
            $query->havingRaw('SUM(domestic_inventories.total_boxes) - MAX(COALESCE(order_totals.total_ordered_boxes, 0)) <= ?', [$request->max_boxes]);
        }

        // Boxes open orders still have to take (picked boxes have already left the stock, dispatched lines are done)
        $query->leftJoin(DB::raw(InventoryStockService::openOrderBoxesSql(['product_id', 'size_set_id', 'rack_id'], 'total_ordered_boxes') . ' as order_totals'), function ($join) {
            $join->on('domestic_inventories.product_id', '=', 'order_totals.product_id')
                 ->on('domestic_inventories.size_set_id', '=', 'order_totals.size_set_id')
                 ->on('domestic_inventories.rack_id', '=', 'order_totals.rack_id');
        });

        $query->leftJoin('production_goods_variants as variants', function ($join) {
            InventoryStockService::skipDuplicateVariants($join);
            $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                 ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
        });

        $mrpFrom = $request->input('mrp_from', $request->input('min_mrp'));
        if ($mrpFrom !== null && $mrpFrom !== '') {
            $query->where('variants.mrp', '>=', (float) $mrpFrom);
        }

        $mrpTo = $request->input('mrp_to', $request->input('max_mrp'));
        if ($mrpTo !== null && $mrpTo !== '') {
            $query->where('variants.mrp', '<=', (float) $mrpTo);
        }

        $query->select(
            'domestic_inventories.product_id', 
            'domestic_inventories.size_set_id', 
            'domestic_inventories.rack_id',
            DB::raw('SUM(domestic_inventories.total_boxes) - MAX(COALESCE(order_totals.total_ordered_boxes, 0)) as total_boxes'),
            DB::raw('MAX(domestic_inventories.quantity) as quantity'), // Assuming pieces per box is the same, use MAX
            'variants.image as product_image',
            'variants.id as variant_id',
            'variants.mrp as mrp'
        )->groupBy(
            'domestic_inventories.product_id', 
            'domestic_inventories.size_set_id', 
            'domestic_inventories.rack_id', 
            'variants.image', 
            'variants.id',
            'variants.mrp'
        );

        return $query;
    }

    public function indexList(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        // Calculate total sum across all groups (BEFORE pagination mutates $query)
        $totalsQuery = clone $query;
        $totalBoxes = DB::query()->fromSub($totalsQuery, 'sub')->sum('total_boxes');
        $totalPcs = DB::query()->fromSub($totalsQuery, 'sub')->sum(DB::raw('total_boxes * quantity'));

        if ($request->has('load_more')) {
            $perPage = 20;
            $results = $query->paginate($perPage);
            
            $html = '';
            $start = ($results->currentPage() - 1) * $perPage + 1;
            foreach ($results as $index => $row) {
                // Ensure relationships are loaded for the view
                $row->loadMissing(['product.series', 'sizeSet', 'rack.storeroom']);
                $html .= view('admin.inventory.warehouse_stock.partials.row', [
                    'row' => $row,
                    'index' => $start + $index
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null,
                'meta' => ['current' => $results->currentPage(), 'last' => $results->lastPage(), 'total' => $results->total(), 'from' => $results->firstItem() ?? 0, 'to' => $results->lastItem() ?? 0],
                'total_boxes' => $totalBoxes,
                'total_pcs' => $totalPcs
            ]);
        }
        
        return Datatables::of($query)
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
            ->addColumn('mrp', function ($row) {
                return $row->mrp ? '₹' . number_format($row->mrp, 2) : '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('admin.inventory.warehouse_stock.show', [$row->product_id, $row->size_set_id, $row->rack_id]) . '" class="btn btn-xs btn-primary mr-1" title="View"><i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        // eager load to avoid N+1 in export
        $query->with(['product.series', 'sizeSet', 'rack.storeroom']);
        
        $data = $query->get();

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('admin.inventory.warehouse_stock.export_pdf', compact('data'))
                      ->setPaper('A4', 'landscape');
            return $pdf->download('warehouse-stock-' . now()->format('Y-m-d_H-i') . '.pdf');
        }

        $withPrice = $request->type === 'excel_price';

        return response()
            ->view('admin.inventory.warehouse_stock.export_excel', [
                'data' => $data,
                'exportedAt' => now(),
                'withPrice' => $withPrice
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="warehouse-stock-' . ($withPrice ? 'with-price-' : '') . now()->format('Y-m-d_H-i') . '.xls"'
            );
    }

    public function show($product_id, $size_set_id, $rack_id)
    {
        $product_id = $product_id == 0 ? null : $product_id;
        $size_set_id = $size_set_id == 0 ? null : $size_set_id;
        $rack_id = $rack_id == 0 ? null : $rack_id;

        $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom', 'carton']);
        
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

        $query->leftJoin(DB::raw(InventoryStockService::openOrderBoxesSql(['barcode', 'rack_id'], 'color_total_ordered') . ' as order_totals'), function ($join) {
            $join->on('domestic_inventories.barcode', '=', 'order_totals.barcode')
                 ->on('domestic_inventories.rack_id', '=', 'order_totals.rack_id');
        });
        // empty rows (0 boxes, nothing ordered) are not shown
        $query->where(function ($q) {
            $q->where('domestic_inventories.total_boxes', '>', 0)->orWhere('order_totals.color_total_ordered', '>', 0);
        });

        $query->leftJoin('production_goods_variants as variants', function ($join) {
            InventoryStockService::skipDuplicateVariants($join);
            $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                 ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
        });

        $query->select(
            'domestic_inventories.*',
            'variants.image as product_image',
            'variants.id as variant_id',
            DB::raw('domestic_inventories.total_boxes - COALESCE(order_totals.color_total_ordered, 0) as total_boxes')
        );

        $items = $query->get();
            
        if ($items->isEmpty()) {
            return redirect()->route('admin.inventory.warehouse_stock')->withError('No inventory found for this selection.');
        }

        // Pass the grouping attributes for display
        $product = $items->first()->product;
        $sizeSet = $items->first()->sizeSet;
        $rack = $items->first()->rack;

        $storerooms = Storeroom::where('status', '1')->get();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();

        return view('admin.inventory.warehouse_stock.show', compact('items', 'product', 'sizeSet', 'rack', 'storerooms', 'size_sets', 'products', 'colors', 'fittings', 'patterns'));
    }

    public function transferRow(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:domestic_inventories,id',
            'rack_id' => 'required|exists:racks,id',
            'transfer_qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $stock = app(InventoryStockService::class);
            $inventory = $stock->lockRow($request->id);
            $transferQty = (int) $request->transfer_qty;

            if ($transferQty > $inventory->total_boxes) {
                throw new \Exception("Cannot transfer more boxes than available.");
            }

            $toRack = Rack::findOrFail($request->rack_id);
            if ((int) $toRack->id === (int) $inventory->rack_id) {
                throw new \Exception("The boxes are already in this rack. Please choose another rack.");
            }

            // The boxes go to THIS item's row in the chosen rack (same pieces per box); the old row stays (it may become 0)
            $target = $stock->rowAt($inventory->product_id, $inventory->color_id, $inventory->size_set_id, $toRack->id, $inventory->quantity);
            $stock->take($inventory, $transferQty);
            $stock->add($target, $transferQty);

            $stock->history('transfer', $inventory, $transferQty, [
                'new_product_id' => $inventory->product_id,
                'new_size_set_id' => $inventory->size_set_id,
                'new_color_id' => $inventory->color_id,
                'new_rack_id' => $toRack->id,
                'new_warehouse_id' => $toRack->storeroom_id,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Inventory transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transfer Row Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function transfer(Request $request)
    {
        // Keeping this for potential bulk transfers if still used, but updating to use History
        $request->validate([
            'rack_id' => 'required|exists:racks,id',
            'carton_ids' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $toRack = Rack::findOrFail($request->rack_id);
            $items = DomesticInventory::whereIn('packing_carton_id', $request->carton_ids)->get();

            $stock = app(InventoryStockService::class);
            foreach ($items as $item) {
                $item = $stock->lockRow($item->id);
                $boxes = (int) $item->total_boxes;
                if ($boxes <= 0 || (int) $item->rack_id === (int) $toRack->id) continue;

                $target = $stock->rowAt($item->product_id, $item->color_id, $item->size_set_id, $toRack->id, $item->quantity);
                $stock->take($item, $boxes);
                $stock->add($target, $boxes);
                $stock->history('transfer', $item, $boxes, [
                    'new_product_id' => $item->product_id,
                    'new_size_set_id' => $item->size_set_id,
                    'new_color_id' => $item->color_id,
                    'new_rack_id' => $toRack->id,
                    'new_warehouse_id' => $toRack->storeroom_id,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function history()
    {
        return view('admin.inventory.warehouse_stock.history');
    }

    public function indexHistoryList(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\DomesticInventoryHistory::with(['oldRack.storeroom', 'newRack.storeroom', 'oldProduct', 'user'])
                ->where('type', 'transfer')
                ->latest();

            return datatables()->of($query->get())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.inventory.warehouse_stock.history.show', $row->id) . '" class="btn btn-xs btn-primary mr-1" title="View"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.inventory.warehouse_stock.download_slip', $row->id) . '" class="btn btn-xs btn-info" target="_blank" title="Slip"><i class="fa fa-download"></i></a>';
                    return $btn;
                })
                ->addColumn('from_location', function ($row) {
                    return ($row->oldRack->storeroom->name ?? 'N/A') . ' / ' . ($row->oldRack->name ?? 'N/A');
                })
                ->addColumn('to_location', function ($row) {
                    return ($row->newRack->storeroom->name ?? 'N/A') . ' / ' . ($row->newRack->name ?? 'N/A');
                })
                ->addColumn('product_details', function ($row) {
                    return ($row->oldProduct->design_number ?? '') . ' - ' . ($row->oldProduct->name_of_garment ?? '');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function showHistory($id)
    {
        $history = \App\Models\DomesticInventoryHistory::with([
            'oldProduct.series',
            'oldColor',
            'oldSizeSet',
            'oldRack.storeroom',
            'newRack.storeroom',
            'user'
        ])->findOrFail($id);

        return view('admin.inventory.warehouse_stock.show_history', compact('history'));
    }

    public function downloadSlip($id)
    {
        $history = \App\Models\DomesticInventoryHistory::with([
            'oldProduct.series',
            'oldColor',
            'oldSizeSet',
            'oldRack.storeroom',
            'newRack.storeroom',
            'user'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('admin.inventory.warehouse_stock.slip', [
            'history' => $history
        ]);

        return $pdf->download('transfer_' . $history->id . '.pdf');
    }

    public function getRacksByStoreroom($id)
    {
        $racks = \App\Models\Rack::where('storeroom_id', $id)->where('status', '1')->get(['id', 'name']);
        return response()->json($racks);
    }

    public function updateAttributes(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:domestic_inventories,id',
            'product_id' => 'required|exists:production_goods,id',
            'color_id' => 'required|exists:master_colors,id',
            'size_set_id' => 'required|exists:master_size_measurements,id',

            'update_qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $stock = app(InventoryStockService::class);
            $inventory = $stock->lockRow($request->id);
            $updateQty = (int) $request->update_qty;

            if ($updateQty > $inventory->total_boxes) {
                throw new \Exception("Cannot update more boxes than available.");
            }

            $same = $inventory->product_id == $request->product_id && $inventory->color_id == $request->color_id && $inventory->size_set_id == $request->size_set_id;
            if ($same) {
                throw new \Exception("Nothing to change: the product, colour and size set are the same.");
            }

            // The boxes become the new item in the SAME rack (same pieces per box); the old row stays (it may become 0)
            $target = $stock->rowAt($request->product_id, $request->color_id, $request->size_set_id, $inventory->rack_id, $inventory->quantity);
            $stock->take($inventory, $updateQty);
            $stock->add($target, $updateQty);

            $stock->history('attribute_change', $inventory, $updateQty, [
                'new_product_id' => $request->product_id,
                'new_size_set_id' => $request->size_set_id,
                'new_color_id' => $request->color_id,
                'new_rack_id' => $inventory->rack_id,
                'new_warehouse_id' => $inventory->rack ? $inventory->rack->storeroom_id : null,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Attributes updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Attributes Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    public function deleteBoxes(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:domestic_inventories,id',
            'delete_qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $stock = app(InventoryStockService::class);
            $inventory = $stock->lockRow($request->id);
            $deleteQty = (int) $request->delete_qty;

            if ($inventory->total_boxes < $deleteQty) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only ' . $inventory->total_boxes . ' boxes available to delete.'
                ]);
            }

            log_deletion('Warehouse Inventory', $inventory->id, [
                'inventory'         => $inventory->toArray(),
                'deleted_boxes_qty' => $deleteQty,
                'rack_id'           => $inventory->rack_id,
                'barcode'           => $inventory->barcode,
            ]);

            $stock->take($inventory, $deleteQty); // the row stays, possibly with 0 boxes
            $stock->history('deletion', $inventory, $deleteQty);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully deleted ' . $deleteQty . ' boxes.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting boxes: ' . $e->getMessage()
            ], 500);
        }
    }
}
