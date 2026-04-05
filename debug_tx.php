<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderStageTransaction;
use App\Models\OrderLot;

$order_id = 19;
$unit_id = 11;

$lots = OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();

echo "Lots for Order 19: " . implode(', ', $lots) . "\n";

$incoming = OrderStageTransaction::whereIn('lot_no', $lots)
    ->where('to_stage_id', 11)
    ->where('sub_stage_id_to', $unit_id)
    ->with('details')
    ->get();

echo "Incoming Transactions:\n";
foreach ($incoming as $tx) {
    echo "  TX ID: {$tx->id}, Lot: {$tx->lot_no}, Type: {$tx->type}, Status: {$tx->status}, Qty: {$tx->quantity}\n";
    foreach ($tx->details as $d) {
        echo "    - Size: {$d->size}, Qty: {$d->quantity}\n";
    }
}

$outgoing = OrderStageTransaction::whereIn('lot_no', $lots)
    ->where('from_stage_id', 11)
    ->where('sub_stage_id', $unit_id)
    ->with('details')
    ->get();

echo "\nOutgoing Transactions:\n";
foreach ($outgoing as $tx) {
    echo "  TX ID: {$tx->id}, Lot: {$tx->lot_no}, Type: {$tx->type}, Status: {$tx->status}, Qty: {$tx->quantity}\n";
}
