<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'users',
    'production_slip_digitization',
    'order_stage_transactions',
    'order_printing_stage_transactions',
    'order_printing_to_stiching_transactions',
    'order_godam_stage_transactions'
];

foreach ($tables as $table) {
    $exists = Schema::hasTable($table) ? 'YES' : 'NO';
    $hasType = $exists === 'YES' && Schema::hasColumn($table, 'type') ? 'YES' : 'NO';
    echo "$table: Exists=$exists, TypeCol=$hasType\n";
}
