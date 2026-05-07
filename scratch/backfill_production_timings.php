<?php

use App\Models\OrderLot;
use App\Models\OrderLotStageTiming;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\MasterProductStage;
use Illuminate\Support\Facades\DB;

function backfillAllProductionData() {
    $lots = OrderLot::all();
    $stages = MasterProductStage::where('status', 1)->get()->keyBy('id');
    
    echo "Processing " . $lots->count() . " lots and their transactions...\n";

    foreach ($lots as $lot) {
        // echo "Processing Lot: " . $lot->lot_no . "\n";
        
        // 1. CUTTING (Stage 3)
        $cuttingDays = isset($stages[3]) ? $stages[3]->lot_time_in_days : 2;
        $cuttingStart = $lot->production_datetime ?: $lot->created_at;
        $cuttingEnd = date('Y-m-d H:i:s', strtotime($cuttingStart . " + $cuttingDays days"));
        $isCuttingDone = ($lot->is_printing || $lot->is_stitching);
        
        // Update order_lot_stage_timings
        $timing3 = OrderLotStageTiming::updateOrCreate(
            ['lot_no' => $lot->lot_no, 'master_stage_id' => 3],
            [
                'order_lot_id' => $lot->id,
                'days_allocated' => $cuttingDays,
                'start_date' => $cuttingStart,
                'end_date' => $cuttingEnd,
                'complete_date' => $isCuttingDone ? $cuttingEnd : null,
                'status' => $isCuttingDone ? 1 : 0
            ]
        );

        // Update order_cutting_stage table
        DB::table('order_cutting_stage')->where('lot_no', $lot->lot_no)->update([
            'start_date' => $cuttingStart,
            'end_date' => $cuttingEnd,
            'complete_date' => $isCuttingDone ? $cuttingEnd : null
        ]);

        // 2. PRINTING (Stage 1)
        $printingTxs = OrderPrintingStageTransaction::where('lot_no', $lot->lot_no)->get();
        if ($printingTxs->isNotEmpty()) {
            $printingDays = isset($stages[1]) ? $stages[1]->lot_time_in_days : 10;
            $minStart = $printingTxs->min('production_datetime');
            $expectedEnd = date('Y-m-d H:i:s', strtotime($minStart . " + $printingDays days"));
            $maxComplete = $printingTxs->max('production_datetime');

            OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lot->lot_no, 'master_stage_id' => 1],
                [
                    'order_lot_id' => $lot->id,
                    'days_allocated' => $printingDays,
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $lot->is_stitching ? $maxComplete : null,
                    'status' => $lot->is_stitching ? 1 : 0
                ]
            );

            // Update individual printing transactions
            foreach ($printingTxs as $ptx) {
                $ptx->update([
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $ptx->production_datetime
                ]);
            }
        }

        // 3. STITCHING & OTHER (Stage 4+)
        $allStageTxs = OrderStageTransaction::where('lot_no', $lot->lot_no)->get();
        $printToStitchTxs = OrderPrintingToStichingTransaction::where('lot_no', $lot->lot_no)->get();
        
        // Group by to_stage_id
        $grouped = $allStageTxs->groupBy('to_stage_id');
        
        // Handle printing-to-stitching specifically (Stage 4)
        if ($printToStitchTxs->isNotEmpty()) {
            $stitchingDays = isset($stages[4]) ? $stages[4]->lot_time_in_days : 15;
            $minStart = $printToStitchTxs->min('production_datetime');
            $expectedEnd = date('Y-m-d H:i:s', strtotime($minStart . " + $stitchingDays days"));
            
            // Check if lot moved BEYOND stitching
            $movedBeyond = OrderStageTransaction::where('lot_no', $lot->lot_no)->where('from_stage_id', 4)->exists();

            OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lot->lot_no, 'master_stage_id' => 4],
                [
                    'order_lot_id' => $lot->id,
                    'days_allocated' => $stitchingDays,
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $movedBeyond ? $printToStitchTxs->max('production_datetime') : null,
                    'status' => $movedBeyond ? 1 : 0
                ]
            );

            foreach ($printToStitchTxs as $pstx) {
                $pstx->update([
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $pstx->production_datetime
                ]);
            }
        }

        foreach ($grouped as $stageId => $txs) {
            $stageDays = isset($stages[$stageId]) ? $stages[$stageId]->lot_time_in_days : 2;
            $minStart = $txs->min('production_datetime');
            $expectedEnd = date('Y-m-d H:i:s', strtotime($minStart . " + $stageDays days"));
            
            $movedBeyond = OrderStageTransaction::where('lot_no', $lot->lot_no)->where('from_stage_id', $stageId)->exists();
            $maxComplete = $txs->max('production_datetime');

            OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lot->lot_no, 'master_stage_id' => $stageId],
                [
                    'order_lot_id' => $lot->id,
                    'days_allocated' => $stageDays,
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $movedBeyond ? $maxComplete : null,
                    'status' => $movedBeyond ? 1 : 0
                ]
            );

            foreach ($txs as $tx) {
                $tx->update([
                    'start_date' => $minStart,
                    'end_date' => $expectedEnd,
                    'complete_date' => $tx->production_datetime
                ]);
            }
        }
    }
    
    echo "Full backfill completed successfully!\n";
}

backfillAllProductionData();
