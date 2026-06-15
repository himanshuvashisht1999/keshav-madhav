<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
DB::enableQueryLog();
$request = request();
$c = new App\Http\Controllers\Admin\AgentOrderController();
$res = $c->edit(92, $request);
$log = DB::getQueryLog();
foreach($log as $q) {
    if (strpos($q['query'], 'having') !== false) {
        echo $q['query'] . "\n\n";
    }
}
