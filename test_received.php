<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;

$totalReceived = FabricReceiptDetail::where('fabric_id', 105)->where('master_fabric_warehouse_id', 1)->sum('meter');
echo 'Total Received: ' . $totalReceived . "\n";
