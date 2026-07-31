<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request([
    'view' => 'all',
    'lot_no' => '557'
]);

$service = app(\App\Services\Admin\ReportService::class);
$result = $service->unitAssignments($request);

foreach ($result['assignments'] as $item) {
    if ($item->to_stage) {
        echo "{$item->from_stage->name} -> {$item->to_stage->name} | Qty: {$item->assigned_qty} | Pending: {$item->pending_qty}\n";
    }
}
