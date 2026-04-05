<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order_id = 9;
$unit_id = 10;

echo "MIRROR OUTFLOWS:\n";
$txs = \DB::table('order_stage_transactions')->where('from_stage_id', 11)
    ->where('sub_stage_id', $unit_id)
    ->where('status', '>=', 0)
    ->get(['id', 'type', 'quantity', 'created_at']);
foreach($txs as $t) {
    echo "TX {$t->id} - {$t->type} - {$t->quantity} pcs\n";
    $dets = \DB::table('order_stage_transaction_details')->where('order_stage_transaction_id', $t->id)->get();
    foreach($dets as $d) echo "  Size: {$d->size} - Qty: {$d->quantity}\n";
}

echo "\nLOGGED OUTFLOWS:\n";
$logs = \DB::table('production_outflow_inventories')
    ->where('order_main_id', $order_id)
    ->where('responsible_unit_id', $unit_id)
    ->get();
foreach($logs as $l) {
    echo "LOG {$l->id} - {$l->type} - size_id: {$l->size_id} - qty: {$l->quantity}\n";
}
