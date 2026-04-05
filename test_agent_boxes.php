<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\AgentOrderItem::with('order')
    ->whereHas('order', function ($q) {
        $q->where('status', '!=', 'dispatched'); })
    ->where('product_id', 5) // Vintage Relaxed Fit
    ->get();

echo "Found " . count($items) . " items.\n";
foreach ($items as $idx => $i) {
    echo "Item $idx: Qty=" . $i->quantity . ", packing_box_id=" . json_encode($i->packing_box_id) . "\n";
}
