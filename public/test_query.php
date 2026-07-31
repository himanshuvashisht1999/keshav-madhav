<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ass1Query = \App\Models\OrderStageTransaction::with(['from_stage', 'to_stage'])->where('lot_no', '557');
$excludedStages = \App\Models\MasterProductStage::whereIn('name', ['Cutting', 'Printing', 'Embroidery', 'Printing & Embroidery'])->pluck('id')->toArray();
$ass1Query->whereNotIn('to_stage_id', $excludedStages);
echo json_encode($ass1Query->get(['id', 'from_stage_id', 'to_stage_id', 'sub_stage_id_to', 'quantity', 'is_closed_for_unit'])->toArray(), JSON_PRETTY_PRINT);
