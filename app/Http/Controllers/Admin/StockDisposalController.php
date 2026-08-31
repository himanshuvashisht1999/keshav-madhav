<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockDisposalMain;
use App\Models\StockDisposalItem;
use App\Models\FabricReceiptDetail;
use App\Models\DomesticInventory;
use App\Models\DomesticInventoryHistory;
use App\Models\FabricInventoryHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class StockDisposalController extends Controller
{
    public function index()
    {
        return view('admin.stock_disposal.index');
    }

    public function historyList(Request $request)
    {
        $query = StockDisposalMain::with(['user'])->withCount('items')->latest();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '<div class="btn-group">
                    <a href="'.route('admin.inventory.stock_disposal.show', $row->id).'" class="btn btn-xs btn-info" title="View Details"><i class="fas fa-eye"></i></a>
                    <a href="'.route('admin.inventory.stock_disposal.edit', $row->id).'" class="btn btn-xs btn-primary" title="Edit Full Details"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-xs btn-danger delete-disposal" data-id="'.$row->id.'" title="Delete/Restore Stock"><i class="fas fa-trash"></i></button>
                </div>';
            })
            ->make(true);
    }

    public function show($id)
    {
        $main = StockDisposalMain::with(['user', 'items.fabricReceiptDetail.fabric', 'items.domesticInventory.product', 'items.domesticInventory.color', 'items.domesticInventory.sizeSet'])->findOrFail($id);
        return view('admin.stock_disposal.show', compact('main'));
    }

    public function create()
    {
        $fabricWarehouses = \App\Models\MasterFabricWarehouse::where('status', 1)->get();
        $storerooms = \App\Models\Storeroom::where('status', 1)->get();
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $fittings = \App\Models\MasterProductFitting::all();
        $patterns = \App\Models\MasterDesignPattern::all();
        $size_sets = \App\Models\MasterSizeMeasurement::all();

        return view('admin.stock_disposal.create', compact(
            'fabricWarehouses', 'storerooms', 'products', 'colors', 'fittings', 'patterns', 'size_sets'
        ));
    }

    public function getFabrics(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $disposal_id = $request->disposal_id;

        $fabrics = \App\Models\Fabric::whereHas('receiptDetails', function($q) use ($warehouse_id, $disposal_id) {
            $q->where('master_fabric_warehouse_id', $warehouse_id)
              ->where(function($sq) use ($disposal_id) {
                  $sq->where('remaining_quantity', '>', 0);
                  if ($disposal_id) {
                      $sq->orWhereIn('id', function($sub) use ($disposal_id) {
                          $sub->select('item_id')
                              ->from('stock_disposal_items')
                              ->where('stock_disposal_main_id', $disposal_id);
                      });
                  }
              });
        })->get(['id', 'name']);

        return response()->json($fabrics);
    }

    public function getRolls(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $fabric_ids = $request->fabric_ids;
        $disposal_id = $request->disposal_id;

        $rolls = FabricReceiptDetail::with('fabric')
            ->where('master_fabric_warehouse_id', $warehouse_id)
            ->whereIn('fabric_id', (array)$fabric_ids)
            ->where(function($q) use ($disposal_id) {
                $q->where('remaining_quantity', '>', 0);
                if ($disposal_id) {
                    $q->orWhereIn('id', function($sub) use ($disposal_id) {
                        $sub->select('item_id')
                            ->from('stock_disposal_items')
                            ->where('stock_disposal_main_id', $disposal_id);
                    });
                }
            })
            ->get();

        return response()->json($rolls);
    }

    public function getDomesticStock(Request $request)
    {
        $inventory = DomesticInventory::where('product_id', $request->product_id)
            ->where('size_set_id', $request->size_set_id)
            ->where('color_id', $request->color_id)
            ->whereHas('rack', function($q) use ($request) {
                $q->where('storeroom_id', $request->warehouse_id);
            })
            ->sum('total_boxes');

        return response()->json(['available' => $inventory]);
    }

    public function getProductDetails(Request $request)
    {
        $product_id = $request->product_id;
        
        // Get all unique size sets available in inventory for this product
        $sizeSets = \App\Models\MasterSizeMeasurement::whereIn('id', function($query) use ($product_id) {
            $query->select('size_set_id')
                  ->from('domestic_inventories')
                  ->where('product_id', $product_id)
                  ->where('total_boxes', '>', 0);
        })->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'size_sets' => $sizeSets
        ]);
    }

    public function getSizeColors(Request $request)
    {
        $product_id = $request->product_id;
        $size_set_id = $request->size_set_id;

        $colors = \App\Models\MasterColor::whereIn('id', function($query) use ($product_id, $size_set_id) {
            $query->select('color_id')
                  ->from('domestic_inventories')
                  ->where('product_id', $product_id)
                  ->where('size_set_id', $size_set_id)
                  ->where('total_boxes', '>', 0);
        })->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'colors' => $colors
        ]);
    }

    public function search(Request $request)
    {
        // Keeping this for backward compatibility if needed, but the main UI will use other methods
        $barcode = trim($request->barcode);
        $barcode = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode);
        $barcode = parseCompactBarcode($barcode);

        // Search in Fabric
        $fabric = FabricReceiptDetail::with('fabric')
            ->where('barcode', $barcode)
            ->orWhere('roll_number', $barcode)
            ->where('remaining_quantity', '>', 0)
            ->first();

        if ($fabric) {
            return response()->json([
                'success' => true,
                'type' => 'fabric',
                'id' => $fabric->id,
                'name' => $fabric->fabric->name ?? 'N/A',
                'sku' => $fabric->fabric_sku ?? $fabric->sku ?? 'N/A',
                'quantity' => $fabric->remaining_quantity,
                'unit' => 'Meter',
                'roll_no' => $fabric->roll_number
            ]);
        }

        // Search in Domestic Inventory (Box)
        $inventory = DomesticInventory::with(['product', 'color', 'sizeSet'])
            ->where('barcode', $barcode)
            ->where('total_boxes', '>', 0)
            ->first();

        if ($inventory) {
            return response()->json([
                'success' => true,
                'type' => 'box',
                'id' => $inventory->id,
                'name' => $inventory->product_name ?? 'N/A',
                'sku' => $inventory->design_number ?? 'N/A',
                'quantity' => $inventory->total_boxes,
                'unit' => 'Boxes',
                'details' => "Color: {$inventory->color_name}, Size: {$inventory->size_set_name}"
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found or already empty.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:fabric,domestic',
            'reason' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $disposal_no = 'DISP-' . strtoupper(Str::random(8)) . '-' . time();
            
            $main = StockDisposalMain::create([
                'disposal_no' => $disposal_no,
                'user_id' => auth()->id(),
                'item_type' => $request->item_type,
                'reason' => $request->reason,
                'remarks' => $request->remarks,
            ]);

            if ($request->item_type === 'fabric') {
                if (!$request->has('roll_ids')) {
                    throw new \Exception('No rolls selected.');
                }

                foreach ($request->roll_ids as $roll_id) {
                    $fabric = FabricReceiptDetail::find($roll_id);
                    if (!$fabric) continue;

                    $requested_qty = $request->input('roll_qty.' . $roll_id, $fabric->remaining_quantity);
                    $qty = min((float)$requested_qty, $fabric->remaining_quantity);

                    if ($qty <= 0) continue;

                    StockDisposalItem::create([
                        'stock_disposal_main_id' => $main->id,
                        'item_id' => $fabric->id,
                        'barcode' => $fabric->getRawOriginal('barcode'),
                        'quantity' => $qty
                    ]);

                    $fabric->remaining_quantity -= $qty;
                    $fabric->save();

                    FabricInventoryHistory::create([
                        'user_id' => auth()->id(),
                        'fabric_id' => $fabric->fabric_id,
                        'old_warehouse_id' => $fabric->master_fabric_warehouse_id,
                        'new_warehouse_id' => null,
                        'roll_id' => $fabric->id,
                        'old_quantity' => $fabric->remaining_quantity + $qty,
                        'new_quantity' => $fabric->remaining_quantity,
                        'type' => 'Disposal',
                        'remarks' => "Disposed " . ($qty == ($fabric->remaining_quantity + $qty) ? 'Complete Roll' : $qty . ' mtr') . " (No: {$disposal_no}). Reason: {$request->reason}. " . $request->remarks
                    ]);
                }
            } else {
                $items = json_decode($request->domestic_items, true);
                if (!$items || count($items) === 0) {
                    throw new \Exception('No domestic items added to list.');
                }

                foreach ($items as $item) {
                    $qty_to_dispose = $item['quantity'];
                    
                    $inventories = DomesticInventory::where('product_id', $item['product_id'])
                        ->where('size_set_id', $item['size_set_id'])
                        ->where('color_id', $item['color_id'])
                        ->whereHas('rack', function($q) use ($item) {
                            $q->where('storeroom_id', $item['warehouse_id']);
                        })
                        ->where('total_boxes', '>', 0)
                        ->get();

                    if ($inventories->sum('total_boxes') < $qty_to_dispose) {
                        throw new \Exception('Insufficient stock for one of the items.');
                    }

                    foreach ($inventories as $inventory) {
                        if ($qty_to_dispose <= 0) break;

                        $take = min($inventory->total_boxes, $qty_to_dispose);

                        // Save item record for each inventory record touched
                        StockDisposalItem::create([
                            'stock_disposal_main_id' => $main->id,
                            'item_id' => $inventory->id, // Store inventory ID
                            'barcode' => $inventory->barcode,
                            'quantity' => $take
                        ]);

                        $inventory->total_boxes -= $take;
                        $inventory->save();

                        DomesticInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'old_product_id' => $inventory->product_id,
                            'old_size_set_id' => $inventory->size_set_id,
                            'old_color_id' => $inventory->color_id,
                            'old_rack_id' => $inventory->rack_id,
                            'old_warehouse_id' => $inventory->rack ? $inventory->rack->storeroom_id : null,
                            'box_quantity' => $take,
                            'mrp' => $inventory->mrp,
                            'pieces_per_box' => $inventory->pieces_per_box,
                            'type' => 'Disposal',
                            'remarks' => "Disposed (No: {$disposal_no}). Reason: {$request->reason}. " . $request->remarks
                        ]);

                        $qty_to_dispose -= $take;
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock disposal processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function edit($id)
    {
        $main = StockDisposalMain::with(['items.fabricReceiptDetail.fabric', 'items.domesticInventory.product', 'items.domesticInventory.color', 'items.domesticInventory.sizeSet'])->findOrFail($id);
        $fabricWarehouses = \App\Models\MasterFabricWarehouse::where('status', 1)->get();
        $storerooms = \App\Models\Storeroom::where('status', 1)->get();
        $products = \App\Models\ProductionGoods::with('series')->get();
        
        return view('admin.stock_disposal.edit', compact('main', 'fabricWarehouses', 'storerooms', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $main = StockDisposalMain::with('items')->findOrFail($id);
            $disposal_no = $main->disposal_no;

            // 1. Restore old stock
            foreach ($main->items as $item) {
                if ($main->item_type === 'fabric') {
                    $fabric = FabricReceiptDetail::find($item->item_id);
                    if ($fabric) {
                        $fabric->remaining_quantity += $item->quantity;
                        $fabric->save();

                        FabricInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'fabric_id' => $fabric->fabric_id,
                            'roll_id' => $fabric->id,
                            'type' => 'Edit (Restored)',
                            'remarks' => "Restored due to Edit (No: {$disposal_no})"
                        ]);
                    }
                } else {
                    $inventory = DomesticInventory::find($item->item_id);
                    if ($inventory) {
                        $inventory->total_boxes += $item->quantity;
                        $inventory->save();

                        DomesticInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'domestic_inventory_id' => $inventory->id,
                            'type' => 'Edit (Restored)',
                            'remarks' => "Restored due to Edit (No: {$disposal_no})"
                        ]);
                    }
                }
            }

            // 2. Delete old items
            $main->items()->delete();

            // 3. Apply new items and deduct stock
            if ($main->item_type === 'fabric') {
                if (!$request->has('roll_ids')) {
                    throw new \Exception('No rolls selected.');
                }

                foreach ($request->roll_ids as $roll_id) {
                    $fabric = FabricReceiptDetail::find($roll_id);
                    if (!$fabric) continue;

                    $requested_qty = $request->input('roll_qty.' . $roll_id, $fabric->remaining_quantity);
                    $qty = min((float)$requested_qty, $fabric->remaining_quantity);

                    if ($qty <= 0) continue;

                    StockDisposalItem::create([
                        'stock_disposal_main_id' => $main->id,
                        'item_id' => $fabric->id,
                        'barcode' => $fabric->getRawOriginal('barcode'),
                        'quantity' => $qty
                    ]);

                    $fabric->remaining_quantity -= $qty;
                    $fabric->save();

                    FabricInventoryHistory::create([
                        'user_id' => auth()->id(),
                        'fabric_id' => $fabric->fabric_id,
                        'roll_id' => $fabric->id,
                        'type' => 'Disposal (Updated)',
                        'remarks' => "Updated Disposal (No: {$disposal_no}). " . ($qty == ($fabric->remaining_quantity + $qty) ? 'Complete Roll' : $qty . ' mtr') . ". Reason: {$request->reason}"
                    ]);
                }
            } else {
                $items = json_decode($request->domestic_items, true);
                if (!$items || count($items) === 0) {
                    throw new \Exception('No domestic items added to list.');
                }

                foreach ($items as $item) {
                    $qty_to_dispose = $item['quantity'];
                    
                    $inventories = DomesticInventory::where('product_id', $item['product_id'])
                        ->where('size_set_id', $item['size_set_id'])
                        ->where('color_id', $item['color_id'])
                        ->whereHas('rack', function($q) use ($item) {
                            $q->where('storeroom_id', $item['warehouse_id']);
                        })
                        ->where('total_boxes', '>', 0)
                        ->get();

                    if ($inventories->sum('total_boxes') < $qty_to_dispose) {
                        throw new \Exception('Insufficient stock for one of the items.');
                    }

                    foreach ($inventories as $inventory) {
                        if ($qty_to_dispose <= 0) break;

                        $take = min($inventory->total_boxes, $qty_to_dispose);

                        StockDisposalItem::create([
                            'stock_disposal_main_id' => $main->id,
                            'item_id' => $inventory->id,
                            'barcode' => $inventory->barcode,
                            'quantity' => $take
                        ]);

                        $inventory->total_boxes -= $take;
                        $inventory->save();

                        DomesticInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'domestic_inventory_id' => $inventory->id,
                            'type' => 'Disposal (Updated)',
                            'remarks' => "Updated Disposal (No: {$disposal_no}). Reason: {$request->reason}"
                        ]);

                        $qty_to_dispose -= $take;
                    }
                }
            }

            // 4. Update Main details
            $main->update([
                'reason' => $request->reason,
                'remarks' => $request->remarks,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Disposal updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $main = StockDisposalMain::with('items')->findOrFail($id);
            $disposal_no = $main->disposal_no;

            log_deletion('Stock Disposal', $id, [
                'main'  => $main->toArray(),
                'items' => $main->items ? $main->items->toArray() : []
            ]);

            foreach ($main->items as $item) {
                if ($main->item_type === 'fabric') {
                    $fabric = FabricReceiptDetail::find($item->item_id);
                    if ($fabric) {
                        $old_qty = $fabric->remaining_quantity;
                        $fabric->remaining_quantity += $item->quantity;
                        $fabric->save();

                        FabricInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'fabric_id' => $fabric->fabric_id,
                            'old_warehouse_id' => null,
                            'new_warehouse_id' => $fabric->master_fabric_warehouse_id,
                            'roll_id' => $fabric->id,
                            'old_quantity' => $old_qty,
                            'new_quantity' => $fabric->remaining_quantity,
                            'type' => 'Restoration',
                            'remarks' => "Restored from Deleted Disposal (No: {$disposal_no})"
                        ]);
                    }
                } else {
                    $inventory = DomesticInventory::find($item->item_id);
                    if ($inventory) {
                        $old_boxes = $inventory->total_boxes;
                        $inventory->total_boxes += $item->quantity;
                        $inventory->save();

                        DomesticInventoryHistory::create([
                            'user_id' => auth()->id(),
                            'domestic_inventory_id' => $inventory->id,
                            'old_total_boxes' => $old_boxes,
                            'new_total_boxes' => $inventory->total_boxes,
                            'type' => 'Restoration',
                            'remarks' => "Restored from Deleted Disposal (No: {$disposal_no})"
                        ]);
                    }
                }
            }

            $main->delete(); // Cascade delete items
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Disposal deleted and stock restored.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
