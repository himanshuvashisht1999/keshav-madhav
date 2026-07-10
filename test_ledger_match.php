<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;
use App\Models\FabricRollAssigning;

$fabricId = 105;
$warehouseId = 1;
$rollIds = FabricReceiptDetail::where('fabric_id', $fabricId)
            ->where('master_fabric_warehouse_id', $warehouseId)
            ->pluck('id');
$usagesSum = FabricRollAssigning::whereIn('fabric_receipt_detail_id', $rollIds)
            ->sum('meter');
$rolls = FabricReceiptDetail::whereIn('id', $rollIds)->get();
$actualIssued = 0;
foreach($rolls as $r) {
    $actualIssued += ((float)$r->meter - (float)$r->remaining_quantity);
}
echo "Usages Sum: " . $usagesSum . "\n";
echo "Actual Issued: " . $actualIssued . "\n";
