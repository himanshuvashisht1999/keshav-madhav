<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = \App\Models\AgentOrderDispatch::whereHas('orders', function($q) {
    $q->where('sale_type', 'item');
});

echo "Item Dispatches Count: " . $q->count() . "\n";
foreach($q->get() as $dispatch) {
    echo "Dispatch {$dispatch->id}:\n";
    foreach($dispatch->orders as $o) {
        echo "  - Order {$o->id} Sale Type: {$o->sale_type}\n";
    }
}
