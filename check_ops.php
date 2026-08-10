<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/admin/packing/order-details/57', 'GET', [
    'unit_id' => 1,
    'slip_id' => 1551
]);

$controller = app(\App\Http\Controllers\Admin\PackingController::class);
$response = $controller->getOrderDetailsJson(57, $request);

$content = json_decode($response->getContent(), true);
$item = $content['packing']['cartons'][0]['items'][0];

if (isset($item['detail']['order_product_set'])) {
    echo "order_product_set exists in JS JSON!\n";
    echo "KEYS: " . implode(', ', array_keys($item['detail']['order_product_set'])) . "\n";
} else {
    echo "order_product_set DOES NOT EXIST in JS JSON!\n";
}
