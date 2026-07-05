<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Agent Order 95 items:\n";
$items = DB::table('agent_order_items')
    ->where('agent_order_id', 95)
    ->where('product_name', 'like', '%JIMMY 3%')
    ->get();

foreach ($items as $item) {
    echo "ID: {$item->id}, Product ID: {$item->product_id}, Name: {$item->product_name}, Size Set ID: {$item->size_set_id}, Size: {$item->size_set_name}\n";
}

echo "\nAll Variants for Product 103 in DB:\n";
$variants = DB::table('production_good_variants')->where('production_goods_id', 103)->get();
foreach ($variants as $v) {
    echo "ID: {$v->id}, Status: {$v->status}, Size Set ID: {$v->master_size_measurement_id}\n";
}

echo "\nAll Sizes:\n";
$sizes = DB::table('master_size_measurements')->whereIn('id', [3, 71, 104, 1, 2, 3])->get();
foreach ($sizes as $s) {
    echo "ID: {$s->id}, Name: {$s->name}\n";
}

