<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;
use App\Models\FabricRollAssigning;

$rolls = FabricReceiptDetail::all();
$fixed = 0;
foreach ($rolls as $roll) {
    $assigned = FabricRollAssigning::where('fabric_receipt_detail_id', $roll->id)->sum('meter');
    $expected = $roll->meter - $assigned;
    if (abs((float)$roll->remaining_quantity - (float)$expected) > 0.01) {
        $roll->remaining_quantity = $expected;
        $roll->save();
        $fixed++;
    }
}
echo "Fixed remaining_quantity for $fixed rolls.\n";
