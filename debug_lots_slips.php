<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionSlipDigitization;

echo "--- Slips for Lot 601 ---\n";
$slips = ProductionSlipDigitization::where('lot_no', '601')->get();
foreach ($slips as $s) {
    echo "ID: {$s->id}, UnitID: {$s->stage_master_unit_id}, Status: {$s->status}\n";
}
