<?php

function getformatDateTime($dateString)
{
    return date('d M Y h:i A', strtotime($dateString));
}

function getformatDate($dateString)
{
    return date('d M Y', strtotime($dateString));
}

function getCurrentStage($order_product_id,$from_stage_id){
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', $from_stage_id)->whereNotIn('stage_id',[1,2])->firstOrFail();
    return $data;
}
function getNextStage($order_product_id,$sequence){
    $data = OrderProductStage::where('order_product_id', $order_product_id)->where('sequence', '>', $sequence)->orderBy('sequence', 'asc')->whereNotIn('stage_id',[1,2])->first();
    return $data;
}
function getFirstStage($order_product_id){
    $data = ProductStage::where('master_product_id', $order_product_id)->whereNotIn('stage_id',[1,2])->orderBy('id', 'asc')->first();
    return $data;
}
function getCuttingSubStages(){
    $data = MasterProductSubStage::where('status',1)->where('master_product_stage_id',3)->get();
    return $data;
}



?>