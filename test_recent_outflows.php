<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$recent = \App\Models\ProductionOutflowInventory::orderBy('id', 'desc')->take(5)->get();
print_r($recent->toArray());
?>
