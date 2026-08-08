<?php
$lot = '671';
$stage = 1;
$std_inflow = \App\Models\OrderStageTransaction::where('to_stage_id', $stage)->where('lot_no', $lot)->get();
$prt_inflow = \App\Models\OrderPrintingStageTransaction::where('to_stage_id', $stage)->where('lot_no', $lot)->get();
echo "STD:\n"; echo json_encode($std_inflow); echo "\n";
echo "PRT:\n"; echo json_encode($prt_inflow); echo "\n";

$std_det = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transactions.id', '=', 'order_stage_transaction_details.order_stage_transaction_id')
                ->where('order_stage_transactions.to_stage_id', $stage)
                ->where('order_stage_transactions.lot_no', $lot)
                ->select('order_stage_transaction_details.size', 'order_stage_transaction_details.quantity')
                ->get();
$prt_det = \App\Models\OrderPrintingStageTransactionDetail::join('order_printing_stage_transactions', 'order_printing_stage_transactions.id', '=', 'order_printing_stage_transaction_details.order_printing_stage_transaction_id')
                ->where('order_printing_stage_transactions.to_stage_id', $stage)
                ->where('order_printing_stage_transactions.lot_no', $lot)
                ->select('order_printing_stage_transaction_details.size', 'order_printing_stage_transaction_details.quantity')
                ->get();
echo "STD DET:\n"; echo json_encode($std_det); echo "\n";
echo "PRT DET:\n"; echo json_encode($prt_det); echo "\n";
