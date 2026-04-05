<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($results as $r) {
    foreach ($r as $key => $value) {
        echo $value . "\n";
    }
}
