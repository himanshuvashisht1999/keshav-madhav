<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "sales_orders: " . (\Illuminate\Support\Facades\Schema::hasTable('sales_orders') ? "Yes" : "No") . "\n";
echo "sales_order_items: " . (\Illuminate\Support\Facades\Schema::hasTable('sales_order_items') ? "Yes" : "No") . "\n";
