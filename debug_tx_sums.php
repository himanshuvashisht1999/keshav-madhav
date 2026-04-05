<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderStageTransaction;
use App\Models\OrderLot;
use Illuminate\Support\Facades\DB;

$order_id = 19;
$unit_id = 11;

$lots = OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();

$incoming = DB::table('order_stage_transactions as tx')
    ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
    ->whereIn('tx.lot_no', $lots)
    ->where('tx.to_stage_id', 11)
    ->where('tx.sub_stage_id_to', $unit_id)
    ->where('tx.status', 1)
    ->select('det.size', DB::raw('SUM(det.quantity) as total'))
    ->groupBy('det.size')
    ->pluck('total', 'det.size')
    ->toArray();

echo "Incoming Sums:\n";
print_r($incoming);

$outgoing = DB::table('order_stage_transactions as tx')
    ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
    ->whereIn('tx.lot_no', $lots)
    ->where('tx.from_stage_id', 11)
    ->where('tx.sub_stage_id', $unit_id)
    ->where('tx.status', 1)
    ->select('det.size', DB::raw('SUM(det.quantity) as total'))
    ->groupBy('det.size')
    ->pluck('total', 'det.size')
    ->toArray();

echo "\nOutgoing Sums:\n";
print_r($outgoing);
