<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;

$unusedRolls = FabricReceiptDetail::where('fabric_id', 105)->where('master_fabric_warehouse_id', 1)->where('remaining_quantity', '>', 0)->get(['id', 'roll_number', 'meter', 'remaining_quantity'])->toArray();

print_r($unusedRolls);
