<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = request();
$c = new App\Http\Controllers\Admin\AgentOrderController();
$res = $c->edit(92, $request);
print_r(array_slice($res->gatherData()['selected_quantities']->toArray(), 0, 5));
