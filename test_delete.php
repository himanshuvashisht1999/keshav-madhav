<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tx = \App\Models\OrderStageTransaction::create([
    'from_stage_id' => 5,
    'to_stage_id' => 6,
    'sub_stage_id' => 6,
    'sub_stage_id_to' => 43,
    'lot_no' => '534',
    'quantity' => 8,
    'remaining_quantity' => 8,
    'production_datetime' => now(),
    'production_slip_digitization_id' => 1000,
    'status' => 1,
    'type' => 1,
    'start_date' => now(),
    'end_date' => now()
]);

$src = \App\Models\OrderStageTransaction::find(223);
$src->remaining_quantity -= 8;
$src->save();

echo "Created TX: " . $tx->id . " | Src Rem: " . $src->remaining_quantity . "\n";

deleteProductionSession('transfer', $tx->id);

$src->refresh();
echo "After Delete Src Rem: " . $src->remaining_quantity . "\n";
