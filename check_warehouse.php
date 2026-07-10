<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;

$rolls = FabricReceiptDetail::where('fabric_id', 105)
    ->selectRaw('master_fabric_warehouse_id, sum(meter) as total_received, sum(remaining_quantity) as total_remaining')
    ->groupBy('master_fabric_warehouse_id')
    ->get()
    ->toArray();
print_r($rolls);
