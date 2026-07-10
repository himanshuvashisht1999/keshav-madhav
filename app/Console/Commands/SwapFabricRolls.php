<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FabricRollAssigning;
use App\Models\FabricReceiptDetail;

class SwapFabricRolls extends Command
{
    protected $signature = 'fabric:swap-rolls';
    protected $description = 'Swap fabric roll assignments to correct warehouse rolls and adjust quantities';

    public function handle()
    {
        $assignments = FabricRollAssigning::with(['stageMasterUnit', 'fabricReceiptDetail'])->get();
        
        $mismatches = 0;
        $fixed = 0;
        $unfixable = 0;

        foreach ($assignments as $assignment) {
            if (!$assignment->stageMasterUnit || !$assignment->fabricReceiptDetail) {
                continue;
            }

            $cuttingWarehouseId = $assignment->stageMasterUnit->master_fabric_warehouse_id;
            $rollWarehouseId = $assignment->fabricReceiptDetail->master_fabric_warehouse_id;

            if ($cuttingWarehouseId != $rollWarehouseId) {
                $mismatches++;
                
                $this->info("Mismatch: Assignment {$assignment->id}, Lot {$assignment->lot_no}, Meter {$assignment->meter}");
                $this->info("  -> Current Roll: {$assignment->fabricReceiptDetail->id} in WH {$rollWarehouseId} (Cutting WH is {$cuttingWarehouseId})");
                
                // Find alternative roll in correct warehouse with SAME fabric_id and ENOUGH remaining_quantity
                $requiredMeter = $assignment->meter;
                $fabricId = $assignment->fabricReceiptDetail->fabric_id;

                $alternativeRoll = FabricReceiptDetail::where('fabric_id', $fabricId)
                    ->where('master_fabric_warehouse_id', $cuttingWarehouseId)
                    ->where('remaining_quantity', '>=', $requiredMeter)
                    ->first();

                if ($alternativeRoll) {
                    // We found a replacement!
                    $this->info("  -> FOUND Replacement Roll ID: {$alternativeRoll->id} in WH {$cuttingWarehouseId}");
                    
                    // 1. Refund old roll
                    $oldRoll = $assignment->fabricReceiptDetail;
                    $oldRoll->remaining_quantity += $requiredMeter;
                    $oldRoll->save();

                    // 2. Deduct new roll
                    $alternativeRoll->remaining_quantity -= $requiredMeter;
                    $alternativeRoll->save();

                    // 3. Update assignment
                    $assignment->fabric_receipt_detail_id = $alternativeRoll->id;
                    $assignment->save();

                    $this->info("  [FIXED] Successfully swapped and adjusted quantities!");
                    $fixed++;
                } else {
                    $this->error("  [ERROR] No roll found in WH {$cuttingWarehouseId} for Fabric {$fabricId} with >= {$requiredMeter} meters!");
                    $unfixable++;
                }
            }
        }

        $this->info("\nSummary:");
        $this->info("Total mismatches: {$mismatches}");
        $this->info("Fixed: {$fixed}");
        $this->info("Unfixable (Not enough stock in correct warehouse): {$unfixable}");
        
        return Command::SUCCESS;
    }
}
