<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$part = \App\Models\ProductionSlipDigitizationParts::first();
print_r($part ? $part->toArray() : 'not found');
