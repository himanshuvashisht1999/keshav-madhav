<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['vendors'] as $table) {
    if (Illuminate\Support\Facades\Schema::hasTable($table)) {
        $results = DB::select("DESCRIBE $table");
        echo "Structure of $table:\n";
        foreach ($results as $row) {
            echo " - " . $row->Field . "\n";
        }
        echo "\n";
    }
}
