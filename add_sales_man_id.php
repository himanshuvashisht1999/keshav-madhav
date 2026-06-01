<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('agent_orders', 'sales_man_id')) {
    Schema::table('agent_orders', function(Blueprint $table) {
        $table->unsignedBigInteger('sales_man_id')->nullable()->after('sales_agent_id');
    });
    echo "Column added successfully.\n";
} else {
    echo "Column already exists.\n";
}
