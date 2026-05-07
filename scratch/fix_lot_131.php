<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderCuttingStage;
use App\Models\OrderLotStageTiming;

$c = OrderCuttingStage::find(131);
if ($c) {
    $c->lot_no = '131';
    $c->save();
    echo "Updated OrderCuttingStage 131 lot_no to 131\n";
    
    OrderLotStageTiming::updateOrCreate(
        ['lot_no' => '131', 'master_stage_id' => 3],
        ['complete_date' => $c->complete_date, 'status' => 2]
    );
    echo "Updated OrderLotStageTiming for lot 131 stage 3 with complete_date: " . $c->complete_date . "\n";
} else {
    echo "OrderCuttingStage 131 not found\n";
}
