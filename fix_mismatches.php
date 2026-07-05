<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mismatches = [];
$assignments = App\Models\FabricRollAssigning::with(['fabricReceiptDetail', 'orderProductSet'])->whereNotNull('fabric_receipt_detail_id')->get();
foreach ($assignments as $a) {
    if (!$a->fabricReceiptDetail || !$a->orderProductSet) continue;
    $rollFabric = (string) $a->fabricReceiptDetail->fabric_id;
    
    // Check orderProductSet->fabric_id. It can be a comma-separated string.
    $setFabricsStr = $a->orderProductSet->fabric_id;
    if (!$setFabricsStr) continue;
    $setFabrics = array_map('trim', explode(',', $setFabricsStr));
    
    if (!in_array($rollFabric, $setFabrics)) {
        $mismatches[] = [
            'lot_no' => $a->lot_no,
            'roll_id' => $a->fabric_receipt_detail_id,
            'roll_fabric' => $rollFabric,
            'set_fabrics' => $setFabrics
        ];
        
        // Let's fix it automatically: set the roll's fabric_id to the first fabric_id of the set
        $firstFabric = $setFabrics[0];
        if ($firstFabric) {
            $a->fabricReceiptDetail->update(['fabric_id' => $firstFabric]);
        }
    }
}
echo json_encode($mismatches, JSON_PRETTY_PRINT);
