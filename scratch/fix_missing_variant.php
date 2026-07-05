<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductionGoodVariant;
use App\Models\ProductionGoodVariantItem;

// Re-create the missing variant for size set 19 (26*30) on product 103
$variant = new ProductionGoodVariant();
$variant->production_goods_id = 103;
$variant->master_size_measurement_id = 19;
$variant->mrp = 1160.00; // Same as other variants for JIMMY 3
$variant->save();

echo "Created Variant ID: {$variant->id}\n";

// Colors from inventory: 4, 72, 73, 74, 12
$colors = [4, 72, 73, 74, 12];
foreach ($colors as $color_id) {
    // Generate a temporary barcode or pull from inventory if possible
    $barcode = 'D' . $variant->production_goods_id . 'S' . $variant->master_size_measurement_id . 'C' . $color_id;
    
    $item = new ProductionGoodVariantItem();
    $item->variant_id = $variant->id;
    $item->master_color_id = $color_id;
    $item->barcode = $barcode;
    $item->save();
    
    echo "Created Variant Item for Color ID: {$color_id} with barcode: {$barcode}\n";
}

echo "Fix completed.\n";
