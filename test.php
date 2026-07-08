<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = App\Models\OrderMain::where('sku', 'SNA/08/07/2026/287')->first();
$p = App\Models\OrderProductSet::where('order_main_id', $o->id)->with('size_measurement')->first();
echo json_encode($p->toArray(), JSON_PRETTY_PRINT);
