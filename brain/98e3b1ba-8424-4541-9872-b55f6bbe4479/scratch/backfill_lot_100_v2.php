<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderCuttingStage;
use App\Models\StageMasterUnit;
use App\Models\OrderLot;

$orderLot = OrderLot::where('lot_no', '100')->first();
if ($orderLot) {
    $lot = OrderCuttingStage::where('set_product_id', $orderLot->order_products_set_id)->orderBy('id', 'desc')->first();
    if ($lot) {
        $lot->start_date = $lot->created_at;
        $unit = StageMasterUnit::find($lot->to_assign_id);
        $days = $unit->lot_time_in_days ?? 0;
        if ($days > 0) {
            $lot->end_date = $lot->created_at->addDays($days);
        }
        if ($lot->is_closed_for_unit == 1) {
            $lot->complete_date = $lot->updated_at;
        }
        $lot->save();
        echo "Updated Cutting Stage for Lot 100\n";
    } else {
        echo "Cutting Stage record not found for set " . $orderLot->order_products_set_id . "\n";
    }
} else {
    echo "Lot 100 not found in order_lots\n";
}
