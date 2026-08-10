<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$service = app(\App\Services\Admin\PackingService::class);
$pm = $service->getPackingMainWithStructure(1551);
if ($pm && count($pm->cartons) > 0 && count($pm->cartons[0]->items) > 0) {
    $ops = $pm->cartons[0]->items[0]->detail->orderProductSet;
    echo "ops.design_number = " . $ops->design_number . "\n";
    echo "ops.product.design_number = " . ($ops->product ? $ops->product->design_number : 'NULL') . "\n";
    echo "ops.colors.name = " . ($ops->colors ? $ops->colors->name : 'NULL') . "\n";
    echo "ops.size_measurement.name = " . ($ops->size_measurement ? $ops->size_measurement->name : 'NULL') . "\n";
} else {
    echo "No items found.";
}
