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

class WarehouseInventoryController extends Controller
{
    public function index()
    {
        $storerooms = Storeroom::where('status', '1')->get();
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

        return view('admin.inventory.warehouse_stock.index', compact('storerooms', 'size_sets', 'products', 'colors', 'designs', 'fittings', 'patterns', 'series', 'brands', 'natures', 'fabric_types'));
    }

    public function indexList(Request $request)
    {
        $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom']);

        if ($request->has('storeroom_id') && !empty($request->storeroom_id)) {
            $query->whereHas('rack', function ($q) use ($request) {
                $q->where('storeroom_id', $request->storeroom_id);
            });
        }

        if ($request->has('rack_id') && !empty($request->rack_id)) {
            $query->where('rack_id', $request->rack_id);
        }

        if ($request->has('size_set_id') && !empty($request->size_set_id)) {
            $query->where('size_set_id', $request->size_set_id);
        }

        if ($request->has('design_filter') && !empty($request->design_filter)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('design_number', $request->design_filter);
            });
        }

        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('color_id') && !empty($request->color_id)) {
            $query->where('color_id', $request->color_id);
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
        
        $query->latest();

        if ($request->has('load_more')) {
            $perPage = 20;
            $results = $query->paginate($perPage);
            
            $html = '';
            $start = ($results->currentPage() - 1) * $perPage + 1;
            foreach ($results as $index => $row) {
                $html .= view('admin.inventory.warehouse_stock.partials.row', [
                    'row' => $row,
                    'index' => $start + $index
                ])->render();
            }

            $totalBoxes = (clone $query)->sum('total_boxes');
            $totalPcs = (clone $query)->sum(DB::raw('total_boxes * quantity'));

            return response()->json([
                'html' => $html,
                'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null,
                'total_boxes' => $totalBoxes,
                'total_pcs' => $totalPcs
            ]);
        }
        
        $totalBoxes = (clone $query)->sum('total_boxes');
        $totalPcs = (clone $query)->sum(DB::raw('total_boxes * quantity'));

        return Datatables::of($query)
            ->with('total_boxes', $totalBoxes)
            ->with('total_pcs', $totalPcs)
            ->addIndexColumn()
            ->addColumn('product_name', function ($row) {
                return $row->product_name;
            })
            ->addColumn('design_number', function ($row) {
                return $row->design_number;
            })
            ->addColumn('size_set_name', function ($row) {
                return $row->size_set_name;
            })
            ->addColumn('color_name', function ($row) {
                return $row->color_name;
            })
            ->addColumn('location', function ($row) {
                $wh = $row->rack->storeroom->name ?? 'N/A';
                $rk = $row->rack->name ?? 'N/A';
                return $wh . ' / ' . $rk;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('admin.inventory.warehouse_stock.show', $row->id) . '" class="btn btn-xs btn-primary mr-1" title="View"><i class="fas fa-eye"></i></a>';
                $btn .= '<button type="button" class="btn btn-xs btn-danger ml-1 btn-delete-boxes" title="Delete Boxes" 
                    data-id="' . $row->id . '" 
                    data-product-id="' . $row->product_id . '"
                    data-design-no="' . $row->design_number . '"
                    data-color-id="' . $row->color_id . '"
                    data-size-set-id="' . $row->size_set_id . '"
                    data-fitting-id="' . ($row->fitting_id ?? '') . '"
                    data-pattern-id="' . ($row->pattern_id ?? '') . '"
                    data-available-boxes="' . $row->total_boxes . '"
                    data-rack-id="' . $row->rack_id . '">
                    <i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $data = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom', 'orderMain.customer', 'carton', 'box'])->findOrFail($id);
        return view('admin.inventory.warehouse_stock.show', compact('data'));
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
            $inventory = DomesticInventory::findOrFail($request->id);
            $transferQty = (int) $request->transfer_qty;

            if ($transferQty > $inventory->total_boxes) {
                throw new \Exception("Cannot transfer more boxes than available.");
            }

            $toRack = Rack::findOrFail($request->rack_id);

            // Log in domestic_inventory_histories as requested EXCLUSIVELY
            \App\Models\DomesticInventoryHistory::create([
                'user_id' => auth()->id(),
                'old_product_id' => $inventory->product_id,
                'old_size_set_id' => $inventory->size_set_id,
                'old_color_id' => $inventory->color_id,
                'old_fitting_id' => $inventory->fitting_id,
                'old_pattern_id' => $inventory->pattern_id,
                'old_rack_id' => $inventory->rack_id,
                'new_product_id' => $inventory->product_id,
                'new_size_set_id' => $inventory->size_set_id,
                'new_color_id' => $inventory->color_id,
                'new_fitting_id' => $inventory->fitting_id,
                'new_pattern_id' => $inventory->pattern_id,
                'new_rack_id' => $toRack->id,
                'box_quantity' => $transferQty,
                'type' => 'transfer'
            ]);

            // Merge search criteria (Matches unique configuration at destination)
            $matchCriteria = [
                'product_id' => $inventory->product_id,
                'color_id' => $inventory->color_id,
                'size_set_id' => $inventory->size_set_id,
                'fitting_id' => $inventory->fitting_id,
                'pattern_id' => $inventory->pattern_id,
                'rack_id' => $toRack->id,
                'quantity' => $inventory->quantity, // Pieces per box must match
            ];

            // If transferring all boxes
            if ($transferQty == $inventory->total_boxes) {
                // Check if target configuration already exists at destination
                $target = DomesticInventory::where($matchCriteria)
                    ->where('id', '!=', $inventory->id)
                    ->first();

                if ($target) {
                    $target->increment('total_boxes', $transferQty);
                    $inventory->delete();
                } else {
                    $inventory->rack_id = $toRack->id;
                    $inventory->save();
                }
            } else {
                // Partial Transfer
                $inventory->decrement('total_boxes', $transferQty);

                // Check for existing record at destination
                $target = DomesticInventory::where($matchCriteria)->first();

                if ($target) {
                    $target->increment('total_boxes', $transferQty);
                } else {
                    $newInventory = $inventory->replicate();
                    $newInventory->total_boxes = $transferQty;
                    $newInventory->rack_id = $toRack->id;
                    $newInventory->save();
                }
            }

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

            foreach ($items as $item) {
                \App\Models\DomesticInventoryHistory::create([
                    'user_id' => auth()->id(),
                    'old_product_id' => $item->product_id,
                    'old_rack_id' => $item->rack_id,
                    'new_product_id' => $item->product_id,
                    'new_rack_id' => $toRack->id,
                    'box_quantity' => $item->total_boxes,
                    'type' => 'transfer'
                ]);

                $matchCriteria = [
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
                    'fitting_id' => $item->fitting_id,
                    'pattern_id' => $item->pattern_id,
                    'rack_id' => $toRack->id,
                    'quantity' => $item->quantity,
                ];

                $target = DomesticInventory::where($matchCriteria)
                    ->where('id', '!=', $item->id)
                    ->first();

                if ($target) {
                    $target->increment('total_boxes', $item->total_boxes);
                    $item->delete();
                } else {
                    $item->rack_id = $toRack->id;
                    $item->save();
                }
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
            'fitting_id' => 'nullable|exists:master_product_fittings,id',
            'pattern_id' => 'nullable|exists:master_design_patterns,id',
            'update_qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $inventory = DomesticInventory::findOrFail($request->id);
            $updateQty = (int) $request->update_qty;

            if ($updateQty > $inventory->total_boxes) {
                throw new \Exception("Cannot update more boxes than available.");
            }

            $newFittingId = $request->fitting_id ?: null;
            $newPatternId = $request->pattern_id ?: null;

            // Log History
            \App\Models\DomesticInventoryHistory::create([
                'user_id' => auth()->id(),
                'old_product_id' => $inventory->product_id,
                'old_size_set_id' => $inventory->size_set_id,
                'old_color_id' => $inventory->color_id,
                'old_fitting_id' => $inventory->fitting_id,
                'old_pattern_id' => $inventory->pattern_id,
                'old_rack_id' => $inventory->rack_id,
                'new_product_id' => $request->product_id,
                'new_size_set_id' => $request->size_set_id,
                'new_color_id' => $request->color_id,
                'new_fitting_id' => $newFittingId,
                'new_pattern_id' => $newPatternId,
                'new_rack_id' => $inventory->rack_id,
                'box_quantity' => $updateQty,
                'type' => 'attribute_change'
            ]);

            // Construct new barcode
            $newBarcode = 'D' . $request->product_id . 'S' . $request->size_set_id . 'C' . $request->color_id . 'P' . ($newPatternId ?: 0) . 'F' . ($newFittingId ?: 0);

            // Match criteria for merging at SAME rack
            $matchCriteria = [
                'product_id' => $request->product_id,
                'color_id' => $request->color_id,
                'size_set_id' => $request->size_set_id,
                'fitting_id' => $newFittingId,
                'pattern_id' => $newPatternId,
                'rack_id' => $inventory->rack_id,
                'quantity' => $inventory->quantity, // Pieces per box should ideally remain same
            ];

            if ($updateQty == $inventory->total_boxes) {
                // Changing entire row
                $target = DomesticInventory::where($matchCriteria)
                    ->where('id', '!=', $inventory->id)
                    ->first();

                if ($target) {
                    $target->increment('total_boxes', $updateQty);

                    // Update individual boxes before deleting
                    // We filter by rack_id to ensure we only update boxes in the correct location
                    $boxesToUpdateIds = DB::table('packing_boxes')
                        ->join('packing_cartons', 'packing_boxes.packing_carton_id', '=', 'packing_cartons.id')
                        ->where('packing_boxes.barcode', $inventory->barcode)
                        ->where('packing_cartons.rack_id', $inventory->rack_id)
                        ->select('packing_boxes.id')
                        ->pluck('id');

                    if ($boxesToUpdateIds->isNotEmpty()) {
                        DB::table('packing_boxes')
                            ->whereIn('id', $boxesToUpdateIds)
                            ->update(['barcode' => $newBarcode]);
                    }

                    $inventory->delete();
                } else {
                    $oldBarcode = $inventory->barcode;
                    $inventory->product_id = $request->product_id;
                    $inventory->color_id = $request->color_id;
                    $inventory->size_set_id = $request->size_set_id;
                    $inventory->fitting_id = $newFittingId;
                    $inventory->pattern_id = $newPatternId;
                    $inventory->barcode = $newBarcode;
                    $inventory->save();

                    $boxesToUpdateIds = DB::table('packing_boxes')
                        ->join('packing_cartons', 'packing_boxes.packing_carton_id', '=', 'packing_cartons.id')
                        ->where('packing_boxes.barcode', $oldBarcode)
                        ->where('packing_cartons.rack_id', $inventory->rack_id)
                        ->select('packing_boxes.id')
                        ->pluck('id');

                    if ($boxesToUpdateIds->isNotEmpty()) {
                        DB::table('packing_boxes')
                            ->whereIn('id', $boxesToUpdateIds)
                            ->update(['barcode' => $newBarcode]);
                    }
                }
            } else {
                // Partial Change
                $oldBarcode = $inventory->barcode;
                $inventory->decrement('total_boxes', $updateQty);

                $target = DomesticInventory::where($matchCriteria)->first();
                if ($target) {
                    $target->increment('total_boxes', $updateQty);

                    // Assign boxes to the target
                    $boxesToUpdateIds = DB::table('packing_boxes')
                        ->join('packing_cartons', 'packing_boxes.packing_carton_id', '=', 'packing_cartons.id')
                        ->where('packing_boxes.barcode', $oldBarcode)
                        ->where('packing_cartons.rack_id', $inventory->rack_id)
                        ->limit($updateQty)
                        ->select('packing_boxes.id')
                        ->pluck('id');

                    if ($boxesToUpdateIds->isNotEmpty()) {
                        DB::table('packing_boxes')
                            ->whereIn('id', $boxesToUpdateIds)
                            ->update(['barcode' => $newBarcode]);
                    }
                } else {
                    $newInventory = $inventory->replicate();
                    $newInventory->product_id = $request->product_id;
                    $newInventory->color_id = $request->color_id;
                    $newInventory->size_set_id = $request->size_set_id;
                    $newInventory->fitting_id = $newFittingId;
                    $newInventory->pattern_id = $newPatternId;
                    $newInventory->total_boxes = $updateQty;
                    $newInventory->barcode = $newBarcode;
                    $newInventory->save();

                    $boxesToUpdateIds = DB::table('packing_boxes')
                        ->join('packing_cartons', 'packing_boxes.packing_carton_id', '=', 'packing_cartons.id')
                        ->where('packing_boxes.barcode', $oldBarcode)
                        ->where('packing_cartons.rack_id', $inventory->rack_id)
                        ->limit($updateQty)
                        ->select('packing_boxes.id')
                        ->pluck('id');

                    if ($boxesToUpdateIds->isNotEmpty()) {
                        DB::table('packing_boxes')
                            ->whereIn('id', $boxesToUpdateIds)
                            ->update(['barcode' => $newBarcode]);
                    }
                }
            }

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

        try {
            DB::beginTransaction();

            $inventory = DomesticInventory::findOrFail($request->id);

            if ($inventory->total_boxes < $request->delete_qty) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only ' . $inventory->total_boxes . ' boxes available to delete.'
                ]);
            }

            // Log History
            \App\Models\DomesticInventoryHistory::create([
                'user_id' => auth()->id(),
                'old_product_id' => $inventory->product_id,
                'old_size_set_id' => $inventory->size_set_id,
                'old_color_id' => $inventory->color_id,
                'old_fitting_id' => $inventory->fitting_id,
                'old_pattern_id' => $inventory->pattern_id,
                'old_rack_id' => $inventory->rack_id,
                'box_quantity' => $request->delete_qty,
                'type' => 'deletion'
            ]);

            // Find individual boxes to delete
            $boxesToDeleteIds = DB::table('packing_boxes')
                ->join('packing_cartons', 'packing_boxes.packing_carton_id', '=', 'packing_cartons.id')
                ->where('packing_boxes.barcode', $inventory->barcode)
                ->where('packing_cartons.rack_id', $inventory->rack_id)
                ->limit($request->delete_qty)
                ->select('packing_boxes.id')
                ->pluck('id');

            if ($boxesToDeleteIds->isNotEmpty()) {
                DB::table('packing_boxes')->whereIn('id', $boxesToDeleteIds)->delete();
            }

            $inventory->total_boxes -= $request->delete_qty;
            if ($inventory->total_boxes <= 0) {
                $inventory->delete();
            } else {
                $inventory->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully deleted ' . $request->delete_qty . ' boxes.'
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
