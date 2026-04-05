<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 72; // SLIP ID from URL
$packing = \App\Models\PackingMain::where('slip_id', $id)->first();
if(!$packing) {
    echo "No packing for slip 72\n";
    // Check if it's the latest incomplete?
    // Let's just lookup the SLIP record
    $slip = \App\Models\ProductionSlipDigitization::find($id);
    echo "Slip Order Main ID Search: " . ($slip->order_main_id ?? 'N/A') . "\n";
} else {
    $order = \App\Models\OrderMain::find($packing->order_main_id);
    echo "ID: " . $order->id . " | Type: '" . $order->order_type . "'\n";
}
