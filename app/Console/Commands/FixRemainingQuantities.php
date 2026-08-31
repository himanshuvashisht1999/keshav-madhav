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
        $this->info("Scanning for Stage 11 (Packing) transactions with stuck remaining_quantity...");

        $txs = \App\Models\OrderStageTransaction::where('to_stage_id', 11)->get();
        $fixedCount = 0;

        foreach ($txs as $tx) {
            // 1. Forward transfers from this stage & unit
            $forwardQty = (int) \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
                ->where('from_stage_id', 11)
                ->where('sub_stage_id', $tx->sub_stage_id_to)
                ->sum('quantity');

            // 2. Packing items packed for this lot at this unit
            $packedQty = (int) \DB::table('packing_items as pi')
                ->join('packing_cartons as pc', 'pi.packing_carton_id', '=', 'pc.id')
                ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
                ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
                ->where('pi.lot_no', $tx->lot_no)
                ->where('psd.stage_master_unit_id', $tx->sub_stage_id_to)
                ->where('pc.status', 1)
                ->sum('pi.quantity');

            // 3. Outflows for this lot at this unit
            $outflowQty = (int) \App\Models\ProductionOutflowInventory::join('production_slip_digitization as psd', 'production_outflow_inventories.slip_id', '=', 'psd.id')
                ->where('production_outflow_inventories.lot_no', $tx->lot_no)
                ->where('psd.stage_master_unit_id', $tx->sub_stage_id_to)
                ->sum('production_outflow_inventories.quantity');

            $expected = max(0, (int)$tx->quantity - $forwardQty - $packedQty - $outflowQty);

            if ($tx->remaining_quantity != $expected) {
                $this->line("Fixing Lot: {$tx->lot_no} (Tx #{$tx->id}, Unit {$tx->sub_stage_id_to}): {$tx->remaining_quantity} -> {$expected}");
                $tx->remaining_quantity = $expected;
                $tx->save();
                $fixedCount++;
            }
        }

        $this->info("Successfully fixed {$fixedCount} transactions!");
        return 0;
    }
}
