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
function getCurrentStage($order_product_id,$from_stage_id){
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', $from_stage_id)->first();
    return $data;
}
function getNextStage($order_product_id,$sequence){
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('sequence', '>', $sequence)->orderBy('sequence', 'asc')->whereNotIn('stage_id',[1,2])->first();
    return $data;
}
function getFirstStage($order_product_id){
    $data = ProductStage::where('master_product_id', $order_product_id)->whereNotIn('master_stage_id',[1,2])->orderBy('id', 'asc')->first();
    return $data;
}
function getCuttingSubStages(){
    $data = MasterProductSubStage::where('status',1)->where('master_product_stage_id',3)->get();
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
    if (!$pre_stage->stage_id ) {
        return false;
    }

    if (!in_array($pre_stage->stage_id, [1,2])){
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


function package_box_show($order_main_id){
        $status = 1;

        $total_quantity = 0;
        $order_product_data = OrderProduct::where('order_main_id',$order_main_id)->select('quantity')->get();
        foreach($order_product_data as $single_data){
            $total_quantity = $total_quantity + $single_data->quantity;
        }
        $packaged_items = PackageBox::where('order_main_id',$order_main_id)->select('quantity')->get();
        $total_packed_quantity = 0;
        foreach($packaged_items as $single_data){
            $total_packed_quantity = $total_packed_quantity + $single_data->quantity;
        }
        if($total_packed_quantity == $total_quantity){
            $status = 0;
        }
    return $status;

}

function total_packed_quantity($order_main_id){
    $packaged_items = PackageBox::where('order_main_id',$order_main_id)->select('quantity')->get();
    $total_packed_quantity = 0;
    foreach($packaged_items as $single_data){
        $total_packed_quantity = $total_packed_quantity + $single_data->quantity;
    }
    return $total_packed_quantity;

}
function total_ordered_quantity($order_main_id){
    $total_quantity = 0;
    $order_product_data = OrderProduct::where('order_main_id',$order_main_id)->select('quantity')->get();
    foreach($order_product_data as $single_data){
        $total_quantity = $total_quantity + $single_data->quantity;
    }
    return $total_quantity;
}

function getLotDetailsOld($lot_id,$master_stage){
    if($master_stage == 1){
        $data = OrderPrintingStageTransaction::with('getToUnitMaster')->where('lot_no',$lot_id)->where('to_stage_id',$master_stage)->first();
    }else{
        $data = OrderStageTransaction::with('getToUnitMaster')->where('lot_no',$lot_id)->where('to_stage_id',$master_stage)->first();
    }
    $column_namevar = 'stage_id_'.$master_stage;
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
        'unit_name'           => $unitName,
        'quantity'            => $totalQuantity,
        'remaining_quantity'  => $remainingQuantity,
        'time_allocation'     => $time_allocation,
        'completed_time'      => $completedTime,
    ];
}


function getOrderDispatchData($orderMainId)
{   
    $total = DB::table('order_products_sets')
        ->where('order_main_id', $orderMainId)
        ->sum('total_quantity');

    $pack_items = PackingMain::with([
        'cartons' => function ($q) {
            $q->whereIn('status', [2,3])
            ->withSum('items', 'quantity');
        }
    ])->where('order_main_id', $orderMainId)
    ->first();

    // safe check
    $packed = $pack_items ? $pack_items->cartons->sum('items_sum_quantity') : 0;

    return [
        'total'     => (int) $total,
        'packed'    => (int) $packed,
        'remaining' => max(0, $total - $packed),
    ];
}



function getIndianCurrency($number)
{
    $decimal = round($number - floor($number), 2);
    $money = (string)floor($number);
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
    $decimal_str = ($decimal > 0) ? substr(strrchr((string)($decimal + 1), "."), 1) : '00';
    if(strlen($decimal_str) == 1) $decimal_str .= '0';
    if(empty($decimal_str)) $decimal_str = '00';

    return $result . "." . $decimal_str;
}

?>