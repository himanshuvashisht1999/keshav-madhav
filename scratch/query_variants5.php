<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking orphaned variant items for product 103:\n";
$items = DB::table('production_good_variant_items')
    ->whereIn('variant_id', function($query) {
        $query->select('id')->from('production_goods_variants')->where('production_goods_id', 103);
    })->get();
echo "Found " . count($items) . " items under existing variants.\n";

$all_orphans = DB::table('production_good_variant_items')
    ->whereNotIn('variant_id', function($query) {
        $query->select('id')->from('production_goods_variants');
    })->get();
echo "Found " . count($all_orphans) . " orphans in total.\n";

$barcodes = DB::table('production_good_variant_items')
    ->join('production_goods_variants', 'production_good_variant_items.variant_id', '=', 'production_goods_variants.id')
    ->where('production_goods_variants.production_goods_id', 103)
    ->get();

echo "Existing items for product 103:\n";
foreach ($barcodes as $b) {
    echo "Variant ID: {$b->variant_id}, Color ID: {$b->master_color_id}, Barcode: {$b->barcode}\n";
}
