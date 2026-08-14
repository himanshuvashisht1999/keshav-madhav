<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$outflow = \App\Models\ProductionOutflowInventory::create([
    'type' => 'debit', 
    'quantity' => 5, 
    'order_main_id' => 242, 
    'slip_id' => 1638, 
    'lot_no' => '6438-2', 
    'responsible_unit_id' => 15
]);
(new \App\Services\Admin\PackingService())->deleteOutflow($outflow->id);
echo 'Done. Remaining Qty: ' . \App\Models\OrderStageTransaction::where('id', 3099)->value('remaining_quantity');
