<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$goods = \App\Models\ProductionGoods::find(706);
echo "Good ID: {$goods->id}, Name: {$goods->name_of_garment}, Design: {$goods->design_number}\n";

$orderItems = \Illuminate\Support\Facades\DB::table('agent_order_items')->where('product_id', 706)->get();
echo "Order items: " . count($orderItems) . "\n";
foreach($orderItems as $item) {
    echo "Order ID: {$item->agent_order_id}, Qty: {$item->quantity}, Scanned: {$item->scanned_quantity}, Dispatched: {$item->dispatched_at}\n";
}

$historyCount = \App\Models\DomesticInventoryHistory::where('old_product_id', 706)->orWhere('new_product_id', 706)->count();
echo "Total history: {$historyCount}\n";
