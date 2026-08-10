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
$carton = $content['packing']['cartons'][0];

$designs = [];
$colors = [];
$sizeSets = [];

if (isset($carton['items']) && count($carton['items']) > 0 && isset($carton['items'][0]['detail'])) {
    foreach ($carton['items'] as $item) {
        $detail = $item['detail'];
        if ($detail) {
            $ops = $detail['order_product_set'] ?? null;
            $prod = $detail['product'] ?? ($ops['product'] ?? null);
            $col = $detail['colors'] ?? ($ops['colors'] ?? null);
            $size = $detail['size_measurement'] ?? ($ops['size_measurement'] ?? null);

            if ($prod && isset($prod['design_number'])) $designs[] = $prod['design_number'];
            elseif ($ops && isset($ops['design_number'])) $designs[] = $ops['design_number'];
            elseif (isset($detail['design_number'])) $designs[] = $detail['design_number'];

            if ($col && isset($col['name'])) $colors[] = $col['name'];
            if ($size && isset($size['name'])) $sizeSets[] = $size['name'];
        }
    }
}

echo "DESIGNS: " . implode(', ', array_unique($designs)) . "\n";
echo "COLORS: " . implode(', ', array_unique($colors)) . "\n";
echo "SIZES: " . implode(', ', array_unique($sizeSets)) . "\n";
