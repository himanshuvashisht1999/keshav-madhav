<?php
require 'C:/xampp/htdocs/keshav-madhav/vendor/autoload.php';
$app = require_once 'C:/xampp/htdocs/keshav-madhav/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$r = DB::table('racks')->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')->where('racks.id', 64)->select('racks.*', 'storerooms.name as storeroom_name', 'storerooms.order_dispatch', 'storerooms.order_taken')->first();
print_r($r);
