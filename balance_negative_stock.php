<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;

$rolls = FabricReceiptDetail::where('remaining_quantity', '<', 0)->get();
$fixed = 0;
foreach ($rolls as $roll) {
    // We add the negative remaining quantity back to the meter
    // Example: meter = 24, remaining = -10. 
    // We want meter = 34, remaining = 0.
    
    $roll->meter = (float)$roll->meter + abs((float)$roll->remaining_quantity);
    $roll->remaining_quantity = 0;
    $roll->save();
    $fixed++;
}
echo "Fixed $fixed rolls with negative remaining quantities by balancing their original meter receipts.\n";
