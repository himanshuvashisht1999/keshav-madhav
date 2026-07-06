<?php

$issues = [];

// Get all incoming transactions that still have remaining quantity
$pendingTxs = \App\Models\OrderStageTransaction::where('remaining_quantity', '>', 0)
    ->get();

foreach ($pendingTxs as $tx) {
    // How much went OUT of this stage for this lot?
    $outgoingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('from_stage_id', $tx->to_stage_id)
        ->sum('quantity');

    // How much came IN to this stage for this lot? (From all sources)
    $incomingWork = \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)
        ->where('to_stage_id', $tx->to_stage_id)
        ->sum('quantity');
        
    // Add Printing to Stitching if applicable
    if ($tx->to_stage_id == 4) {
        $incomingWork += \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $tx->lot_no)
            ->where('to_stage_id', 4)
            ->sum('quantity');
    }

    // If the outgoing work is EQUAL TO OR GREATER THAN the total incoming work,
    // this stage is COMPLETELY FINISHED for this lot.
    // Yet this transaction still has remaining_quantity > 0! That's a stuck transaction!
    if ($outgoingWork > 0 && $outgoingWork >= $incomingWork) {
        $issues[] = [
            'lot_no' => $tx->lot_no,
            'stuck_in_stage_id' => $tx->to_stage_id,
            'stuck_transaction_id' => $tx->id,
            'stuck_quantity' => $tx->remaining_quantity,
            'reason' => 'Stage has completed all items, but this assignment is still marked as pending'
        ];
    }
}

echo json_encode($issues, JSON_PRETTY_PRINT);
