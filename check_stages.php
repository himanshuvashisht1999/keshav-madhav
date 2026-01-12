<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$stages = \App\Models\MasterProductStage::all(['id', 'name']);
foreach ($stages as $stage) {
    echo "ID: " . $stage->id . " - Name: " . $stage->name . "\n";
}
