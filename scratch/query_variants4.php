<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Deleted variants? checking if there's soft deletes:\n";
$variant19 = DB::table('production_goods_variants')->where('production_goods_id', 103)->where('master_size_measurement_id', 19)->first();
if ($variant19) {
    echo "Variant exists: " . json_encode($variant19) . "\n";
} else {
    echo "Variant 19 missing completely from production_goods_variants.\n";
}

// Any variant items matching size 19? But they link by production_good_variant_id
// Let's check domestic inventory!
echo "\nDomestic Inventory for Product 103, Size Set 19:\n";
$invs = DB::table('domestic_inventories')->where('product_id', 103)->where('size_set_id', 19)->get();
foreach ($invs as $inv) {
    echo "Inventory: Color ID: {$inv->color_id}, Total Boxes: {$inv->total_boxes}\n";
}
