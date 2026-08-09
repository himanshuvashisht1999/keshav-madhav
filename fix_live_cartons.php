<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slip_id = 1409;
$slip = \App\Models\ProductionSlipDigitization::find($slip_id);
if (!$slip) {
    echo "Slip not found\n";
    exit;
}

$order_id = $slip->order_main_id;
if (!$order_id) {
    echo "Slip has no order_main_id\n";
    exit;
}

echo "Found slip for Order #$order_id\n";

// 1. Delete cartons for this slip
$cartons = \App\Models\PackingCarton::whereHas('main', function($q) use($slip_id) { 
    $q->where('slip_id', $slip_id); 
})->get();

echo "Deleting " . $cartons->count() . " cartons...\n";
foreach($cartons as $carton) {
    \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
    \App\Models\PackingBox::where('packing_carton_id', $carton->id)->delete();
    $carton->delete();
}

// 2. We only want to restore remaining_quantity for OrderStageTransactions of this order at Stage 11 (Packing)
echo "Recalculating remaining_quantity for all Stage 11 transactions of Order #$order_id...\n";

// Get all Stage 11 transactions for this order
$orderLots = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
$transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11) // Assuming Stage 11 is packing arrival
    ->whereIn('lot_no', $orderLots)
    ->get();

// For each transaction, reset remaining_quantity = quantity
foreach ($transactions as $tx) {
    $tx->remaining_quantity = $tx->quantity;
    $tx->save();
}

// Now re-deduct for all remaining PackingItems of this order!
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

echo "Successfully wiped cartons for slip 1409 and restored true lot quantities!\n";
