<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = [
    'storerooms' => \Illuminate\Support\Facades\DB::select("SELECT * FROM storerooms LIMIT 1"),
    'racks' => \Illuminate\Support\Facades\DB::select("SELECT * FROM racks LIMIT 1"),
    'cartons' => \Illuminate\Support\Facades\DB::select("SELECT * FROM packing_cartons WHERE rack_id IS NOT NULL LIMIT 1"),
    'inventory_sample' => \Illuminate\Support\Facades\DB::select("SELECT * FROM domestic_inventories WHERE packing_carton_id > 0 LIMIT 1")
];

file_put_contents('schema_check.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Saved to schema_check.json\n";
