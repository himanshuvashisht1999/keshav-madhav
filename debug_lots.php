<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderStageTransaction;

echo "--- Printing Transactions for Lot 601 ---\n";
$txs = OrderPrintingStageTransaction::where('lot_no', '601')->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id}, ToUnit: {$tx->sub_stage_id_to}, Qty: {$tx->remaining_quantity}, Image: " . ($tx->image ?? 'NULL') . "\n";
}

echo "\n--- Stage Transactions for Lot 601 ---\n";
$txs = OrderStageTransaction::where('lot_no', '601')->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id}, ToUnit: {$tx->sub_stage_id_to}, Qty: {$tx->remaining_quantity}, Image: " . ($tx->image ?? 'NULL') . "\n";
}
