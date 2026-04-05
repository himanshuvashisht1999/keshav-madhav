<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingToStichingTransaction;

echo "--- OrderStageTransaction ---\n";
foreach (OrderStageTransaction::where('lot_no', '17900')->get() as $tx) {
    echo "ID: {$tx->id}, ToU: {$tx->sub_stage_id_to}, Qty: {$tx->quantity}\n";
}
echo "--- OrderPrintingStageTransaction ---\n";
foreach (OrderPrintingStageTransaction::where('lot_no', '17900')->get() as $tx) {
    echo "ID: {$tx->id}, ToU: {$tx->sub_stage_id_to}, Qty: {$tx->quantity}\n";
}
echo "--- OrderPrintingToStichingTransaction ---\n";
foreach (OrderPrintingToStichingTransaction::where('lot_no', '17900')->get() as $tx) {
    echo "ID: {$tx->id}, ToU: {$tx->sub_stage_id_to}, Qty: {$tx->quantity}\n";
}
