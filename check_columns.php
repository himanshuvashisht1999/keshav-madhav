<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['order_cutting_stage', 'order_stage_transactions', 'order_printing_stage_transactions', 'order_printing_to_stiching_transactions', 'order_products_sets'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo $table . ': ' . (Schema::hasColumn($table, 'is_closed_for_unit') ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo $table . ': TABLE NOT FOUND' . PHP_EOL;
    }
}
