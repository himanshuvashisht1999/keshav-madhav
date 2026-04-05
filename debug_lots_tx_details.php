<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderStageTransaction;

$txs = OrderStageTransaction::where('lot_no', '601')->get();
foreach ($txs as $tx) {
    echo "ID: {$tx->id}, FromU: {$tx->sub_stage_id}, ToU: {$tx->sub_stage_id_to}, Qty: {$tx->remaining_quantity}, Image: " . ($tx->image ?? 'NULL') . "\n";
}
