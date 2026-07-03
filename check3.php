<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lot = \App\Models\OrderLot::where('lot_no', '6444')->first();
$rolls = \App\Models\FabricRollAssigning::where('order_lot_id', $lot->id)->get();
$actual_fabrics = collect();
$currentRolls = $rolls->where('order_lot_id', $lot->id);
foreach($currentRolls as $roll) {
    if ($roll->fabricReceiptDetail && $roll->fabricReceiptDetail->fabric) {
        $actual_fabrics->push($roll->fabricReceiptDetail->fabric->name);
    }
}
$fabric_display = $actual_fabrics->unique()->implode(', ') ?: ($lot->orderProductSet->fabric?->name ?? '-');
echo "Fabric Display: " . $fabric_display . "\n";
