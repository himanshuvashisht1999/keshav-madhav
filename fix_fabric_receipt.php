<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;

$receipt = FabricReceipt::find(1);
if (!$receipt) {
    echo "Receipt 1 not found.\n";
    exit;
}

$details = FabricReceiptDetail::where('fabric_receipt_id', 1)->get();
$newTotalMeters = 0;

foreach ($details as $detail) {
    $meter = floatval($detail->meter);
    $remaining = floatval($detail->remaining_quantity);
    
    $newMeter = $meter - $remaining;
    $detail->meter = $newMeter;
    $detail->remaining_quantity = 0;
    
    if ($detail->remaining_quantity <= 0) {
        $detail->status = 'Used';
    }
    
    $detail->save();
    echo "Roll: {$detail->roll_number} updated. New Meter: {$newMeter}, Remaining: 0\n";
    
    $newTotalMeters += $newMeter;
}

$receipt->total_meters = $newTotalMeters;
$receipt->save();

echo "Receipt 1 total_meters updated to: {$newTotalMeters}\n";
