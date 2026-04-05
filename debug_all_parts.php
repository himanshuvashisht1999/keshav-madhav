<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$parts = DB::table('production_slip_digitization_parts')->get();
foreach ($parts as $p) {
    echo "ID: {$p->id}, SlipID: {$p->production_slip_digitization_id}, Lot: {$p->lot_no}, SetQ: {$p->set_quantity}, SingleQ: {$p->single_quantity}\n";
}
