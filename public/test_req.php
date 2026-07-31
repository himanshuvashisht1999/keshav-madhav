<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = \Illuminate\Http\Request::create('/?stage_id=&view=all&order_no=&lot_no=557&design_no=&start_date=&end_date=&production_status=');
$res = app(\App\Services\Admin\ReportService::class)->unitAssignments($req);
foreach($res['assignments'] as $item) {
    $from = $item->from_stage->name ?? $item->fromStage->name ?? '';
    $to = $item->to_stage_name ?? $item->to_stage->name ?? $item->toStage->name ?? '';
    echo $from . ' -> ' . $to . ' | ' . $item->assigned_qty . "\n";
}
