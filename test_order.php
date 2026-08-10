<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$packing = \App\Models\PackingMain::where('slip_id', 1551)->first();
echo "Order ID: " . $packing->order_main_id . "\n";

$unit_lots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
    ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
    ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
    ->where('order_lots.order_main_id', $packing->order_main_id)
    ->select('order_stage_transactions.lot_no', 'order_products_sets.id as set_id', 'order_products_sets.design_number')
    ->get();

echo "Lots:\n";
print_r($unit_lots);
?>
