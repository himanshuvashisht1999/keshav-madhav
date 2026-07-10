<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricRollAssigning;

$assignments = FabricRollAssigning::where('fabric_receipt_detail_id', 1778)->get(['id', 'lot_no', 'meter', 'created_at'])->toArray();
print_r($assignments);
