<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = request();
$order = App\Models\AgentOrder::find(92);
$c = new App\Http\Controllers\Admin\AgentOrderController();
$ref = new ReflectionMethod($c, 'edit');
$ref->invoke($c, 92, $request); // This is just to run the method, but wait I can't easily extract a local variable.
