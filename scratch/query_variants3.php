<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "All Variants for Product 103 in DB:\n";
$variants = DB::table('production_goods_variants')->where('production_goods_id', 103)->get();
foreach ($variants as $v) {
    echo "ID: {$v->id}, Size Set ID: {$v->master_size_measurement_id}\n";
}

$size19 = DB::table('master_size_measurements')->where('id', 19)->first();
if ($size19) {
    echo "Size 19 is: {$size19->name}\n";
} else {
    echo "Size 19 not found\n";
}
