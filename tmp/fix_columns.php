<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'order_printing_stage_transactions',
    'order_printing_to_stiching_transactions',
    'order_godam_stage_transactions'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        if (!Schema::hasColumn($table, 'type')) {
            echo "Adding 'type' to '$table'...\n";
            Schema::table($table, function ($t) {
                $t->tinyInteger('type')->default(1)->after('status')->comment('1: Regular, 2: Damage');
            });
            echo "  Done.\n";
        } else {
            echo "Column 'type' already exists in '$table'.\n";
        }
    } else {
        echo "Table '$table' does not exist!\n";
    }
}

// Also ensure the first migration's columns exist
$tables1 = [
    'production_slip_digitization' => 'save_type',
    'order_stage_transactions' => 'status'
];

foreach ($tables1 as $table => $after) {
    if (Schema::hasTable($table)) {
        if (!Schema::hasColumn($table, 'type')) {
            echo "Adding 'type' to '$table'...\n";
            Schema::table($table, function ($t) use ($after) {
                $t->tinyInteger('type')->default(1)->after($after)->comment('1: Regular, 2: Damage');
            });
            echo "  Done.\n";
        } else {
            echo "Column 'type' already exists in '$table'.\n";
        }
    }
}
