<?php

namespace App\Services\Admin;

use App\Models\ProductionSlipDigitization;
use App\Models\PackingMain;
use App\Models\PackingCarton;
use App\Models\PackingBox;
use App\Models\PackingItem;
use App\Models\OrderMain;
use Illuminate\Support\Facades\DB;

class PackingService
{
    /**
     * Get pending packing slips (Stage 11)
     */
    public function getPendingSlips()
    {
        // Check if already packed?
        // Maybe we need a status in ProductionSlipDigitization or just check if it exists in PackingMain
        
        return ProductionSlipDigitization::where('from_stage_id', 11) // Packing Stage
            ->whereDoesntHave('packingMain') // Assuming we add relation to ProductionSlipDigitization
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getSlipDetails($id)
    {
        return ProductionSlipDigitization::findOrFail($id);
    }
    
    public function getOrderDetails($sku)
    {
        return OrderMain::where('sku', $sku)
            ->with(['OrderProductSets.product_set_details', 'OrderProductSets.colors'])
            ->first();
    }

    public function getOrCreatePackingMain($slip_id, $order_id)
    {
        $packing = PackingMain::where('slip_id', $slip_id)->first();
        if (!$packing) {
            $packing = PackingMain::create([
                'slip_id' => $slip_id,
                'order_main_id' => $order_id,
                'packing_date' => now(), 
                'status' => 0 // Draft
            ]);
        }
        return $packing;
    }

    public function getPackingMainWithStructure($slip_id)
    {
         return PackingMain::where('slip_id', $slip_id)
             ->with(['boxes' => function($q) {
                 $q->whereNull('packing_carton_id'); // Only unpacked boxes
             }, 'cartons.boxes', 'cartons.items'])
             ->first();
    }

    public function saveCarton($data)
    {
        DB::beginTransaction();
        try {

            $exists = $this->checkCartonNo($data['carton_no']); 
            if ($exists) {
                DB::rollBack();
                return [
                    'status' => 'exists',
                    'message' => 'Carton number already exists'
                ];
            }
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'rack_id' => $data['rack_id'] ?? null, // Save Rack ID
                'note' => $data['note'] ?? null,
                'status' => 1,
                // Dimensions etc
            ]);

            // If direct items provided (Legacy/Manual)
            if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
                foreach ($data['items'] as $item) {
                     PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            // If SETS are provided (New)
            if (isset($data['sets']) && is_array($data['sets'])) {
                foreach ($data['sets'] as $set_req) {
                    $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_req['set_id']);
                    if($set) {
                        $pack_qty = $set_req['quantity'];
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                        
                        foreach($set->product_set_details as $detail) {
                            // Calculate qty per set unit
                            // If total required is 100 for 100 sets, then 1 per set.
                            // If total required is 200 for 100 sets, then 2 per set.
                            $ratio = $detail->total_quantity / $set_total_qty; 
                            
                            $qty_to_pack = ceil($ratio * $pack_qty); // Use ceil to be safe or round? 
                            // Integer math might key here.
                            
                            if($qty_to_pack > 0) {
                                PackingItem::create([
                                    'packing_main_id' => $main->id,
                                    'packing_carton_id' => $carton->id,
                                    'size_id' => $detail->id, // Wait, size_id refers to ORDER_PRODUCT_SET_DETAIL_ID? OR Size Master ID?
                                    // In current PackingItem, what is 'size_id'?
                                    // In Controller saveBox, we used $item['size_id']. Frontend passed `item.id`.
                                    // item.id comes from `OrderProductSetDetail->id`.
                                    // So `size_id` in PackingItem actually stores `order_product_set_detail_id`.
                                    // Checking PackingItem model to be sure...
                                    // Assuming it stores Detail ID for traceability.
                                    'quantity' => $qty_to_pack
                                ]);
                            }
                        }
                    }
                }
            }
            
            // If existing boxes are being put into this carton
            if (isset($data['box_ids']) && is_array($data['box_ids'])) {
                PackingBox::whereIn('id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
                PackingItem::whereIn('packing_box_id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
            }

            DB::commit();
            return ['status' => 'success', 'carton' => $carton];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function saveBox($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $box = PackingBox::create([
                'packing_main_id' => $main->id,
                'box_no' => $data['box_no'],
                'box_type' => 'mixed' 
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                     PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_box_id' => $box->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            

            DB::commit();
            return ['status' => 'success', 'box' => $box];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizePacking($main_id)
    {
        // Simple status update for now
        PackingMain::where('id', $main_id)->update(['status' => 1]);
        $packing_data = PackingMain::where('id', $main_id)->first();
        $update = ProductionSlipDigitization::where('id',$packing_data->slip_id)->update([
            'status' => 1
        ]);
        return ['status' => 'success'];
    }

    public function getPackedQuantitiesForOrder($order_id)
    {
        return PackingItem::join('packing_mains', 'packing_items.packing_main_id', '=', 'packing_mains.id')
            ->where('packing_mains.order_main_id', $order_id)
            ->select('packing_items.size_id', DB::raw('SUM(packing_items.quantity) as total_packed'))
            ->groupBy('packing_items.size_id')
            ->pluck('total_packed', 'packing_items.size_id')
            ->toArray();
    }
    public function createAdHocSet($data)
    {
        DB::beginTransaction();
        try {
            // Check if Order exists
            $order = OrderMain::findOrFail($data['order_id']);

            // Create OrderProductSet
            // We need to fill required fields. Some might be null or dummy for ad-hoc sets.
            // Assuming 0 for price related fields if not critical for packing.
            $set = \App\Models\OrderProductSet::create([
                'order_main_id' => $order->id,
                'order_id' => $order->id, // Legacy field?
                'set_quantity' => $data['quantity'], // Total Sets requested
                'remark' => 'Created via Ad-Hoc Packing',
                'status' => 1,
                // Required fields based on Model fillable (assuming nullable in DB or defaults)
                // If strict mode is on, we might fail if we don't provide everything.
                // Assuming basic fields are enough for now based on context.
                'company_id' => $order->company_id ?? 1,
            ]);

            // Create Details
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty_per_set = $item['qty_per_set'];
                    $total_qty = $data['quantity'] * $qty_per_set;
                    
                    \App\Models\OrderProductSetDetail::create([
                        'order_products_set_id' => $set->id,
                        'size' => $item['size_name'], // String name (e.g., "22")
                        'order_product_id' => $item['product_id'] ?? null, // Link to original product if known
                        'total_quantity' => $total_qty,
                        'remaining_quantity' => $total_qty, // Initially all remaining
                        'color_id' => $item['color_id'] ?? null
                    ]);
                }
            }

            DB::commit();
            return ['status' => 'success', 'set' => $set];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function deleteCarton($carton_id)
    {
        DB::beginTransaction();
        try {
            $carton = PackingCarton::findOrFail($carton_id);
            
            // 1. Release Boxes (Set carton_id to null)
            PackingBox::where('packing_carton_id', $carton->id)->update(['packing_carton_id' => null]);
            
            // 2. Delete Items directly in Carton (where box_id is null)
            // Wait, items inside boxes also have packing_carton_id set?
            // In saveCarton: PackingItem::whereIn('packing_box_id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
            // So if we just delete items with carton_id, we delete items inside boxes too!
            // We must NOT delete items that belong to a box.
            
            // Delete items that are DIRECTLY in the carton (no box)
            PackingItem::where('packing_carton_id', $carton->id)
                       ->whereNull('packing_box_id')
                       ->delete();
                       
            // For items IN boxes, we should set their packing_carton_id to null too?
            // Yes, so they stay with the box.
            PackingItem::where('packing_carton_id', $carton->id)
                       ->whereNotNull('packing_box_id')
                       ->update(['packing_carton_id' => null]);

            // 3. Delete the Carton
            $carton->delete();

            DB::commit();
            return ['status' => 'success'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function checkCartonNo($carton_no)
    {
        $exists = PackingCarton::where('carton_no', $carton_no)->exists();  
        return $exists; 
    }

}
