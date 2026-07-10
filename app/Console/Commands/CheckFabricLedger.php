<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FabricRollAssigning;
use App\Models\FabricReceiptDetail;

class CheckFabricLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fabric:check-ledger {--fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix fabric ledger mismatches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $assignments = FabricRollAssigning::with(['stageMasterUnit', 'fabricReceiptDetail'])->get();
        
        $mismatches = 0;
        $fixed = 0;

        foreach ($assignments as $assignment) {
            if (!$assignment->stageMasterUnit || !$assignment->fabricReceiptDetail) {
                continue;
            }

            $cuttingWarehouseId = $assignment->stageMasterUnit->master_fabric_warehouse_id;
            $rollWarehouseId = $assignment->fabricReceiptDetail->master_fabric_warehouse_id;

            if ($cuttingWarehouseId != $rollWarehouseId) {
                $mismatches++;
                
                $this->info("Mismatch found: Assignment ID {$assignment->id}, Lot No: {$assignment->lot_no}");
                $this->info("  -> Cutting Master ({$assignment->stageMasterUnit->name}) Warehouse: {$cuttingWarehouseId}");
                $this->info("  -> Selected Roll ({$assignment->fabricReceiptDetail->roll_number}) Warehouse: {$rollWarehouseId}");
                
                // Check if a roll with the SAME roll_number and SAME fabric_id exists in the correct warehouse
                $alternativeRolls = FabricReceiptDetail::where('roll_number', $assignment->roll_no ?? $assignment->fabricReceiptDetail->roll_number)
                    ->where('master_fabric_warehouse_id', $cuttingWarehouseId)
                    ->where('fabric_id', $assignment->fabricReceiptDetail->fabric_id)
                    ->get();
                
                if ($alternativeRolls->isNotEmpty()) {
                    $this->info("     [GOOD NEWS] Found matching roll_number in the correct warehouse!");
                    foreach ($alternativeRolls as $alt) {
                        $this->info("       -> Alt Roll ID: {$alt->id}, Fabric ID: {$alt->fabric_id}");
                    }

                    if ($this->option('fix')) {
                        // Option A: Just switch the assignment to the correct FabricReceiptDetail ID
                        $assignment->fabric_receipt_detail_id = $alternativeRolls->first()->id;
                        $assignment->save();
                        
                        // We also need to fix the ledger quantities!
                        // The wrong roll was deducted, so we add it back
                        $assignment->fabricReceiptDetail->remaining_quantity += $assignment->meter;
                        $assignment->fabricReceiptDetail->save();

                        // The correct roll should be deducted
                        $correctRoll = $alternativeRolls->first();
                        $correctRoll->remaining_quantity -= $assignment->meter;
                        $correctRoll->save();

                        $this->info("     [FIXED] Swapped fabric_receipt_detail_id to {$correctRoll->id} and adjusted quantities!");
                        $fixed++;
                    }
                } else {
                    $this->warn("     [WARNING] No alternative roll with roll_number '" . ($assignment->roll_no ?? $assignment->fabricReceiptDetail->roll_number) . "' found in Warehouse {$cuttingWarehouseId}!");
                    
                    if ($this->option('fix')) {
                        // Option B: Just move the roll to the correct warehouse so the ledger balances out
                        $assignment->fabricReceiptDetail->master_fabric_warehouse_id = $cuttingWarehouseId;
                        $assignment->fabricReceiptDetail->save();
                        $this->info("     [FIXED] Roll moved to Warehouse {$cuttingWarehouseId}");
                        $fixed++;
                    }
                }
            }
        }

        $this->info("Total mismatches found: {$mismatches}");
        if ($this->option('fix')) {
            $this->info("Total fixed: {$fixed}");
        }
        
        return Command::SUCCESS;
    }
}
