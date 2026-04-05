<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\Admin\OrderDispatchService::class);
$request = new \Illuminate\Http\Request(['search_order_no' => 'A-O/18/03/2026/15']);

$data = $service->getOrderPackingData($request);

if (!empty($data) && isset($data[0]['cartons'][0])) {
    $carton = $data[0]['cartons'][0];
    echo 'BOXES:' . $carton['boxes_in_carton'] . '|PCS:' . $carton['pcs_in_carton'] . PHP_EOL;
} else {
    echo 'Data not found' . PHP_EOL;
}
