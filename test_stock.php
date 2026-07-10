<?php
$fabricId = 105;
$warehouseId = 1;
$rollIds = App\Models\FabricReceiptDetail::where('fabric_id', $fabricId)->where('master_fabric_warehouse_id', $warehouseId)->pluck('id');
$usagesSum = App\Models\FabricRollAssigning::whereIn('fabric_receipt_detail_id', $rollIds)->sum('meter');
$agentUsagesSum = App\Models\AgentOrderFabricItem::whereHas('roll', function ($q) use ($warehouseId) {
    $q->where('master_fabric_warehouse_id', $warehouseId);
})->where('fabric_id', $fabricId)->whereNotNull('agent_order_dispatch_id')->sum('meter');
$receiptSumIssued = App\Models\FabricReceiptDetail::where('fabric_id', $fabricId)->where('master_fabric_warehouse_id', $warehouseId)->sum(\DB::raw('meter - remaining_quantity'));
dump(['internal_usages' => (float)$usagesSum, 'agent_usages' => (float)$agentUsagesSum, 'total_usages' => (float)$usagesSum + (float)$agentUsagesSum, 'receipts_sum_issued' => (float)$receiptSumIssued]);
