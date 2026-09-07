<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRemainingQuantities extends Command
{
    protected $signature = 'fix:remaining-quantities {--lot= : Specific lot to fix}';
    protected $description = 'Fix remaining_quantity and status for transactions across all stages based on real inflow vs outflow';

    public function handle()
    {
        $lotFilter = $this->option('lot');
        $this->info("Scanning transactions for remaining_quantity synchronization" . ($lotFilter ? " for Lot {$lotFilter}..." : "..."));

        $models = [
            'stage' => \App\Models\OrderStageTransaction::class,
            'printing' => \App\Models\OrderPrintingStageTransaction::class,
            'printing_stitching' => \App\Models\OrderPrintingToStichingTransaction::class,
            'godam' => \App\Models\OrderGodamStageTransaction::class,
        ];

        // Collect all distinct lots
        $lotsQuery = \App\Models\OrderStageTransaction::distinct();
        if ($lotFilter) {
            $lotsQuery->where('lot_no', $lotFilter);
        }
        $lots = $lotsQuery->pluck('lot_no')
            ->concat(\App\Models\OrderPrintingStageTransaction::when($lotFilter, fn($q) => $q->where('lot_no', $lotFilter))->distinct()->pluck('lot_no'))
            ->concat(\App\Models\OrderPrintingToStichingTransaction::when($lotFilter, fn($q) => $q->where('lot_no', $lotFilter))->distinct()->pluck('lot_no'))
            ->concat(\App\Models\OrderGodamStageTransaction::when($lotFilter, fn($q) => $q->where('lot_no', $lotFilter))->distinct()->pluck('lot_no'))
            ->filter()->unique()->values();

        $fixedCount = 0;

        foreach ($lots as $lotNo) {
            // Group incoming transactions by (to_stage_id, sub_stage_id_to)
            $incomingTxs = collect();
            foreach ($models as $type => $model) {
                $txs = $model::where('lot_no', $lotNo)->whereNotNull('sub_stage_id_to')->orderBy('id', 'asc')->get();
                foreach ($txs as $t) {
                    $incomingTxs->push($t);
                }
            }

            $grouped = $incomingTxs->groupBy(function($item) {
                return $item->to_stage_id . '_' . $item->sub_stage_id_to;
            });

            foreach ($grouped as $key => $txGroup) {
                list($stageId, $unitId) = explode('_', $key);

                // Calculate total inflow to this stage & unit
                $inflow = $txGroup->sum('quantity');

                // Calculate total outflow from this stage & unit
                $outflow = 0;
                $outflow += \App\Models\OrderStageTransaction::where('lot_no', $lotNo)->where('from_stage_id', $stageId)->where('sub_stage_id', $unitId)->sum('quantity');
                $outflow += \App\Models\OrderPrintingStageTransaction::where('lot_no', $lotNo)->where('from_stage_id', $stageId)->where('sub_stage_id', $unitId)->sum('quantity');
                $outflow += \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $lotNo)->where('from_stage_id', $stageId)->where('sub_stage_id', $unitId)->sum('quantity');
                $outflow += \App\Models\OrderGodamStageTransaction::where('lot_no', $lotNo)->where('from_stage_id', $stageId)->where('sub_stage_id', $unitId)->sum('quantity');

                // If stage is packing (11), add packed items
                if ($stageId == 11) {
                    $packedQty = (int) \DB::table('packing_items as pi')
                        ->join('packing_cartons as pc', 'pi.packing_carton_id', '=', 'pc.id')
                        ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
                        ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
                        ->where('pi.lot_no', $lotNo)
                        ->where('psd.stage_master_unit_id', $unitId)
                        ->where('pc.status', 1)
                        ->sum('pi.quantity');
                    $outflow += $packedQty;
                }

                // Add production outflows (debit, dead, sampling, defect)
                $outflowInv = (int) \App\Models\ProductionOutflowInventory::join('production_slip_digitization as psd', 'production_outflow_inventories.slip_id', '=', 'psd.id')
                    ->where('production_outflow_inventories.lot_no', $lotNo)
                    ->where('psd.stage_master_unit_id', $unitId)
                    ->sum('production_outflow_inventories.quantity');
                $outflow += $outflowInv;

                // Total remaining to allocate across transactions in this group
                $totalRemaining = max(0, $inflow - $outflow);

                // Allocate remaining quantity from oldest to newest transaction (or vice-versa)
                // We consume from the first transactions first:
                $usedOutflow = $outflow;

                foreach ($txGroup as $tx) {
                    $origRem = $tx->remaining_quantity;
                    $hasClosedCol = \Illuminate\Support\Facades\Schema::hasColumn($tx->getTable(), 'is_closed_for_unit');
                    $origClosed = $hasClosedCol ? $tx->is_closed_for_unit : 0;

                    if ($usedOutflow >= $tx->quantity) {
                        $newRem = 0;
                        $usedOutflow -= $tx->quantity;
                    } else {
                        $newRem = $tx->quantity - $usedOutflow;
                        $usedOutflow = 0;
                    }

                    $newClosed = ($newRem <= 0) ? 1 : 0;
                    $newStatus = ($newRem <= 0) ? 2 : 1;

                    if ($origRem != $newRem || ($hasClosedCol && $origClosed != $newClosed)) {
                        $tx->remaining_quantity = $newRem;
                        if ($hasClosedCol) {
                            $tx->is_closed_for_unit = $newClosed;
                        }
                        $tx->status = $newStatus;
                        if ($newRem > 0 && \Illuminate\Support\Facades\Schema::hasColumn($tx->getTable(), 'complete_date')) {
                            $tx->complete_date = null;
                        }
                        $tx->save();
                        $fixedCount++;
                        $this->line("Fixed Lot {$lotNo} (Stage {$stageId}, Unit {$unitId}, Tx #{$tx->id}): Rem {$origRem} -> {$newRem}");
                    }
                }
            }
        }

        $this->info("Successfully synchronized {$fixedCount} transactions across all stages!");
        return 0;
    }
}

