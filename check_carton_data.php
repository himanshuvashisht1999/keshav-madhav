<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\OrderMain::where('sku', 'A-O/18/03/2026/15')->with('dispatchCartons.items')->first();
if ($order) {
    foreach ($order->dispatchCartons as $carton) {
        echo 'Carton: ' . $carton->carton_no . PHP_EOL;
        echo '  Items count (PackingItem): ' . $carton->items->count() . PHP_EOL;
        echo '  Box count (PackingBox): ' . $carton->boxes->count() . PHP_EOL;
        echo '  Sum of items quantity: ' . $carton->items->sum('quantity') . PHP_EOL;
    }
} else {
    echo 'Order not found' . PHP_EOL;
}
