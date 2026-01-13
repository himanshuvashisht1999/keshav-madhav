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
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'note' => $data['note'] ?? null,
                // Dimensions etc
            ]);

            // If direct items provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                     PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }
            
            // If existing boxes are being put into this carton
            if (isset($data['box_ids']) && is_array($data['box_ids'])) {
                PackingBox::whereIn('id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
                // Also update items? No, items link to box. But they also have carton_id? 
                // schema says item has `packing_carton_id`. 
                // Ideally, if item is in box, and box is in carton, item should link to both for query ease?
                // Or just link to box, and box links to carton.
                // My migration creates both FKs. I'll update them for consistency if I want, or leave null.
                // Let's update them so queries on "Items in Carton X" are easier.
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
        return ['status' => 'success'];
    }
}
