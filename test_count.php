<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = request();
$c = new App\Http\Controllers\Admin\AgentOrderController();
$res = $c->edit(92, $request);
echo count($res->gatherData()['selected_quantities']);
