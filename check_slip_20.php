<?php

use App\Models\ProductionSlipDigitization;
use App\Models\OrderMain;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$slip = ProductionSlipDigitization::find(20);

if (!$slip) {
    echo "Slip 20 not found.\n";
    exit;
}

echo "Slip SKU: " . $slip->sku . "\n";
echo "Slip Attributes: " . json_encode($slip->toArray()) . "\n";

if ($slip->sku) {
    $order = OrderMain::where('sku', $slip->sku)->first();
    if ($order) {
        echo "Order Found: " . $order->id . " SKU: " . $order->sku . "\n";
    } else {
        echo "Order NOT found for SKU: " . $slip->sku . "\n";
        // Check fuzzy
        $likeOrder = OrderMain::where('sku', 'LIKE', '%'.$slip->sku.'%')->first();
        if($likeOrder) {
             echo "Possible Match: " . $likeOrder->id . " SKU: " . $likeOrder->sku . "\n";
        }
    }
} else {
    echo "Slip has no SKU.\n";
}
