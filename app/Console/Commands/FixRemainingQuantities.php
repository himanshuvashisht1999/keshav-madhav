<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRemainingQuantities extends Command
{
    protected $signature = 'fix:remaining-quantities';
    protected $description = 'Fix remaining_quantity for transactions that have no forwarded pieces (fixes the Mark as Final delete bug)';

    public function handle()
    {
        $this->info("Scanning for stuck OrderStageTransactions...");

        // Find all transactions where remaining_quantity != quantity
        $transactions = \App\Models\OrderStageTransaction::whereColumn('remaining_quantity', '!=', 'quantity')->get();

        $fixedCount = 0;

        foreach ($transactions as $tx) {
            // Check if there are ANY child transactions from this stage and unit
            $hasChildStage = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
                ->where('from_stage_id', $tx->to_stage_id)
                ->where('sub_stage_id', $tx->sub_stage_id_to)
                ->exists();

            // Check if there are any outflows (packing, etc)
            $hasOutflow = \App\Models\ProductionOutflowInventory::where('lot_no', $tx->lot_no)
                ->where('responsible_unit_id', $tx->sub_stage_id_to)
                ->exists();

            // Check if there are corporate packing items for this order and unit
            $hasPacking = DB::table('packing_items as pi')
                ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
                ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
                ->where('pm.order_main_id', function($q) use ($tx) {
                    $q->select('order_main_id')->from('order_lots')->where('lot_no', $tx->lot_no)->limit(1);
                })
                ->where('psd.stage_master_unit_id', $tx->sub_stage_id_to)
                ->exists();

            if (!$hasChildStage && !$hasOutflow && !$hasPacking) {
                // This is a leaf transaction but its remaining quantity is reduced!
                // This means it was affected by the "Mark as Final" bug.
                $this->info("Fixing Lot: {$tx->lot_no} | Stage: {$tx->to_stage_id} | Unit: {$tx->sub_stage_id_to}");
                $tx->remaining_quantity = $tx->quantity;
                $tx->is_closed_for_unit = 0;
                $tx->status = 1;
                $tx->save();
                $fixedCount++;
            }
        }

        $this->info("Fixed {$fixedCount} stuck transactions!");
        return 0;
    }
}
