<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = App\Models\PackingItem::whereIn('lot_no', ['6446', '6446-2', '6447', '6447-2'])->get();
echo "Packing items found: " . $items->count() . "\n";
foreach ($items as $it) {
    echo "ID: {$it->id}, main_id: {$it->packing_main_id}, carton_id: {$it->packing_carton_id}, lot_no: {$it->lot_no}, qty: {$it->quantity}\n";
}

$pm = App\Models\PackingMain::where('order_main_id', 131)->get();
echo "\nPacking Mains for Order 131:\n";
foreach ($pm as $p) {
    echo "ID: {$p->id}, slip_id: {$p->slip_id}, status: {$p->status}\n";
    $cartons = App\Models\PackingCarton::where('packing_main_id', $p->id)->get();
    echo "  Cartons: " . $cartons->count() . "\n";
}
