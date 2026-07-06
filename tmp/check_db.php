<?php

$inventories = \App\Models\DomesticInventory::select('product_id', 'size_set_id', 'color_id', \DB::raw('COUNT(*) as total_records'), \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(total_boxes) as total_boxes'))
    ->groupBy('product_id', 'size_set_id', 'color_id')
    ->get();

$out = fopen("tmp/mismatches.txt", "w");

foreach ($inventories as $inv) {
    $product = \App\Models\ProductionGoods::find($inv->product_id);
    $color = \App\Models\MasterColor::find($inv->color_id);
    $sizeSet = \App\Models\MasterSizeMeasurement::find($inv->size_set_id);
    
    $pName = $product ? $product->name : 'Unknown';
    $cName = $color ? $color->name : 'Unknown';
    $sName = $sizeSet ? $sizeSet->name : 'Unknown';

    // Check if variant exists for this product and size set
    $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $inv->product_id)
        ->where('master_size_measurement_id', $inv->size_set_id)
        ->first();

    if (!$variant) {
        fwrite($out, "Product: {$pName} (ID: {$inv->product_id}), Size Set: {$sName}, Color in Inventory: {$cName} -> ISSUE: Size Set not found in Product Variants (Boxes in Inventory: {$inv->total_boxes})\n");
        continue;
    }

    // Check if color exists in this variant
    $colorExists = \App\Models\ProductionGoodVariantItem::where('variant_id', $variant->id)
        ->where('master_color_id', $inv->color_id)
        ->exists();

    if (!$colorExists) {
        fwrite($out, "Product: {$pName} (ID: {$inv->product_id}), Size Set: {$sName}, Color in Inventory: {$cName} -> ISSUE: Color not found in Product Variant Colors (Boxes in Inventory: {$inv->total_boxes})\n");
    }
}
fclose($out);
