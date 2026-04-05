<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderMain;
use App\Models\OrderProductSet;

$orderId = 100;
$order = OrderMain::with('OrderProductSets.product_set_details')->find($orderId);

if (!$order) {
    echo "Order $orderId not found\n";
    exit;
}

echo "Order ID: " . $order->id . " Type: " . $order->order_type . "\n";
foreach ($order->OrderProductSets as $set) {
    echo "Set ID: " . $set->id . " Design: " . $set->design_number . "\n";
    foreach ($set->product_set_details as $detail) {
        echo "  Detail ID: " . $detail->id . " Size: [" . $detail->size . "] Rem: " . $detail->remaining_quantity . "\n";
    }
}
