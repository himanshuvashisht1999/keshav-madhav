<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('order_cutting_stage', 'is_closed_for_unit')) {
    echo "EXISTS";
} else {
    echo "MISSING";
}
