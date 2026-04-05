<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indexes = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM domestic_inventories');
foreach ($indexes as $index) {
    echo "Column: " . $index->Column_name . " | Key_name: " . $index->Key_name . "\n";
}
