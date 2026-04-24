<?php
use App\Models\MasterProductSubStage;
use App\Models\OrderProductStage;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderStageWiseTimeTracking;
use App\Models\ProductStage;
use App\Models\OrderMain;
use App\Models\PackingMain;
use App\Models\PackageBox;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;

function getformatDateTime($dateString)
{
    return date('d M Y h:i A', strtotime($dateString));
}

function getformatDate($dateString)
{
    return date('d M Y', strtotime($dateString));
}

// function getCurrentStage($order_product_id,$from_stage_id){
//     $data = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', $from_stage_id)->whereNotIn('stage_id',[1,2])->first();
//     return $data;
// }
function getCurrentStage($order_product_id, $from_stage_id)
{
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', $from_stage_id)->first();
    return $data;
}
function getNextStage($order_product_id, $sequence)
{
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('sequence', '>', $sequence)->orderBy('sequence', 'asc')->whereNotIn('stage_id', [1, 2])->first();
    return $data;
}
function getFirstStage($order_product_id)
{
    $data = ProductStage::where('master_product_id', $order_product_id)->whereNotIn('master_stage_id', [1, 2])->orderBy('id', 'asc')->first();
    return $data;
}
function getCuttingSubStages()
{
    $data = MasterProductSubStage::where('status', 1)->where('master_product_stage_id', 3)->get();
    return $data;
}

// function getParcialCheck($order_product_id,$to_stage_id){
//     // $data = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', $to_stage_id)->first();
//     // if ($data->sequence){
//     //     $pre_stage = OrderProductStage::where('order_product_id', $order_product_id)->where('sequence', ($data->sequence - 1))->first();
//     //     dd($pre_stage->stage_id);
//     // }

//     $pre_stage = OrderProductStage::where('order_product_id', $order_product_id)
//     ->where('sequence', function ($q) use ($order_product_id, $to_stage_id) {
//         $q->select(DB::raw('sequence - 1'))
//           ->from('order_product_stages')
//           ->where('order_product_id', $order_product_id)
//           ->where('stage_id', $to_stage_id)
//           ->limit(1);
//     })
//     ->first();
//     if ($pre_stage->stage_id){
//         $data = OrderStageTransaction::where('order_product_id', $order_product_id)->whereIn('to_stage_id',[1,2])->orderBy('id', 'asc')->first();
//         if($data && $data->remaining_quantity == 0){
//             return true;
//         }
//     }
//     return false;

// }

function getParcialCheck($order_product_id, $to_stage_id)
{
    // Get previous stage in ONE query
    $pre_stage = OrderProductStage::where('order_product_id', $order_product_id)
        ->where('sequence', function ($q) use ($order_product_id, $to_stage_id) {
            $q->select(DB::raw('sequence - 1'))
                ->from('order_product_stages')
                ->where('order_product_id', $order_product_id)
                ->where('stage_id', $to_stage_id)
                ->limit(1);
        })
        ->first();

    // If no previous stage found → return false
    if (!$pre_stage) {
        return false;
    }

    // Check if stage_id is valid
    if (!$pre_stage->stage_id) {
        return false;
    }

    if (!in_array($pre_stage->stage_id, [1, 2])) {
        return false;
    }
    // Check INCOMING transaction from stage 1 or 2
    $data = OrderStageTransaction::where('order_product_id', $order_product_id)->where('to_stage_id', $pre_stage->stage_id)
        ->orderBy('id', 'asc')
        ->first();

    if ($data && $data->remaining_quantity == 0) {
        return false;
    }
    return true;
}


function package_box_show($order_main_id)
{
    $status = 1;

    $total_quantity = 0;
    $order_product_data = OrderProduct::where('order_main_id', $order_main_id)->select('quantity')->get();
    foreach ($order_product_data as $single_data) {
        $total_quantity = $total_quantity + $single_data->quantity;
    }
    $packaged_items = PackageBox::where('order_main_id', $order_main_id)->select('quantity')->get();
    $total_packed_quantity = 0;
    foreach ($packaged_items as $single_data) {
        $total_packed_quantity = $total_packed_quantity + $single_data->quantity;
    }
    if ($total_packed_quantity == $total_quantity) {
        $status = 0;
    }
    return $status;

}

function total_packed_quantity($order_main_id)
{
    $packaged_items = PackageBox::where('order_main_id', $order_main_id)->select('quantity')->get();
    $total_packed_quantity = 0;
    foreach ($packaged_items as $single_data) {
        $total_packed_quantity = $total_packed_quantity + $single_data->quantity;
    }
    return $total_packed_quantity;

}
function total_ordered_quantity($order_main_id)
{
    $total_quantity = 0;
    $order_product_data = OrderProduct::where('order_main_id', $order_main_id)->select('quantity')->get();
    foreach ($order_product_data as $single_data) {
        $total_quantity = $total_quantity + $single_data->quantity;
    }
    return $total_quantity;
}

