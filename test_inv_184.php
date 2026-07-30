<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inv = \App\Models\DomesticInventory::where('product_id', 184)
    ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
    ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
    ->select('domestic_inventories.*', 'storerooms.name as storeroom_name')
    ->get();

foreach($inv as $i) {
    echo "Color: {$i->color_id}, Size: {$i->size_set_id}, Qty: {$i->total_boxes}, Rack: {$i->rack_id}, Storeroom: {$i->storeroom_name}\n";
}
