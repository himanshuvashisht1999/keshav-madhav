<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionSlipDigitization;
use App\Models\OrderLot;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderStageTransaction;

$slipId = 57;
$slip = ProductionSlipDigitization::find($slipId);

if (!$slip) {
    echo "Slip $slipId not found\n";
    exit;
}

echo "Slip #$slipId\n";
echo "Main OrderProductSet Name: " . ($slip->orderProductSet?->size_set_name ?? 'N/A') . "\n";

$all_sizes = collect();

// Cutting
foreach($slip->orderLots as $lot) {
    echo "Cutting Session: Lot #{$lot->lot_no}, OrderProductSet Name: " . ($lot->orderProductSet?->size_set_name ?? 'N/A') . "\n";
    $rolls = \App\Models\FabricRollAssigning::where('order_lot_id', $lot->id)->with('fabricRollAssigningsDetail')->get();
    foreach($rolls as $r) {
        foreach($r->fabricRollAssigningsDetail as $d) {
            $all_sizes->push($d->size);
        }
    }
}

// Printing
foreach($slip->orderPrintingStageTransaction as $opt) {
    echo "Printing Session: Lot #{$opt->lot_no}, OrderProductSet Name: " . ($opt->orderProduct?->orderProductSet?->size_set_name ?? 'N/A') . "\n";
    foreach($opt->details as $d) {
        $all_sizes->push($d->size);
    }
}

// Transfer
foreach($slip->orderStageTransaction as $ost) {
    echo "Transfer Session: Lot #{$ost->lot_no}, OrderProductSet Name: " . ($ost->orderProduct?->orderProductSet?->size_set_name ?? 'N/A') . "\n";
    foreach($ost->details as $d) {
        $all_sizes->push($d->size);
    }
}

$all_sizes = $all_sizes->unique()->filter()->values();
echo "Actual Sizes in sessions: " . $all_sizes->join(', ') . "\n";
if ($all_sizes->isNotEmpty()) {
    echo "Admin Style Range: " . $all_sizes->min() . "-" . $all_sizes->max() . "\n";
}
