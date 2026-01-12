<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check for nulls
$nullCount = \App\Models\OrderProductSetDetail::whereNull('order_products_set_id')->count();
$totalCount = \App\Models\OrderProductSetDetail::count();

echo "Total Details: $totalCount\n";
echo "Details with NULL set_id: $nullCount\n";

// Scan for a valid record
$validFound = false;
$details = \App\Models\OrderProductSetDetail::whereNotNull('order_products_set_id')->take(100)->get();

foreach ($details as $record) {
    $setCheck = \App\Models\OrderProductSet::find($record->order_products_set_id);
    if ($setCheck) {
        echo "FOUND VALID RECORD! Detail ID: " . $record->id . " -> Set ID: " . $record->order_products_set_id . "\n";
        $validFound = true;
        
        // Check relationships on valid record
         $set = $record->orderProductSet;
         if ($set) {
             echo "Relationship Works! Fabric ID: " . $set->fabric_id . "\n";
         } else {
             echo "Relationship Failed despite record existing!\n";
         }
        break;
    }
}

if (!$validFound) {
    echo "Scanned 100 records and ALL are orphaned (Parent Set does not exist).\n";
}

