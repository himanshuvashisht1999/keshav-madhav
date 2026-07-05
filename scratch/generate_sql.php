<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$productId = 45;
$sizeSetId = 20;

// Get MRP from an existing variant of Product 45, or default to 0
$mrp = DB::table('production_goods_variants')->where('production_goods_id', $productId)->value('mrp') ?: 0;

echo "-- 1. Insert the missing variant\n";
echo "INSERT INTO `production_goods_variants` (`production_goods_id`, `master_size_measurement_id`, `mrp`, `created_at`, `updated_at`) ";
echo "VALUES ({$productId}, {$sizeSetId}, {$mrp}, NOW(), NOW());\n\n";

echo "-- 2. Find the inserted variant ID (assuming it is the latest insert for this product/size)\n";
echo "SET @variant_id = LAST_INSERT_ID();\n\n";

// Get distinct colors from agent_order_items
$colorsFromOrders = DB::table('agent_order_items')
    ->where('product_id', $productId)
    ->where('size_set_id', $sizeSetId)
    ->pluck('color_id')
    ->toArray();

// Get distinct colors from domestic_inventories
$colorsFromInventory = DB::table('domestic_inventories')
    ->where('product_id', $productId)
    ->where('size_set_id', $sizeSetId)
    ->where('total_boxes', '>', 0)
    ->pluck('color_id')
    ->toArray();

$allColors = array_unique(array_merge($colorsFromOrders, $colorsFromInventory));

echo "-- 3. Insert the variant colors (barcodes)\n";
if (empty($allColors)) {
    echo "-- No colors found for this product and size set.\n";
} else {
    foreach ($allColors as $colorId) {
        if (!$colorId) continue;
        $barcode = 'D' . $productId . 'S' . $sizeSetId . 'C' . $colorId;
        echo "INSERT INTO `production_goods_variant_colors` (`variant_id`, `master_color_id`, `barcode`, `created_at`, `updated_at`) ";
        echo "VALUES (@variant_id, {$colorId}, '{$barcode}', NOW(), NOW());\n";
    }
}
