<?php

$issues = [];

// Get all incoming transactions that still have remaining quantity
$pendingTxs = \App\Models\OrderStageTransaction::where('remaining_quantity', '>', 0)->get();

foreach ($pendingTxs as $tx) {
    $outgoingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('from_stage_id', $tx->to_stage_id)
        ->sum('quantity');

    $incomingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('to_stage_id', $tx->to_stage_id)
        ->sum('quantity');
        
    if ($tx->to_stage_id == 4) {
        $incomingWork += \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $tx->lot_no)
            ->where('to_stage_id', 4)
            ->sum('quantity');
    }

    if ($outgoingWork > 0 && $outgoingWork >= $incomingWork) {
        $stageName = 'Stage ' . $tx->to_stage_id;
        $issues[$tx->lot_no] = $stageName;
    }
}

echo json_encode($issues, JSON_PRETTY_PRINT);
