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

$newTotal = FabricReceiptDetail::where('fabric_receipt_id', 1)->sum('meter');
$receipt->total_meter = $newTotal;
$receipt->save();

echo "Total updated to " . $newTotal . "\n";
