<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\AgentOrderItem::where('agent_order_id', 340)->get();

foreach($items as $i) {
    echo "Item: {$i->product_id}-{$i->color_id}-{$i->size_set_id} qty: {$i->box_qty}\n";
}
