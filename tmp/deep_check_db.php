<?php

// Check 1: Products in inventory that do not exist in production_goods
$missingProducts = \DB::select("
    SELECT di.product_id, SUM(di.total_boxes) as boxes
    FROM domestic_inventories di
    LEFT JOIN production_goods pg ON di.product_id = pg.id
    WHERE pg.id IS NULL
    GROUP BY di.product_id
");

// Check 2: Size sets in inventory that do not exist for the product in production_goods_variants
$missingSizeSets = \DB::select("
    SELECT di.product_id, di.size_set_id, SUM(di.total_boxes) as boxes
    FROM domestic_inventories di
    LEFT JOIN production_goods_variants pgv 
        ON di.product_id = pgv.production_goods_id AND di.size_set_id = pgv.master_size_measurement_id
    WHERE pgv.id IS NULL
    GROUP BY di.product_id, di.size_set_id
");

// Check 3: Colors in inventory that do not exist for the product + size set in production_goods_variant_colors
$missingColors = \DB::select("
    SELECT di.product_id, di.size_set_id, di.color_id, SUM(di.total_boxes) as boxes
    FROM domestic_inventories di
    JOIN production_goods_variants pgv 
        ON di.product_id = pgv.production_goods_id AND di.size_set_id = pgv.master_size_measurement_id
    LEFT JOIN production_goods_variant_colors pgvc 
        ON pgv.id = pgvc.variant_id AND di.color_id = pgvc.master_color_id
    WHERE pgvc.id IS NULL
    GROUP BY di.product_id, di.size_set_id, di.color_id
");

echo "--- DEEP SEARCH RESULTS ---\n\n";

if (empty($missingProducts) && empty($missingSizeSets) && empty($missingColors)) {
    echo "NO DISCREPANCIES FOUND! All inventory records correctly match the product master.\n";
} else {
    if (!empty($missingProducts)) {
        echo "1. ORPHANED PRODUCTS (Product doesn't exist in master):\n";
        foreach ($missingProducts as $mp) {
            echo "   - Product ID: {$mp->product_id} | Total Boxes: {$mp->boxes}\n";
        }
        echo "\n";
    }

    if (!empty($missingSizeSets)) {
        echo "2. MISSING SIZE SETS (Size set doesn't exist for product in master):\n";
        foreach ($missingSizeSets as $ms) {
            echo "   - Product ID: {$ms->product_id} | Size Set ID: {$ms->size_set_id} | Total Boxes: {$ms->boxes}\n";
        }
        echo "\n";
    }

    if (!empty($missingColors)) {
        echo "3. MISSING COLORS (Color doesn't exist for size set in master):\n";
        foreach ($missingColors as $mc) {
            echo "   - Product ID: {$mc->product_id} | Size Set ID: {$mc->size_set_id} | Color ID: {$mc->color_id} | Total Boxes: {$mc->boxes}\n";
        }
        echo "\n";
    }
}
