<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\ProductionGoods::where('name_of_garment', 'LIKE', '%CAMPIN%')->first();
echo 'Product ID: ' . $product->id . "\n";
$inventory = \App\Models\DomesticInventory::where('product_id', $product->id)->get();
print_r($inventory->toArray());

$racks = \DB::table('domestic_inventories')
    ->where('product_id', $product->id)
    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
    ->select('domestic_inventories.*', 'racks.name as rack_name', 'storerooms.name as storeroom_name')
    ->get();
print_r($racks->toArray());

