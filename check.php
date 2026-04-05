<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- STOREROOMS ---\n";
print_r(\Illuminate\Support\Facades\DB::select("SELECT * FROM storerooms LIMIT 5"));

echo "\n--- RACKS ---\n";
print_r(\Illuminate\Support\Facades\DB::select("SELECT * FROM racks LIMIT 5"));

echo "\n--- PACKING CARTONS ---\n";
print_r(\Illuminate\Support\Facades\DB::select("SELECT id, carton_no, rack_id FROM packing_cartons WHERE rack_id IS NOT NULL LIMIT 5"));

echo "\n--- DOMESTIC INVENTORY ---\n";
print_r(\Illuminate\Support\Facades\DB::select("SELECT id, packing_carton_id FROM domestic_inventories WHERE packing_carton_id > 0 LIMIT 5"));
