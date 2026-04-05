<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slip_id = 88;
$ctrl = app(\App\Http\Controllers\Admin\PackingController::class);
$svc = app(\App\Services\Admin\PackingService::class);

$slip = $svc->getSlipDetails($slip_id);
echo "Order Main ID: " . $slip->orderMain?->id . "\n";
$packing = $svc->getPackingMainWithStructure($slip_id);
$order = null;
if ($packing && $packing->order_main_id) {
    $order = \App\Models\OrderMain::find($packing->order_main_id);
} else if ($slip->sku) {
    $order = $svc->getOrderDetails($slip->sku);
}

if ($order) {
    $avail = $svc->getAvailableQuantitiesAtUnit($order->id, $slip->stage_master_unit_id);
    print_r($avail);
} else {
    echo "No Order found.\n";
}
