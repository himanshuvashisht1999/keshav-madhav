<?php

$issues = [];

// Find all transactions where a lot was assigned to a stage with NULL unit, and it's still pending
$pendingNullUnitTxs = \App\Models\OrderStageTransaction::whereNull('sub_stage_id_to')
    ->where('remaining_quantity', '>', 0)
    ->get();

foreach ($pendingNullUnitTxs as $tx) {
    // Check if the lot has actually moved OUT of this stage (meaning work was completed, but this tx was skipped)
    $outgoingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('from_stage_id', $tx->to_stage_id)
        ->sum('quantity');

    if ($outgoingWork > 0) {
        $issues[] = [
            'lot_no' => $tx->lot_no,
            'stuck_stage_id' => $tx->to_stage_id,
            'stuck_remaining_quantity' => $tx->remaining_quantity,
            'reason' => 'Unit was NULL, so it was skipped during completion'
        ];
    }
}

// Also check Printing to Stitching specifically (in case they have a similar bug there)
$printingStitching = \App\Models\OrderPrintingToStichingTransaction::whereNull('sub_stage_id_to')
    ->where('remaining_quantity', '>', 0)
    ->get();

foreach ($printingStitching as $tx) {
    $outgoingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('from_stage_id', $tx->to_stage_id)
        ->sum('quantity');
        
    if ($outgoingWork > 0) {
        $issues[] = [
            'lot_no' => $tx->lot_no,
            'stuck_stage_id' => $tx->to_stage_id,
            'stuck_remaining_quantity' => $tx->remaining_quantity,
            'reason' => 'Unit was NULL (Printing->Stitching), skipped during completion'
        ];
    }
}

echo json_encode($issues);
