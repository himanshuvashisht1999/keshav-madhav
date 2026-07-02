<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DomesticInventory;
use App\Models\Rack;
use App\Models\Storeroom;
use App\Models\DomesticInventoryHistory;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockTransferController extends Controller
{
    public function index()
    {
        $storerooms = Storeroom::where('status', '1')->get();
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $designs = \App\Models\ProductionGoods::select('design_number')->distinct()->orderBy('design_number')->get();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();

        return view('admin.inventory.stock_transfer.index', compact(
            'storerooms', 'size_sets', 'products', 'colors', 'designs', 'fittings', 'patterns'
        ));
    }

    public function search(Request $request)
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

        if ($request->has('fitting_id') && !empty($request->fitting_id)) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('master_product_fitting_id', $request->fitting_id);
            });
        }

        if ($request->has('pattern_id') && !empty($request->pattern_id)) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('master_pattern_id', $request->pattern_id);
            });
        }

        $perPage = 20;
        $results = $query->paginate($perPage);

        $html = '';
        foreach ($results as $row) {
            $html .= view('admin.inventory.stock_transfer.partials.row', compact('row'))->render();
        }

        return response()->json([
            'html' => $html,
            'next_page' => $results->nextPageUrl() ? $results->currentPage() + 1 : null,
            'total' => $results->total()
        ]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'target_rack_id' => 'required|exists:racks,id',
            'inventory_ids' => 'required|array',
            'transfer_qty' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $toRack = Rack::findOrFail($request->target_rack_id);
            $inventoryIds = $request->inventory_ids;
            $transferQtys = $request->transfer_qty;

            foreach ($inventoryIds as $id) {
                $inventory = DomesticInventory::findOrFail($id);
                $transferQty = (int) $transferQtys[$id];

                if ($transferQty <= 0) continue;
                if ($transferQty > $inventory->total_boxes) {
                    throw new \Exception("Transfer quantity exceeds available stock for Design: " . $inventory->design_number);
                }

                // Log History
                DomesticInventoryHistory::create([
                    'user_id' => auth()->id(),
                    'old_product_id' => $inventory->product_id,
                    'old_size_set_id' => $inventory->size_set_id,
                    'old_color_id' => $inventory->color_id,

                    'old_rack_id' => $inventory->rack_id,
                    'new_product_id' => $inventory->product_id,
                    'new_size_set_id' => $inventory->size_set_id,
                    'new_color_id' => $inventory->color_id,

                    'new_rack_id' => $toRack->id,
                    'box_quantity' => $transferQty,
                    'type' => 'transfer'
                ]);

                // Merge criteria
                $matchCriteria = [
                    'product_id' => $inventory->product_id,
                    'color_id' => $inventory->color_id,
                    'size_set_id' => $inventory->size_set_id,

                    'rack_id' => $toRack->id,
                    'quantity' => $inventory->quantity,
                ];

                if ($transferQty == $inventory->total_boxes) {
                    // Full transfer
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
                    // Partial transfer
                    $inventory->decrement('total_boxes', $transferQty);
                    
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
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Stock transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode'      => 'required|string',
            'from_rack_id' => 'required|integer|exists:racks,id',
        ]);

        $barcode     = strtoupper(trim($request->barcode));
        $barcode     = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode);
        $parsedBarcode = parseCompactBarcode($barcode);
        $fromRack    = Rack::with('storeroom')->findOrFail($request->from_rack_id);

        // Look up matching inventory record at the given source rack
        $inventoryQuery = DomesticInventory::with(['product', 'sizeSet', 'color', 'rack.storeroom'])
            ->where('rack_id', $fromRack->id)
            ->where('total_boxes', '>', 0);

        if (preg_match('/^D(\d+)S(\d+)C(\d+)/', $parsedBarcode, $matches)) {
            $inventoryQuery->where('product_id', $matches[1])
                ->where('size_set_id', $matches[2])
                ->where('color_id', $matches[3]);
        } else {
            $inventoryQuery->where('barcode', $parsedBarcode);
        }

        $inventory = $inventoryQuery->first();

        if (!$inventory) {
            return response()->json([
                'status'  => 'error',
                'message' => "Barcode \"{$barcode}\" not found in " .
                             ($fromRack->storeroom->name ?? 'selected warehouse') .
                             " / {$fromRack->name}. Please check the source location.",
            ], 404);
        }

        return response()->json([
            'status'        => 'ok',
            'inventory_id'  => $inventory->id,
            'barcode'       => $barcode,
            'product_name'  => $inventory->product->name ?? $inventory->product_name ?? '-',
            'design_number' => $inventory->product->design_number ?? $inventory->design_number ?? '-',
            'color_name'    => $inventory->color->name ?? $inventory->color_name ?? '-',
            'size_set_name' => $inventory->sizeSet->name ?? $inventory->size_set_name ?? '-',
            'warehouse'     => $fromRack->storeroom->name ?? '-',
            'rack'          => $fromRack->name,
            'total_boxes'   => $inventory->total_boxes,
        ]);
    }
}

