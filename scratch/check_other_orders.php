<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$missing = DB::table('order_products')
    ->join('master_size_measurements', 'order_products.size_set_id', '=', 'master_size_measurements.id')
    ->select('order_products.product_id', 'order_products.size_set_id', 'master_size_measurements.name as size_set_name')
    ->distinct()
    ->leftJoin('production_goods_variants', function($join) {
        $join->on('order_products.product_id', '=', 'production_goods_variants.production_goods_id')
             ->on('order_products.size_set_id', '=', 'production_goods_variants.master_size_measurement_id');
    })
    ->whereNull('production_goods_variants.id')
    ->get();

if ($missing->isEmpty()) {
    echo "NO_MISSING_DATA in order_products\n";
} else {
    echo "MISSING_DATA_FOUND in order_products:\n";
    foreach ($missing as $item) {
        echo "- Product ID: {$item->product_id} | Size Set: {$item->size_set_name} (ID: {$item->size_set_id})\n";
    }
}
