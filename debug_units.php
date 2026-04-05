<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StageMasterUnit;

$units = StageMasterUnit::whereIn('id', [2, 6])->get();
foreach ($units as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, EmployeeID: {$u->employee_id}, StageID: {$u->master_stage_id}\n";
}
