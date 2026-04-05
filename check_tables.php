<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'purchase_orders',
    'agent_orders',
    'order_main',
    'fabric_receipts',
    'production_fabric_roll_assigning',
    'production_goods',
    'order_dispatch',
    'packing_mains',
    'stocks',
    'item_receipts',
    'order_cutting_stage',
    'order_printing_stage_transactions',
    'order_godam_stage_transactions',
    'order_stage_transactions',
    'production_slip_digitization'
];

foreach ($tables as $t) {
    if (\Illuminate\Support\Facades\Schema::hasTable($t)) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn($t, 'status')) {
            echo $t . " missing column status.\n";
        }
    } else {
        echo $t . " not found.\n";
    }
}
