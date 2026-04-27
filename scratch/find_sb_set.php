<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterSizeMeasurement;

$measurements = MasterSizeMeasurement::where('name', 'like', '%SB%')->get();
foreach($measurements as $m) {
    echo "ID: {$m->id}, Name: {$m->name}, Size Group: {$m->size_group}\n";
}
