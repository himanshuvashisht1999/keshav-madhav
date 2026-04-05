<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderStageTransactionDetail;
use App\Models\OrderPrintingStageTransaction;

echo "--- Stage Transactions for Lot 17900 ---\n";
$txs = \App\Models\OrderStageTransaction::where('lot_no', '17900')->with('details')->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id}, Qty: {$tx->quantity}, DetailCount: " . $tx->details->count() . "\n";
    foreach ($tx->details as $d) {
        echo "  - Size: {$d->size}, Qty: {$d->quantity}\n";
    }
}
