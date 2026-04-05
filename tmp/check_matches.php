<?php
$total = \App\Models\DomesticInventory::count();
$matchedSizes = \App\Models\DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')->count();
$matchedColors = \App\Models\DomesticInventory::join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')->count();

echo "Total Inventory: $total\n";
echo "Matched Sizes: $matchedSizes\n";
echo "Matched Colors: $matchedColors\n";
