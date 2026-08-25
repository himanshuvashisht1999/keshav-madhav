<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;
use App\Models\Storeroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoAssignStockController extends Controller
{
    public function autoAssign(Request $request)
    {
        try {
            DB::beginTransaction();

            // 1. Get ADVANCE SAMPLE storeroom racks
            $sampleStoreroom = Storeroom::where('name', 'ADVANCE SAMPLE')->with('racks')->first();
            if (!$sampleStoreroom || $sampleStoreroom->racks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ADVANCE SAMPLE storeroom or racks not found.'
                ]);
            }
            $sampleRackIds = $sampleStoreroom->racks->pluck('id')->toArray();

            // 2. Find pending agent order items mapped to ADVANCE SAMPLE
            $pendingOrderItems = AgentOrderItem::whereHas('order', function ($query) {
                $query->where('status', 'pending');
            })
            ->whereIn('rack_id', $sampleRackIds)
            ->get();

            if ($pendingOrderItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending order items found in ADVANCE SAMPLE.',
                    'reassigned_count' => 0
                ]);
            }

            // Group order items by product, size_set, and color to fetch available stock efficiently
            $reassignedCount = 0;
            $itemsProcessed = 0;
            
            // To prevent double allocating the same stock in loop, we'll keep track of allocations
            // per rack, product, size_set, color.
            $stockCache = [];

            foreach ($pendingOrderItems as $item) {
                $itemsProcessed++;
                $neededQty = $item->box_qty;
                
                $cacheKey = $item->product_id . '_' . $item->size_set_id . '_' . $item->color_id;

                if (!isset($stockCache[$cacheKey])) {
                    // Fetch available stock in non-sample racks
                    // Calculate (Total Boxes - Already Allocated in Pending Orders)
                    
                    $availableInventory = DomesticInventory::select(
                            'domestic_inventories.rack_id', 
                            'storerooms.order_priority',
                            DB::raw('SUM(domestic_inventories.total_boxes) as actual_total_boxes')
                        )
                        ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                        ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                        ->where('domestic_inventories.product_id', $item->product_id)
                        ->where('domestic_inventories.size_set_id', $item->size_set_id)
                        ->where('domestic_inventories.color_id', $item->color_id)
                        ->whereNotIn('domestic_inventories.rack_id', $sampleRackIds)
                        ->where('domestic_inventories.quantity', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('storerooms.id')
                              ->orWhere('storerooms.order_taken', '=', 'Yes');
                        })
                        ->groupBy('domestic_inventories.rack_id', 'storerooms.order_priority')
                        ->get();

                    $stockRecords = [];
                    foreach ($availableInventory as $inv) {
                        // Find allocated pending boxes for this rack, product, sizeset, color
                        $allocated = AgentOrderItem::whereHas('order', function($q) {
                                $q->where('status', 'pending');
                            })
                            ->where('rack_id', $inv->rack_id)
                            ->where('product_id', $item->product_id)
                            ->where('size_set_id', $item->size_set_id)
                            ->where('color_id', $item->color_id)
                            ->sum('box_qty');
                        
                        $available = $inv->actual_total_boxes - $allocated;
                        if ($available > 0) {
                            $stockRecords[] = [
                                'rack_id' => $inv->rack_id,
                                'available' => $available,
                                'order_priority' => is_numeric($inv->order_priority) ? (int)$inv->order_priority : 9999
                            ];
                        }
                    }
                    
                    $stockCache[$cacheKey] = collect($stockRecords)->sort(function ($a, $b) {
                        if ($a['order_priority'] === $b['order_priority']) {
                            return $b['available'] <=> $a['available'];
                        }
                        return $a['order_priority'] <=> $b['order_priority'];
                    })->values();
                }

                $availableStocks = &$stockCache[$cacheKey];

                if ($availableStocks->isEmpty()) {
                    continue; // No stock available to reassign this item
                }

                // Try to fulfill $neededQty from availableStocks
                $remainingQty = $neededQty;
                
                $originalItemUpdated = false;
                $originalPieces = $item->quantity;
                $originalBoxes = $item->box_qty;
                $piecesPerBox = $originalBoxes > 0 ? ($originalPieces / $originalBoxes) : 0;

                foreach ($availableStocks as $index => &$stock) {
                    if ($stock['available'] <= 0) continue;
                    if ($remainingQty <= 0) break;

                    $take = min($remainingQty, $stock['available']);
                    
                    if (!$originalItemUpdated) {
                        // Update the original item for the first part
                        $item->rack_id = $stock['rack_id'];
                        $item->box_qty = $take;
                        $item->quantity = $take * $piecesPerBox;
                        $item->save();
                        
                        $originalItemUpdated = true;
                    } else {
                        // Clone the item for the remaining part
                        $newItem = $item->replicate();
                        $newItem->rack_id = $stock['rack_id'];
                        $newItem->box_qty = $take;
                        $newItem->quantity = $take * $piecesPerBox;
                        $newItem->save();
                    }

                    $stock['available'] -= $take;
                    $remainingQty -= $take;
                    $reassignedCount++;
                }

                // If we couldn't fulfill everything, we need to create a record for the remaining in ADVANCE SAMPLE
                if ($remainingQty > 0 && $originalItemUpdated) {
                     $newItem = $item->replicate();
                     $newItem->rack_id = $item->getOriginal('rack_id'); // Original advance sample rack
                     $newItem->box_qty = $remainingQty;
                     $newItem->quantity = $remainingQty * $piecesPerBox;
                     $newItem->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully reassigned $reassignedCount order item splits from ADVANCE SAMPLE.",
                'reassigned_count' => $reassignedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto Assign Stock Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning stock: ' . $e->getMessage()
            ], 500);
        }
    }
}
