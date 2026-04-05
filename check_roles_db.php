<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM roles");
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}
