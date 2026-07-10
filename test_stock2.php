<?php
$fabricId = 105;
$warehouseId = 1;
$rolls = App\Models\FabricReceiptDetail::where('fabric_id', $fabricId)->where('master_fabric_warehouse_id', $warehouseId)->get(['id', 'meter', 'remaining_quantity']);
foreach($rolls as $roll) {
    $assigned = App\Models\FabricRollAssigning::where('fabric_receipt_detail_id', $roll->id)->sum('meter');
    $diff = $roll->meter - $roll->remaining_quantity;
    if (abs($assigned - $diff) > 0.01) {
        dump(['roll_id' => $roll->id, 'meter' => $roll->meter, 'remaining' => $roll->remaining_quantity, 'assigned' => (float)$assigned, 'diff' => (float)$diff]);
    }
}
