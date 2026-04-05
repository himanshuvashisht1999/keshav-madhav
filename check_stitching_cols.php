<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['order_stage_transactions', 'order_printing_stage_transactions'] as $table) {
    echo "--- $table ---" . PHP_EOL;
    $cols = DB::select("DESCRIBE $table");
    foreach ($cols as $col) {
        echo $col->Field . PHP_EOL;
    }
}
