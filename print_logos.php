<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$brands = \App\Models\Brand::all();
foreach($brands as $b) {
    echo $b->name . " - " . $b->logo . "\n";
}
