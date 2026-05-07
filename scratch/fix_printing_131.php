<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderLotStageTiming;
use App\Models\OrderPrintingStageTransaction;

$t = OrderLotStageTiming::where('lot_no', '131')->where('master_stage_id', 1)->first();
if ($t) {
    $t->complete_date = '2026-05-06 14:48:30';
    $t->status = 2;
    $t->save();
    echo "Updated OrderLotStageTiming for lot 131 stage 1\n";
} else {
    echo "OrderLotStageTiming not found for lot 131 stage 1\n";
}

$tx = OrderPrintingStageTransaction::where('lot_no', '131')->where('to_stage_id', 1)->first();
if ($tx) {
    $tx->complete_date = '2026-05-06 14:48:30';
    $tx->is_closed_for_unit = 1;
    $tx->status = 2;
    $tx->save();
    echo "Updated OrderPrintingStageTransaction for lot 131\n";
} else {
    echo "OrderPrintingStageTransaction not found for lot 131\n";
}
