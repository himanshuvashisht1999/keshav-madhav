<?php
$mismatches = DB::select('
    SELECT d.id, d.product_id, d.size_set_id, d.quantity, s.name, s.no_of_pcs
    FROM domestic_inventories d
    JOIN master_size_measurements s ON d.size_set_id = s.id
    WHERE d.quantity != s.no_of_pcs
');

$fixes = 0;
foreach ($mismatches as $row) {
    // find an alternative size set with the same name and the correct quantity
    $alt = DB::table('master_size_measurements')
        ->where('name', $row->name)
        ->where('no_of_pcs', $row->quantity)
        ->first();
        
    if (!$alt) {
        // create new master size measurement
        $original = DB::table('master_size_measurements')->where('id', $row->size_set_id)->first();
        $altId = DB::table('master_size_measurements')->insertGetId([
            'name' => $row->name,
            'sku' => $original->sku ?? '',
            'set_size' => $original->set_size ?? '',
            'no_of_pcs' => $row->quantity,
            'size_group' => $original->size_group ?? '',
            'status' => $original->status ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $alt = (object)['id' => $altId];
    }
        
    // update the inventory row
    DB::table('domestic_inventories')->where('id', $row->id)->update(['size_set_id' => $alt->id]);
    
    // ensure product variant exists
    $variant = DB::table('production_goods_variants')
        ->where('production_goods_id', $row->product_id)
        ->where('master_size_measurement_id', $alt->id)
        ->first();
        
    if (!$variant) {
        // copy from old variant if possible
        $oldVariant = DB::table('production_goods_variants')
            ->where('production_goods_id', $row->product_id)
            ->where('master_size_measurement_id', $row->size_set_id)
            ->first();
            
        $newVariantId = DB::table('production_goods_variants')->insertGetId([
            'production_goods_id' => $row->product_id,
            'master_size_measurement_id' => $alt->id,
            'mrp' => $oldVariant ? $oldVariant->mrp : 0,
            'image' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // copy colors
        if ($oldVariant) {
            $colors = DB::table('production_goods_variant_colors')
                ->where('variant_id', $oldVariant->id)
                ->get();
            foreach ($colors as $color) {
                // Update barcode S{old} to S{new}
                $newBarcode = preg_replace('/S'.$row->size_set_id.'C/', 'S'.$alt->id.'C', $color->barcode);
                DB::table('production_goods_variant_colors')->insert([
                    'variant_id' => $newVariantId,
                    'master_color_id' => $color->master_color_id,
                    'barcode' => $newBarcode,
                    'image' => $color->image,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
    $fixes++;
}
echo "Fixed $fixes mismatched inventory items and created variants!\n";
