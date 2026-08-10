<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slip = \App\Models\ProductionSlipDigitization::find(1551);
$order = \App\Models\OrderMain::find($slip->order_main_id ?? 1);
$unit_lots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
    ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
    ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
    ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
    ->where('order_stage_transactions.to_stage_id', 11)
    ->where('order_stage_transactions.sub_stage_id_to', $slip->stage_master_unit_id)
    ->where('order_lots.order_main_id', $order->id)
    ->select('order_stage_transactions.lot_no', 'order_products_sets.id as set_id', 'order_products_sets.design_number', 'master_size_measurements.name as size_set_name', 'order_stage_transactions.quantity', 'order_stage_transactions.remaining_quantity')
    ->get();

file_put_contents('dump_lots.json', json_encode($unit_lots, JSON_PRETTY_PRINT));
echo "Dumped lots\n";
?>
