<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rolls = App\Models\FabricReceiptDetail::all();
foreach($rolls as $roll) {
    $internal = App\Models\FabricRollAssigning::where('fabric_receipt_detail_id', $roll->id)->sum('meter');
    $agent = App\Models\AgentOrderFabricItem::where('fabric_receipt_detail_id', $roll->id)->whereNotNull('agent_order_dispatch_id')->sum('meter');
    $expected = $roll->meter - $internal - $agent;
    if (abs($roll->remaining_quantity - $expected) > 0.01) {
        echo "Roll {$roll->id} (meter {$roll->meter}): current rem {$roll->remaining_quantity}, expected {$expected} (int {$internal}, ag {$agent})\n";
        
        $roll->remaining_quantity = $expected;
        $roll->save();
    }
}
echo "Done.\n";
