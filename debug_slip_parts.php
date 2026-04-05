<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionSlipDigitization;

$slip = ProductionSlipDigitization::where('slip_file', 'production-slip-1586_1774434110.jpg')->with('parts')->first();
if ($slip) {
    echo "ID: {$slip->id}, Status: {$slip->status}\n";
    foreach ($slip->parts as $part) {
        echo "Part ID: {$part->id}, SetQ: {$part->set_quantity}, SingleQ: {$part->single_quantity}\n";
    }
} else {
    echo "Slip not found.\n";
}
