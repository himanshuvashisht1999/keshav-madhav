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
    // 1. Fetch from Unified Timing Table
    $timing = \App\Models\OrderLotStageTiming::where('lot_no', $lot_id)
        ->where('master_stage_id', $master_stage)
        ->first();

    // 2. Fetch basic info (Unit, Qty) from original tables (since timing table doesn't store all quantities yet)
    $unitName = '-';
    $totalQuantity = 0;
    $remainingQuantity = 0;

    $isReverse = function($item) {
        if ($item->type == 2) return true;
        if ($item->from_stage_id && $item->to_stage_id) {
            if ($item->from_stage_id >= 4 && $item->from_stage_id <= 12 && 
                $item->to_stage_id >= 4 && $item->to_stage_id <= 12 && 
                $item->from_stage_id > $item->to_stage_id) {
                return true;
            }
        }
        return false;
    };

    if ($master_stage == 3) {
        $records = \App\Models\OrderProductSet::with('stage_master_unit')
            ->where('design_number', $lot_id)
            ->get();
        $unitName = $records->first()?->stage_master_unit?->name ?? '-';
        $totalQuantity = $records->sum('total_quantity');
        $remainingQuantity = $records->sum('remain_total_quantity');
    } elseif ($master_stage == 1) {
        $records = \App\Models\OrderPrintingStageTransaction::with('getToUnitMaster')
            ->where('lot_no', $lot_id)
            ->where('to_stage_id', $master_stage)
            ->get();
        $unitName = $records->first()?->getToUnitMaster?->name ?? '-';
        
        $incomingType1 = $records->filter(function($item) use ($isReverse) { return !$isReverse($item); })->sum('quantity');
        $incomingAll = $records->sum('quantity');
        
        $out1 = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        $out2 = \App\Models\OrderGodamStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        $out3 = \App\Models\OrderPrintingStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        
        $outflowType2 = $out1->filter($isReverse)->sum('quantity') + 
                        $out2->filter($isReverse)->sum('quantity') + 
                        $out3->filter($isReverse)->sum('quantity');
                        
        $outflowAll = $out1->sum('quantity') + $out2->sum('quantity') + $out3->sum('quantity');
        
        $totalQuantity = max(0, $incomingType1 - $outflowType2);
        $remainingQuantity = max(0, $incomingAll - $outflowAll);
    } elseif ($master_stage == 13) {
        $records = \App\Models\OrderGodamStageTransaction::with('getToUnitMaster')
            ->where('lot_no', $lot_id)
            ->where('to_stage_id', $master_stage)
            ->get();
        $unitName = $records->first()?->getToUnitMaster?->name ?? '-';
        
        $incomingType1 = $records->filter(function($item) use ($isReverse) { return !$isReverse($item); })->sum('quantity');
        $incomingAll = $records->sum('quantity');
        
        $out1 = \App\Models\OrderStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        $out2 = \App\Models\OrderGodamStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        
        $outflowType2 = $out1->filter($isReverse)->sum('quantity') + 
                        $out2->filter($isReverse)->sum('quantity');
                        
        $outflowAll = $out1->sum('quantity') + $out2->sum('quantity');
        
        $totalQuantity = max(0, $incomingType1 - $outflowType2);
        $remainingQuantity = max(0, $incomingAll - $outflowAll);
    } else {
        $records = \App\Models\OrderStageTransaction::with('getToUnitMaster')
            ->where('lot_no', $lot_id)
            ->where('to_stage_id', $master_stage)
            ->get();
        $unitName = $records->first()?->getToUnitMaster?->name ?? '-';
        
        $incomingType1 = $records->filter(function($item) use ($isReverse) { return !$isReverse($item); })->sum('quantity');
        $incomingAll = $records->sum('quantity');
        
        $out1 = \App\Models\OrderStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        $out2 = \App\Models\OrderGodamStageTransaction::where('lot_no', $lot_id)->where('from_stage_id', $master_stage)->get();
        
        $outflowType2 = $out1->filter($isReverse)->sum('quantity') + 
                        $out2->filter($isReverse)->sum('quantity');
                        
        $outflowAll = $out1->sum('quantity') + $out2->sum('quantity');
        
        $totalQuantity = max(0, $incomingType1 - $outflowType2);
        $remainingQuantity = max(0, $incomingAll - $outflowAll);
    }

    $timeAllocation = $timing->end_date ?? null;
    $completedTime = $timing->complete_date ?? null;

    // Fallback for Unit Name if timing has it
    if ($unitName === '-' && $timing && $timing->unit_id) {
        $u = \App\Models\StageMasterUnit::find($timing->unit_id);
        $unitName = $u->name ?? '-';
    }

    return [
        'unit_name' => $unitName,
        'quantity' => $totalQuantity,
        'remaining_quantity' => $remainingQuantity,
        'time_allocation' => $timeAllocation,
        'completed_time' => $completedTime,
        'start_date' => $timing->start_date ?? null,
    ];
}

