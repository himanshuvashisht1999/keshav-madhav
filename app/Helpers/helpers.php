<?php
use App\Models\MasterProductSubStage;
use App\Models\OrderProductStage;
use App\Models\OrderStageTransaction;
use App\Models\ProductStage;
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



?>