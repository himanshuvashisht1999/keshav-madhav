<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slip_id = 1550;
$slip = \App\Models\ProductionSlipDigitization::find($slip_id);
if (!$slip) die("Slip not found\n");

$packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
$order_id = $packing ? $packing->order_main_id : null;

if (!$order_id) {
    die("No order found\n");
}

$cartons = \App\Models\PackingCarton::whereHas('main', function($q) use($slip_id) { 
    $q->where('slip_id', $slip_id); 
})->get();

foreach($cartons as $carton) {
    \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
    \App\Models\PackingBox::where('packing_carton_id', $carton->id)->delete();
    $carton->delete();
}

$orderLots = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
$transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
    ->whereIn('lot_no', $orderLots)
    ->get();

foreach ($transactions as $tx) {
    $tx->remaining_quantity = $tx->quantity;
    $tx->save();
}

$remaining_mains = \App\Models\PackingMain::where('order_main_id', $order_id)->get();
foreach ($remaining_mains as $main) {
    $items = \App\Models\PackingItem::where('packing_main_id', $main->id)->get();
    foreach ($items as $item) {
        $detail = \App\Models\OrderProductSetDetail::find($item->size_id);
        $lotsToDeduct = \App\Models\OrderLot::where('order_products_set_id', $detail->order_products_set_id ?? 0)->pluck('lot_no')->toArray();
        if (empty($lotsToDeduct)) {
            $lotsToDeduct = $orderLots;
        }

        $txs = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
            ->whereIn('lot_no', $lotsToDeduct)
            ->orderBy('id')
            ->get();

        $rem = $item->quantity;
        foreach ($txs as $tx) {
            if ($rem <= 0) break;
            if ($tx->remaining_quantity <= 0) continue;
            
            if ($tx->remaining_quantity > $rem) {
                $tx->remaining_quantity -= $rem;
                $rem = 0;
            } else {
                $rem -= $tx->remaining_quantity;
                $tx->remaining_quantity = 0;
            }
            $tx->save();
        }
    }
}

echo "Successfully wiped cartons for slip " . $slip_id . " and restored true lot quantities for Order ID " . $order_id . "!\n";