function getLotDetailsOld($lot_id, $master_stage)
{
    if ($master_stage == 1) {
        $data = OrderPrintingStageTransaction::with('getToUnitMaster')->where('lot_no', $lot_id)->where('to_stage_id', $master_stage)->first();
    } else {
        $data = OrderStageTransaction::with('getToUnitMaster')->where('lot_no', $lot_id)->where('to_stage_id', $master_stage)->first();
    }
    $column_namevar = 'stage_id_' . $master_stage;
    // $time_allocation = OrderStageWiseTimeTracking::where('lot_no',$lot_id)->value($column_namevar);
    $time_allocation = OrderStageWiseTimeTracking::where('lot_no', $lot_id)
        ->whereNotNull($column_namevar)   // 🔥 THIS IS THE FIX
        ->value($column_namevar);

    $data = [
        'unit_name' => $data?->getToUnitMaster?->name,
        'quantity' => $data?->quantity,
        'remaining_quantity' => $data?->remaining_quantity,
        'time_allocation' => $time_allocation,
        'completed_time' => $data?->updated_at,
    ];
    // dd($data);
    return $data;
}

function getLotDetails($lot_id, $master_stage)
{
    // 🔹 Decide model dynamically
    $model = ($master_stage == 1)
        ? OrderPrintingStageTransaction::class
        : OrderStageTransaction::class;

    // 🔹 Fetch ALL entries for this lot & stage
    $records = $model::with('getToUnitMaster')
        ->where('lot_no', $lot_id)
        ->where('to_stage_id', $master_stage)
        ->get();

    // 🔹 Aggregate data from multiple rows
    $unitName = $records->first()?->getToUnitMaster?->name;

    $totalQuantity = $records->sum('quantity');
    $remainingQuantity = $records->sum('remaining_quantity');

    $completedTime = $records->max('updated_at');

    // 🔹 Dynamic stage column
    $column_namevar = 'stage_id_' . $master_stage;

    $time_allocation = OrderStageWiseTimeTracking::where('lot_no', $lot_id)
        ->whereNotNull($column_namevar)
        ->value($column_namevar);

    return [
        'unit_name' => $unitName,
        'quantity' => $totalQuantity,
        'remaining_quantity' => $remainingQuantity,
        'time_allocation' => $time_allocation,
        'completed_time' => $completedTime,
    ];
}


function getOrderDispatchData($orderMainId)
{
    $total = DB::table('order_products_sets')
        ->where('order_main_id', $orderMainId)
        ->sum('total_quantity');

    $pack_items = PackingMain::with([
        'cartons' => function ($q) {
            $q->whereIn('status', [2, 3])
                ->withSum('items', 'quantity');
        }
    ])->where('order_main_id', $orderMainId)
        ->first();

    // safe check
    $packed = $pack_items ? $pack_items->cartons->sum('items_sum_quantity') : 0;

    return [
        'total' => (int) $total,
        'packed' => (int) $packed,
        'remaining' => max(0, $total - $packed),
    ];
}



function getIndianCurrency($number)
{
    $decimal = round($number - floor($number), 2);
    $money = (string) floor($number);
    $length = strlen($money);
    $delimiter = '';
    $money = strrev($money);

    for ($i = 0; $i < $length; $i++) {
        if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $length) {
            $delimiter .= ',';
        }
        $delimiter .= $money[$i];
    }

    $result = strrev($delimiter);
    $decimal_str = ($decimal > 0) ? substr(strrchr((string) ($decimal + 1), "."), 1) : '00';
    if (strlen($decimal_str) == 1)
        $decimal_str .= '0';
    if (empty($decimal_str))
        $decimal_str = '00';

    return $result . "." . $decimal_str;
}

/**
 * Generates TSPL (TSC Label Printer) content for a list of barcodes.
 * Can find data from DomesticInventory or manually parse barcode structure.
 * 
 * Barcode format example: D35S1C3P2F2
 * 
 * @param array|string $barcodes
 * @return string TSPL Command string
 */
