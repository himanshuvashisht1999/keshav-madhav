<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionSlipDigitization;

echo "--- Production Slip 53 ---\n";
$slip = ProductionSlipDigitization::find(53);
if ($slip) {
    echo "ID: {$slip->id}, UnitID: {$slip->stage_master_unit_id}, Lot: {$slip->lot_no}, Status: {$slip->status}\n";
} else {
    echo "Slip 53 not found.\n";
}

echo "\n--- Latest Slips ---\n";
$slips = ProductionSlipDigitization::orderBy('id','desc')->limit(5)->get();
foreach ($slips as $s) {
    echo "ID: {$s->id}, UnitID: {$s->stage_master_unit_id}, Lot: {$s->lot_no}, Status: {$s->status}\n";
}
