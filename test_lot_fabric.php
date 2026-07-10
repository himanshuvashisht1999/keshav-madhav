<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricRollAssigning;

$assignments = FabricRollAssigning::whereHas('fabricReceiptDetail', function($q){ $q->where('fabric_id', 105); })->with(['orderProductSet', 'fabricReceiptDetail'])->get();
$mismatches = 0;
foreach($assignments as $a) {
   if ($a->orderProductSet && $a->orderProductSet->fabric_id != $a->fabricReceiptDetail->fabric_id) {
       echo "Mismatch! Lot: {$a->lot_no}, Assigned Fabric: {$a->fabricReceiptDetail->fabric_id}, Required Fabric: {$a->orderProductSet->fabric_id}\n";
       $mismatches++;
   }
}
echo "Total fabric mismatches: $mismatches\n";
