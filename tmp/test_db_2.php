<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = DB::select("SHOW COLUMNS FROM agent_order_items");
foreach ($columns as $column) {
    printf("%-25s | %-15s | %-10s\n", $column->Field, $column->Type, $column->Null);
}
