<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderLot;
use App\Models\OrderCuttingStage;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderGodamStageTransaction;
use App\Models\OrderLotStageTiming;
use App\Models\MasterStageWiseTimeAllocation;
use Illuminate\Support\Facades\DB;

echo "Starting Timing Data Migration...\n";

$lots = OrderLot::all();

foreach ($lots as $lot) {
    echo "Processing Lot: {$lot->lot_no}\n";

    // Get stage days from Master Allocation if exists
    $masterDays = MasterStageWiseTimeAllocation::where('lot_no', $lot->lot_no)->first();

    // 1. Cutting (Stage 3)
    $cutting = OrderCuttingStage::where('set_product_id', $lot->order_products_set_id)->orderBy('id', 'desc')->first();
    if ($cutting) {
        OrderLotStageTiming::updateOrCreate(
            ['lot_no' => $lot->lot_no, 'master_stage_id' => 3],
            [
                'order_lot_id' => $lot->id,
                'unit_id' => $cutting->to_assign_id,
                'days_allocated' => $masterDays->stage_id_3 ?? 0,
                'start_date' => $cutting->start_date ?: $cutting->created_at,
                'end_date' => $cutting->end_date,
                'complete_date' => $cutting->complete_date,
                'status' => $cutting->is_closed_for_unit ? 2 : 1
            ]
        );
    }

    // 2. Other Stages (Transactions)
    $stages = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    foreach ($stages as $stageId) {
        $record = null;
        if ($stageId == 1) {
            $record = OrderPrintingStageTransaction::where('lot_no', $lot->lot_no)->where('to_stage_id', $stageId)->orderBy('id', 'desc')->first();
        } elseif ($stageId == 4) {
            $record = OrderPrintingToStichingTransaction::where('lot_no', $lot->lot_no)->where('to_stage_id', $stageId)->orderBy('id', 'desc')->first()
                      ?? OrderStageTransaction::where('lot_no', $lot->lot_no)->where('to_stage_id', 4)->orderBy('id', 'desc')->first();
        } elseif ($stageId == 12) {
            $record = OrderGodamStageTransaction::where('lot_no', $lot->lot_no)->where('to_stage_id', $stageId)->orderBy('id', 'desc')->first();
        } else {
            $record = OrderStageTransaction::where('lot_no', $lot->lot_no)->where('to_stage_id', $stageId)->orderBy('id', 'desc')->first();
        }

        if ($record) {
            $unitId = ($stageId == 1) ? $record->sub_stage_id_to : (($stageId == 4 && isset($record->sub_stage_id_to)) ? $record->sub_stage_id_to : (isset($record->to_unit_id) ? $record->to_unit_id : (isset($record->sub_stage_id_to) ? $record->sub_stage_id_to : null)));
            
            // Manual adjustment if unit ID not found directly
            if (!$unitId && method_exists($record, 'getToUnitMaster')) {
                 $unitId = $record->sub_stage_id_to ?? null;
            }

            OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lot->lot_no, 'master_stage_id' => $stageId],
                [
                    'order_lot_id' => $lot->id,
                    'unit_id' => $unitId,
                    'days_allocated' => $masterDays->{'stage_id_'.$stageId} ?? 0,
                    'start_date' => $record->start_date ?: $record->created_at,
                    'end_date' => $record->end_date,
                    'complete_date' => $record->complete_date,
                    'status' => $record->is_closed_for_unit ? 2 : 1
                ]
            );
        }
    }
}

echo "Migration Completed Successfully!\n";
