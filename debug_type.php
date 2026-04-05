<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\OrderMain::orderBy('id', 'desc')->first();
if($order) {
    echo "ID: " . $order->id . " | Type: '" . $order->order_type . "'\n";
} else {
    echo "No orders found\n";
}