function generateBulkTsplByBarcodes($barcodes)
{
    if (empty($barcodes))
        return "";
    $barcodeArray = is_array($barcodes) ? $barcodes : explode(',', $barcodes);

    $labels = [];

    // 1. Try to find in DomesticInventory first
    $items = \App\Models\DomesticInventory::with(['product.series', 'color', 'fitting', 'pattern', 'sizeSet'])
        ->whereIn('barcode', $barcodeArray)
        ->get()
        ->keyBy('barcode');

    foreach ($barcodeArray as $code) {
        $code = trim($code);
        if (!$code)
            continue;

        if (isset($items[$code])) {
            $item = $items[$code];
            $labels[] = (object) [
                'product_name' => $item->product_name,
                'fitting_name' => $item->fitting_name,
                'pattern_name' => $item->pattern_name,
                'size_group' => $item->size_set_name,
                'no_of_pcs' => $item->quantity,
                'color_name' => $item->color_name . ' (' . $item->color_id . ')',
                'design_number' => $item->design_number,
                'barcode' => $item->barcode
            ];
        } else {
            // 2. Fallback: Parse barcode structure D[id]S[id]C[id]P[id]F[id]
            if (preg_match('/D(\d+)S(\d+)C(\d+)P(\d+)F(\d+)/', $code, $matches)) {
                $design = \App\Models\ProductionGoods::with('series')->find($matches[1]);
                $sizeSet = \App\Models\MasterSizeMeasurement::find($matches[2]);
                $color = \App\Models\MasterColor::find($matches[3]);
                $pattern = \App\Models\MasterDesignPattern::find($matches[4]);
                $fitting = \App\Models\MasterProductFitting::find($matches[5]);

                if ($design && $sizeSet && $color && $pattern && $fitting) {
                    $labels[] = (object) [
                        'product_name' => trim(($design->series->name ?? '') . ' ' . ($design->name_of_garment ?? '')),
                        'fitting_name' => $fitting->name,
                        'pattern_name' => $pattern->name,
                        'size_group' => $sizeSet->name,
                        'no_of_pcs' => $sizeSet->no_of_pcs,
                        'color_name' => $color->name . ' (' . $color->id . ')',
                        'design_number' => $design->design_number,
                        'barcode' => $code
                    ];
                }
            }
        }
    }

    if (empty($labels))
        return "";

    // 3. Build TSPL Content (100mm x 90mm label, 2 labels per row)
    $tspl = "SIZE 100 mm,90 mm
GAP 2 mm,0
DIRECTION 1
REFERENCE 0,0
SPEED 2
DENSITY 15
CLS
";

    $chunks = array_chunk($labels, 2);
    foreach ($chunks as $pair) {
        $left = $pair[0] ?? null;
        $right = $pair[1] ?? null;

        $tspl .= "CLS\n";

        if ($left) {
            $tspl .= "TEXT 40,110,\"3\",0,2,2,\"{$left->product_name}\"\n";
            $tspl .= "TEXT 80,170,\"3\",0,2,2,\"{$left->size_group}\"\n";
            $tspl .= "TEXT 80,230,\"3\",0,2,2,\"{$left->no_of_pcs} PCS\"\n";
            $tspl .= "TEXT 40,290,\"2\",0,1,2,\"{$left->color_name}\"\n";
            $tspl .= "TEXT 40,340,\"2\",0,1,1,\"{$left->pattern_name}\"\n";
            $tspl .= "TEXT 40,380,\"2\",0,1,1,\"{$left->fitting_name}\"\n";
            $tspl .= "TEXT 40,420,\"2\",0,1,1,\"# {$left->design_number}\"\n";
            // $tspl .= "BARCODE 20,480,\"128\",100,1,0,2,3,\"{$left->barcode}\"\n";
            $tspl .= "BARCODE 20,480,\"128\",70,1,0,1,2,\"{$left->barcode}\"\n";
        }

        if ($right) {
            $tspl .= "TEXT 440,110,\"3\",0,2,2,\"{$right->product_name}\"\n";
            $tspl .= "TEXT 480,170,\"3\",0,2,2,\"{$right->size_group}\"\n";
            $tspl .= "TEXT 480,230,\"3\",0,2,2,\"{$right->no_of_pcs} PCS\"\n";
            $tspl .= "TEXT 440,290,\"2\",0,1,2,\"{$right->color_name}\"\n";
            $tspl .= "TEXT 440,340,\"2\",0,1,1,\"{$right->pattern_name}\"\n";
            $tspl .= "TEXT 440,380,\"2\",0,1,1,\"{$right->fitting_name}\"\n";
            $tspl .= "TEXT 440,420,\"2\",0,1,1,\"# {$right->design_number}\"\n";
            // $tspl .= "BARCODE 450,480,\"128\",100,1,0,2,3,\"{$right->barcode}\"\n";
            $tspl .= "BARCODE 450,480,\"128\",70,1,0,1,2,\"{$right->barcode}\"\n";
        }

        $tspl .= "PRINT 1\n";
    }

    return $tspl;
}

?>