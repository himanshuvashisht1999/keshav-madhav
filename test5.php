<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 706;
$in = \App\Models\DomesticInventoryHistory::where('new_product_id', $id)->where('type', '!=', 'transfer')->sum('box_quantity');
$out = \App\Models\DomesticInventoryHistory::where('old_product_id', $id)->where('type', '!=', 'transfer')->sum('box_quantity');
$bal = \App\Models\DomesticInventory::where('product_id', $id)->sum('total_boxes');

echo "In: $in, Out: $out, History Balance: " . ($in - $out) . ", Actual Balance: $bal\n";
