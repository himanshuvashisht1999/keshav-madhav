<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderMain;

$customer_name = "A-One Traders";
$order = OrderMain::whereHas('customer', function($q) use ($customer_name) {
    $q->where('name', 'LIKE', '%' . $customer_name . '%');
})->with('OrderProductSets.product_set_details')->orderBy('id', 'desc')->first();

if (!$order) {
    echo "Order for customer $customer_name not found\n";
    exit;
}

echo "Order ID: " . $order->id . " SKU: " . $order->sku . "\n";
foreach ($order->OrderProductSets as $set) {
    echo "Set ID: " . $set->id . ", Set Qty: " . $set->set_quantity . ", No of Pcs: " . $set->no_of_pcs . "\n";
    foreach ($set->product_set_details as $d) {
        echo "  - Size: " . $d->size . ", Total Qty: " . $d->total_quantity . "\n";
    }
}
