<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM domestic_inventories');
foreach ($results as $r) {
    echo "Column: {$r->Column_name} | Index: {$r->Key_name}\n";
}
