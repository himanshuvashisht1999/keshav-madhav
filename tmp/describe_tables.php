<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'order_printing_to_stiching_transactions';
$columns = DB::select("DESCRIBE $table");
echo "Columns for $table:\n";
foreach ($columns as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")\n";
}

$table2 = 'order_stage_transactions';
$columns2 = DB::select("DESCRIBE $table2");
echo "\nColumns for $table2:\n";
foreach ($columns2 as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")\n";
}

$table3 = 'order_printing_stage_transactions';
$columns3 = DB::select("DESCRIBE $table3");
echo "\nColumns for $table3:\n";
foreach ($columns3 as $column) {
    echo "- " . $column->Field . " (" . $column->Type . ")\n";
}
