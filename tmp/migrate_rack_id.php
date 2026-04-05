<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = 0;
\App\Models\PackingCarton::whereNotNull('rack_id')->each(function($carton) use (&$count) {
    $rows = \App\Models\DomesticInventory::where('packing_carton_id', $carton->id)
        ->update(['rack_id' => $carton->rack_id]);
    $count += $rows;
});

echo "SUCCESS: Migrated rack_id for $count inventory records.\n";
