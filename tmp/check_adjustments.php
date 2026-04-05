<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdjustmentMaster;
$masters = AdjustmentMaster::all();
foreach ($masters as $m) {
    echo "ID: " . $m->id . " - Name: " . $m->name . " - Model: " . $m->model_name . "\n";
}
