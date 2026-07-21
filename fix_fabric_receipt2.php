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

$rollMeters = [
    1 => 110,
    2 => 103,
    3 => 118,
    4 => 130,
    5 => 132,
    6 => 100,
    7 => 131,
    8 => 134,
    9 => 100,
    10 => 120,
    11 => 108,
    12 => 130,
    13 => 90,
    14 => 137,
    15 => 100,
    16 => 128,
    17 => 100,
    18 => 106,
    19 => 110,
    20 => 126,
    21 => 112,
    22 => 127,
    23 => 76,
    24 => 110,
    25 => 115,
];

$details = FabricReceiptDetail::where('fabric_receipt_id', 1)->get();

foreach ($details as $detail) {
    $rollNo = intval($detail->roll_number);
    if (isset($rollMeters[$rollNo])) {
        $detail->meter = $rollMeters[$rollNo];
        $detail->remaining_quantity = 0;
        $detail->status = 'Used';
        $detail->save();
        echo "Roll {$rollNo} updated to {$rollMeters[$rollNo]} meters.\n";
    }
}

$receipt->total_meter = 2853.00;
$receipt->amount = 482157.00;
$receipt->gst_amount = 24107.85;
$receipt->total_amount = 506265.00; // 482157.00 + 24107.85 + 0.15 (rounded)
$receipt->save();

echo "Receipt 1 totals updated: Meter 2853, Amount 482157, GST 24107.85, Total 506265.\n";
