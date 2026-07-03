<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$statuses = \DB::table('agent_orders')->select('status')->distinct()->get();
print_r($statuses);
