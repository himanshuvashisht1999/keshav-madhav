<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductionGoods;

$product = ProductionGoods::find(68);
print_r($product->toArray());

$variants = DB::table('production_goods_variants')->where('production_goods_id', 68)->get();
print_r($variants->toArray());
