<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\ProductionGoods::find(103);
if ($product) {
    echo "Product: {$product->name}\n";
    foreach ($product->variants()->get() as $variant) {
        $sizeSet = $variant->sizeSet ? $variant->sizeSet->name : 'N/A';
        echo "Variant ID: {$variant->id}, Size Set ID: {$variant->master_size_measurement_id}, Size Set: {$sizeSet}, Status: {$variant->status}, MRP: {$variant->mrp}\n";
    }
} else {
    echo "Product 103 not found.\n";
}
