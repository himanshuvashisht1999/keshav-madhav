<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/reports/stock-pending/export', 'GET', ['stage_id' => '3']);
$controller = $app->make(App\Http\Controllers\Admin\ReportController::class);

$service = $app->make(App\Services\Admin\ReportService::class);
$data = $service->stockPending($request);
echo "Count: " . count($data['assignments']) . "\n";
