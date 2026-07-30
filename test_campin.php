<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$query = \App\Models\DomesticInventory::where('domestic_inventories.status', 1)
    ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
    ->select('production_goods.name_of_garment', 'production_goods.design_number', 'storerooms.name as storeroom_name', 'domestic_inventories.total_boxes')
    ->where('production_goods.name_of_garment', 'LIKE', '%CAMPIN%')
    ->get();

print_r($query->toArray());
