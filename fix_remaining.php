<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rolls = \App\Models\FabricReceiptDetail::all();
foreach ($rolls as $roll) {
    $used = \App\Models\FabricRollAssigning::where('fabric_receipt_detail_id', $roll->id)->sum('meter');
    $expected = $roll->meter - $used;
    if (abs($roll->remaining_quantity - $expected) > 0.01) {
        echo "Roll: " . $roll->roll_number . " Expected: " . $expected . " Actual: " . $roll->remaining_quantity . "\n";
        $roll->remaining_quantity = $expected;
        $roll->save();
    }
}
echo "Done.\n";
