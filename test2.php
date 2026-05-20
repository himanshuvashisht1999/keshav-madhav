<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dispatch = \App\Models\AgentOrderDispatch::with('orders')->find(55);
if($dispatch) {
    echo "Dispatch 55 Orders Sale Types:\n";
    foreach($dispatch->orders as $order) {
        echo "Order ID: {$order->id}, Sale Type: {$order->sale_type}, Order Type: {$order->order_type}\n";
    }
} else {
    echo "Dispatch 55 not found\n";
}

$dispatch = \App\Models\AgentOrderDispatch::with('orders')->find(54);
if($dispatch) {
    echo "Dispatch 54 Orders Sale Types:\n";
    foreach($dispatch->orders as $order) {
        echo "Order ID: {$order->id}, Sale Type: {$order->sale_type}, Order Type: {$order->order_type}\n";
    }
}
