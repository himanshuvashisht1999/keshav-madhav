<?php
$oldBoxes = App\Models\DomesticInventory::whereIn('id', [1743, 1899, 1900, 1901])->update(['size_set_id' => 103]);

$newVariant = new App\Models\ProductionGoodVariant();
$newVariant->production_goods_id = 58;
$newVariant->master_size_measurement_id = 103;
$newVariant->mrp = '1100.00';
$newVariant->image = null;
$newVariant->save();

$colors = DB::select('SELECT * FROM production_goods_variant_colors WHERE variant_id = 286');
foreach($colors as $color) {
    DB::insert('INSERT INTO production_goods_variant_colors (variant_id, master_color_id, barcode, image, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [$newVariant->id, $color->master_color_id, str_replace('S35', 'S103', $color->barcode), $color->image]);
}

echo "Successfully created 6-piece variant and reassigned inventory.\n";
