<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Database: " . DB::getDatabaseName() . "\n";
echo "Has column agent_order_dispatch_id in agent_order_items: " . (Schema::hasColumn('agent_order_items', 'agent_order_dispatch_id') ? 'YES' : 'NO') . "\n";

$columns = DB::select("SHOW COLUMNS FROM agent_order_items");
foreach ($columns as $column) {
    echo $column->Field . "\n";
}
