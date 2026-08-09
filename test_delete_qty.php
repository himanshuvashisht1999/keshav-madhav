<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$carton = App\Models\PackingCarton::latest()->first();
if (!$carton) {
    echo "No cartons to delete\n";
    exit;
}
echo "Before delete:\n";
echo "Carton ID: {$carton->id}\n";
$order_id = $carton->main->order_main_id;
$orderLots = App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
$transactions = App\Models\OrderStageTransaction::whereIn('lot_no', $orderLots)->get();
echo "Total remaining_quantity before: " . $transactions->sum('remaining_quantity') . "\n";

$svc = app(App\Services\Admin\PackingService::class);
$res = $svc->deleteCarton($carton->id);

$transactions = App\Models\OrderStageTransaction::whereIn('lot_no', $orderLots)->get();
echo "Total remaining_quantity after: " . $transactions->sum('remaining_quantity') . "\n";