function getLastCurrentStage($lot_no)
{
    $latestStageTiming = \App\Models\OrderLotStageTiming::with('stage')
        ->where('lot_no', $lot_no)
        ->orderBy('id', 'desc')
        ->first();

    return $latestStageTiming ? ($latestStageTiming->stage->name ?? 'N/A') : 'N/A';
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

function parseCompactBarcode($input)
{
    $input = trim($input);
    // Compact barcode format (Legacy): 10 + D(5) + S(2) + C(3) + P(2) + F(2) = 16 digits
    if (strlen($input) == 16 && str_starts_with($input, '10') && is_numeric($input)) {
        $d = (int) substr($input, 2, 5);
        $s = (int) substr($input, 7, 2);
        $c = (int) substr($input, 9, 3);
        $p = (int) substr($input, 12, 2);
        $f = (int) substr($input, 14, 2);
        return "D{$d}S{$s}C{$c}";
    }
    // Compact barcode format (New): 10 + D(5) + S(2) + C(3) = 12 digits
    if (strlen($input) == 12 && str_starts_with($input, '10') && is_numeric($input)) {
        $d = (int) substr($input, 2, 5);
        $s = (int) substr($input, 7, 2);
        $c = (int) substr($input, 9, 3);
        return "D{$d}S{$s}C{$c}";
    }
    return $input;
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
    $items = \App\Models\DomesticInventory::with(['product.series', 'product.fitting', 'product.pattern', 'color', 'sizeSet'])
        ->whereIn('barcode', $barcodeArray)
        ->get()
        ->keyBy('barcode');

    foreach ($barcodeArray as $code) {
        $code = trim($code);
        if (!$code)
            continue;
            
        $compactBarcode = $code;
        if (preg_match('/D(\d+)S(\d+)C(\d+)P(\d+)F(\d+)/', $code, $matches)) {
            $compactBarcode = sprintf("10%05d%02d%03d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
        } elseif (preg_match('/D(\d+)S(\d+)C(\d+)/', $code, $matches)) {
            $compactBarcode = sprintf("10%05d%02d%03d", $matches[1], $matches[2], $matches[3]);
        }

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
                'barcode' => $compactBarcode,
                'original_code' => $code
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
                        'barcode' => $compactBarcode,
                        'original_code' => $code
                    ];
                }
            } elseif (preg_match('/D(\d+)S(\d+)C(\d+)/', $code, $matches)) {
                $design = \App\Models\ProductionGoods::with('series')->find($matches[1]);
                $sizeSet = \App\Models\MasterSizeMeasurement::find($matches[2]);
                $color = \App\Models\MasterColor::find($matches[3]);

                if ($design && $sizeSet && $color) {
                    $labels[] = (object) [
                        'product_name' => trim(($design->series->name ?? '') . ' ' . ($design->name_of_garment ?? '')),
                        'fitting_name' => $design->fitting->name ?? '',
                        'pattern_name' => $design->pattern->name ?? '',
                        'size_group' => $sizeSet->name,
                        'no_of_pcs' => $sizeSet->no_of_pcs,
                        'color_name' => $color->name . ' (' . $color->id . ')',
                        'design_number' => $design->design_number,
                        'barcode' => $compactBarcode,
                        'original_code' => $code
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
            $tspl .= "TEXT 40,190,\"3\",0,2,2,\"{$left->size_group}\"\n";
            $tspl .= "TEXT 40,250,\"3\",0,2,2,\"{$left->no_of_pcs} PCS\"\n";
            $tspl .= "TEXT 40,310,\"2\",0,2,2,\"{$left->color_name}\"\n";
            // $tspl .= "TEXT 40,340,\"2\",0,1,1,\"{$left->pattern_name}\"\n";
            $tspl .= "TEXT 40,370,\"2\",0,2,2,\"{$left->fitting_name}\"\n";
            $tspl .= "TEXT 40,430,\"2\",0,1,1,\"# {$left->design_number}\"\n";
            $tspl .= "BARCODE 20,470,\"128\",140,0,0,2,4,\"{$left->barcode}\"\n";
            $tspl .= "TEXT 40,630,\"2\",0,1,1,\"{$left->original_code}\"\n";
        }

        if ($right) {
            $tspl .= "TEXT 440,110,\"3\",0,2,2,\"{$right->product_name}\"\n";
            $tspl .= "TEXT 440,190,\"3\",0,2,2,\"{$right->size_group}\"\n";
            $tspl .= "TEXT 440,250,\"3\",0,2,2,\"{$right->no_of_pcs} PCS\"\n";
            $tspl .= "TEXT 440,310,\"2\",0,2,2,\"{$right->color_name}\"\n";
            // $tspl .= "TEXT 440,340,\"2\",0,1,1,\"{$right->pattern_name}\"\n";
            $tspl .= "TEXT 440,370,\"2\",0,2,2,\"{$right->fitting_name}\"\n";
            $tspl .= "TEXT 440,430,\"2\",0,1,1,\"# {$right->design_number}\"\n";
            $tspl .= "BARCODE 420,470,\"128\",140,0,0,2,4,\"{$right->barcode}\"\n";
            $tspl .= "TEXT 440,630,\"2\",0,1,1,\"{$right->original_code}\"\n";
        }

        $tspl .= "PRINT 1\n";
    }

    return $tspl;
}

function generateFairBulkTspl($samples)
{
    $labels = [];
    foreach ($samples as $sample) {
        // Calculate WSP
        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $sample->product_id)
            ->where('master_size_measurement_id', $sample->size_set_id)
            ->first();
        $mrp = $variant->mrp ?? 0;
        $final_price = $mrp - ($mrp * ($sample->discount_percent / 100));

        $count = $sample->barcode_count ?? 1;
        for ($i = 0; $i < $count; $i++) {
            $labels[] = (object) [
                'product_name' => trim(($sample->product->series->name ?? '') . ' ' . ($sample->product->name_of_garment ?? '')),
                'fitting_name' => $sample->product->fitting->name ?? '',
                'pattern_name' => $sample->product->pattern->name ?? '',
                'size_group' => $sample->sizeSet->name,
                'no_of_pcs' => $sample->sizeSet->no_of_pcs ?? '',
                'wsp' => 'Rs. ' . number_format($final_price, 2),
                'barcode' => $sample->barcode,
                'url' => route('fair-product.color-chart', ['barcode' => $sample->barcode])
            ];
        }
    }

    if (empty($labels)) return "";

    // Build TSPL Content (100mm x 90mm label, 2 labels per row)
    $tspl = "SIZE 100 mm,90 mm
GAP 2 mm,0
DIRECTION 1
REFERENCE 0,0
SPEED 2
DENSITY 10
CLS
";

    $chunks = array_chunk($labels, 2);
    foreach ($chunks as $pair) {
        $left = $pair[0] ?? null;
        $right = $pair[1] ?? null;

        $tspl .= "CLS\n";

        if ($left) {
            $tspl .= "TEXT 20,110,\"3\",0,2,2,\"{$left->product_name}\"\n";
            // More space after product name, smaller font
            $tspl .= "TEXT 20,200,\"2\",0,2,2,\"{$left->size_group}\"\n";
            $tspl .= "TEXT 20,260,\"2\",0,2,2,\"{$left->fitting_name}\"\n";
            // Increase height of WSP line
            $tspl .= "TEXT 20,320,\"2\",0,2,3,\"{$left->wsp}\"\n";
            // Barcode more bottom
            $tspl .= "BARCODE 20,420,\"128\",120,0,0,3,6,\"{$left->barcode}\"\n";
            // Barcode text under Barcode
            $tspl .= "TEXT 20,560,\"2\",0,1,1,\"{$left->barcode}\"\n";
        }

        if ($right) {
            $tspl .= "TEXT 420,110,\"3\",0,2,2,\"{$right->product_name}\"\n";
            // More space after product name, smaller font
            $tspl .= "TEXT 420,200,\"2\",0,2,2,\"{$right->size_group}\"\n";
            $tspl .= "TEXT 420,260,\"2\",0,2,2,\"{$right->fitting_name}\"\n";
            // Increase height of WSP line
            $tspl .= "TEXT 420,320,\"2\",0,2,3,\"{$right->wsp}\"\n";
            // Barcode more bottom
            $tspl .= "BARCODE 420,420,\"128\",120,0,0,3,6,\"{$right->barcode}\"\n";
            // Barcode text under Barcode
            $tspl .= "TEXT 420,560,\"2\",0,1,1,\"{$right->barcode}\"\n";
        }

        $tspl .= "PRINT 1\n";
    }

    return $tspl;
}

