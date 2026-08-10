<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = \Illuminate\Http\Request::create('/admin/packing/order-details/57', 'GET', ['unit_id' => 1, 'slip_id' => 1551]);
$controller = app(\App\Http\Controllers\Admin\PackingController::class);
$response = $controller->getOrderDetailsJson(57, $request);
file_put_contents('output.json', $response->getContent());
