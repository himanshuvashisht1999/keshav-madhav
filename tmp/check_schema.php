<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Domestic Inventories:\n";
var_export(Illuminate\Support\Facades\Schema::getColumnListing('domestic_inventories'));
echo "\n\nProduction Goods:\n";
var_export(Illuminate\Support\Facades\Schema::getColumnListing('production_goods'));
echo "\n";