if (!function_exists('send_whatsapp_message')) {
    function send_whatsapp_message($phone, $message)
    {
        // Prevent sending WhatsApp messages when testing locally
        $host = request()->getHost();
        if (in_array($host, ['127.0.0.1', 'localhost', '::1'])) {
            \Illuminate\Support\Facades\Log::info("WhatsApp message simulated on localhost. To: $phone, Message: $message");
            return true;
        }

        // $apiKey can also be moved to .env in future: env('WHATSAPP_API_KEY', '...')
        $apiKey = "14a7d8a2c76144343c3813705bb586a0db28724b888bc838e1";
        $url = "https://app.messageautosender.com/api/v1/message/create";

        $data = [
            "receiverMobileNo" => $phone,
            "message" => [
                $message
            ]
        ];

        $headers = [
            "Content-Type: application/json",
            "x-api-key: " . $apiKey
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            \Illuminate\Support\Facades\Log::error('WhatsApp API Error: ' . $error);
            return false;
        }

        return json_decode($response, true);
    }
}
if (!function_exists('deleteProductionSession')) {
    function deleteProductionSession($type, $id)
    {
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $slip_id = null;

            if ($type == 'lot') {
                $session = \App\Models\OrderLot::findOrFail($id);
                $slip_id = $session->production_slip_digitization_id;

                if (\App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)->exists() || \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)->exists()) {
                    throw new \Exception('Cannot delete Lot. Printing stage has already moved quantity forward.');
                }

                // Revert FabricRollAssigning
                $rolls = \App\Models\FabricRollAssigning::where('order_lot_id', $id)->get();
                foreach ($rolls as $roll) {
                    // Revert FabricReceiptDetail meters
                    $receipt = \App\Models\FabricReceiptDetail::find($roll->fabric_receipt_detail_id);
                    if ($receipt) {
                        $receipt->remaining_quantity += $roll->meter;
                        $receipt->save();
                    }

                    // Revert OrderProductSetDetail quantities
                    $details = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $roll->id)->get();
                    foreach ($details as $detail) {
                        $setDetail = \App\Models\OrderProductSetDetail::where('order_products_set_id', $session->order_products_set_id)
                            ->where('size', $detail->size)
                            ->first();
                        if ($setDetail) {
                            $setDetail->remaining_lot_allocated += $detail->quantity;
                            $setDetail->save();
                        }

                        // Restore OrderCuttingStage remaining_quantity
                        $cs = \App\Models\OrderCuttingStage::where('set_product_id', $session->order_products_set_id)
                            ->where('to_assign_id', $roll->stage_master_unit_id)
                            ->where('status', '!=', 0)
                            ->orderBy('updated_at', 'desc')
                            ->first();
                        if ($cs) {
                            $cs->increment('remaining_quantity', $detail->quantity);
                            $cs->update(['status' => 1]); // Back to partial/assigned status
                        }

                        $detail->delete();
                    }
                    $roll->delete();
                }
                $session->delete();

            } elseif ($type == 'packing') {
                $service = new \App\Services\Admin\PackingService();
                $result = $service->deletePackingSession($id); // In this case $id is slip_id
                if ($result['status'] == 'error') {
                    throw new \Exception($result['message']);
                }
                $slip_id = $id;

            } elseif ($type == 'printing' || $type == 'transfer' || $type == 'printing_stitching' || $type == 'godam') {
                $modelMap = [
                    'printing' => \App\Models\OrderPrintingStageTransaction::class,
                    'transfer' => \App\Models\OrderStageTransaction::class,
                    'printing_stitching' => \App\Models\OrderPrintingToStichingTransaction::class,
                    'godam' => \App\Models\OrderGodamStageTransaction::class
                ];
                $model = $modelMap[$type];
                $session = $model::findOrFail($id);
                $slip_id = $session->production_slip_digitization_id;

                if ($session->remaining_quantity != $session->quantity) {
                    throw new \Exception('Cannot delete. Some quantity has already been moved forward.');
                }

                // Restore Source Quantity
                if ($type == 'printing') {
                    // Source was Cutting (Lot)
                    $otherPrinting = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)
                        ->where('id', '!=', $id)
                        ->exists();
                    if (!$otherPrinting) {
                        \App\Models\OrderLot::where('lot_no', $session->lot_no)->update(['is_printing' => 0]);
                    }
                    \App\Models\FabricRollAssigning::where('lot_no', $session->lot_no)
                        ->where('to_stage_id', 1)
                        ->update(['status' => 1, 'to_stage_id' => null]);
                    
                    // Delete details
                    \App\Models\OrderPrintingStageTransactionDetail::where('order_printing_stage_transaction_id', $id)->delete();

                } elseif ($type == 'printing_stitching') {
                    // Source was Printing
                    $sources = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)
                        ->where('sub_stage_id_to', $session->sub_stage_id)
                        ->get();
                    $qtyToRestore = $session->quantity;
                    foreach($sources as $src) {
                        if ($qtyToRestore <= 0) break;
                        $space = $src->quantity - $src->remaining_quantity;
                        if ($space > 0) {
                            $restoreAmt = min($space, $qtyToRestore);
                            $src->remaining_quantity += $restoreAmt;
                            $src->status = 1; // Mark as active again since quantity was restored
                            $src->save();
                            $qtyToRestore -= $restoreAmt;
                        }
                    }
                    \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $id)->delete();
                    
                } elseif ($type == 'godam') {
                    // Source was Printing or Stitching etc.
                    $sources = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)
                        ->where('to_stage_id', $session->from_stage_id)
                        ->where('sub_stage_id_to', $session->sub_stage_id)
                        ->get();
                    if($sources->isEmpty()) {
                        $sources = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)
                            ->where('to_stage_id', $session->from_stage_id)
                            ->where('sub_stage_id_to', $session->sub_stage_id)
                            ->get();
                    }
                    if($sources->isEmpty()) {
                        $sources = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $session->lot_no)
                            ->where('to_stage_id', $session->from_stage_id)
                            ->where('sub_stage_id_to', $session->sub_stage_id)
                            ->get();
                    }
                    $qtyToRestore = $session->quantity;
                    foreach($sources as $src) {
                        if ($qtyToRestore <= 0) break;
                        $space = $src->quantity - $src->remaining_quantity;
                        if ($space > 0) {
                            $restoreAmt = min($space, $qtyToRestore);
                            $src->remaining_quantity += $restoreAmt;
                            $src->status = 1;
                            $src->save();
                            $qtyToRestore -= $restoreAmt;
                        }
                    }
                    \App\Models\OrderGodamStageTransactionDetail::where('order_godam_stage_transaction_id', $id)->delete();

                } elseif ($type == 'transfer') {
                    // General transfer restore
                    if ($session->from_stage_id == 3 || $session->from_stage_id == 13) {
                        // From Cutting or Godam (Initial Stitching Session)
                        $otherStitching = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)
                            ->where('to_stage_id', 4)
                            ->where('id', '!=', $id)
                            ->exists();
                        if (!$otherStitching) {
                            $otherStitching = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $session->lot_no)
                                ->exists();
                        }
                        if (!$otherStitching) {
                            \App\Models\OrderLot::where('lot_no', $session->lot_no)->update(['is_stitching' => 0]);
                        }
                        \App\Models\FabricRollAssigning::where('lot_no', $session->lot_no)
                            ->where('to_stage_id', $session->to_stage_id)
                            ->update(['status' => 1, 'to_stage_id' => null]);

                        // If it came from Godam, we must restore the Godam transactions proportionally
                        if ($session->from_stage_id == 13) {
                            $godamTxs = \App\Models\OrderGodamStageTransaction::where('lot_no', $session->lot_no)
                                ->where('sub_stage_id_to', $session->sub_stage_id)
                                ->get();
                            
                            $sessionDetails = \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $id)->get();
                            
                            $qtyToRestore = $session->quantity;
                            foreach ($godamTxs as $gTx) {
                                if ($qtyToRestore <= 0) break;
                                $space = $gTx->quantity - $gTx->remaining_quantity;
                                if ($space > 0) {
                                    $restoreAmt = min($space, $qtyToRestore);
                                    $gTx->remaining_quantity += $restoreAmt;
                                    $gTx->status = 1;
                                    $gTx->save();
                                    $qtyToRestore -= $restoreAmt;
                                }
                            }
                            
                            // Restore details size by size
                            foreach ($sessionDetails as $sd) {
                                $detailQtyToRestore = $sd->quantity;
                                $gDetails = \App\Models\OrderGodamStageTransactionDetail::whereIn('order_godam_stage_transaction_id', $godamTxs->pluck('id'))
                                    ->where('size', $sd->size)
                                    ->get();
                                foreach($gDetails as $gd) {
                                    if ($detailQtyToRestore <= 0) break;
                                    $dSpace = $gd->quantity - $gd->remaining_quantity;
                                    if ($dSpace > 0) {
                                        $dRestoreAmt = min($dSpace, $detailQtyToRestore);
                                        $gd->remaining_quantity += $dRestoreAmt;
                                        $gd->save();
                                        $detailQtyToRestore -= $dRestoreAmt;
                                    }
                                }
                            }
                        }
                    } else {
                        // From another stage transaction
                        $sources = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)
                            ->where('to_stage_id', $session->from_stage_id)
                            ->where('sub_stage_id_to', $session->sub_stage_id)
                            ->get();
                        if($sources->isEmpty()) {
                            // Try Printing if not found in StageTransactions
                            $sources = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)
                                ->where('to_stage_id', $session->from_stage_id)
                                ->where('sub_stage_id_to', $session->sub_stage_id)
                                ->get();
                        }
                        if($sources->isEmpty()) {
                            // Try PrintingToStiching for cases where Stitching (from Printing) was the source
                            $sources = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $session->lot_no)
                                ->where('to_stage_id', $session->from_stage_id)
                                ->where('sub_stage_id_to', $session->sub_stage_id)
                                ->get();
                        }

                        $qtyToRestore = $session->quantity;
                        foreach($sources as $src) {
                            if ($qtyToRestore <= 0) break;
                            $space = $src->quantity - $src->remaining_quantity;
                            if ($space > 0) {
                                $restoreAmt = min($space, $qtyToRestore);
                                $src->remaining_quantity += $restoreAmt;
                                $src->status = 1; // Mark as active again since quantity was restored
                                $src->save();
                                $qtyToRestore -= $restoreAmt;
                            }
                        }
                    }
                    \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $id)->delete();
                }

                $session->delete();
            }

            // Reset Digitized Status of Slip
            if ($slip_id) {
                \App\Models\ProductionSlipDigitization::where('id', $slip_id)->update([
                    'status' => 0,
                    'save_type' => 1, // Restore save type
                    'lot_no' => null,
                    'to_stage_id' => null
                ]);
            }

            // Clean up orphaned parts and timings for the deleted session
            if (isset($session) && isset($session->lot_no)) {
                if ($type == 'lot') {
                    \App\Models\ProductionSlipDigitizationParts::where('lot_no', $session->lot_no)->delete();
                    \App\Models\OrderLotStageTiming::where('lot_no', $session->lot_no)->delete();
                } else {
                    if ($slip_id) {
                        \App\Models\ProductionSlipDigitizationParts::where('production_slip_digitization_id', $slip_id)->delete();
                    }
                    if (isset($session->to_stage_id)) {
                        // Check if other transactions exist for this stage
                        $hasOther = false;
                        if ($session->to_stage_id == 1) {
                            $hasOther = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)
                                ->where('id', '!=', $id)->exists();
                        } elseif ($session->to_stage_id == 4) {
                            $hasOther = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)
                                ->where('to_stage_id', 4)->where('id', '!=', ($type == 'transfer' ? $id : 0))->exists();
                            if (!$hasOther) {
                                $hasOther = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $session->lot_no)
                                    ->where('to_stage_id', 4)->where('id', '!=', ($type == 'printing_stitching' ? $id : 0))->exists();
                            }
                        } else {
                            $hasOther = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)
                                ->where('to_stage_id', $session->to_stage_id)->where('id', '!=', ($type == 'transfer' ? $id : 0))->exists();
                        }

                        if (!$hasOther) {
                            \App\Models\OrderLotStageTiming::where('lot_no', $session->lot_no)->where('master_stage_id', $session->to_stage_id)->delete();
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return ['status' => 'success', 'message' => 'Session deleted and quantities restored successfully.'];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return ['status' => 'error', 'message' => 'Error deleting session: ' . $e->getMessage()];
        }
    }
}
