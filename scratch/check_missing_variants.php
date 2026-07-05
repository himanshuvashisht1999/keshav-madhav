<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$missing_from_agent_orders = DB::table('agent_order_items')
    ->select('agent_order_items.product_id', 'agent_order_items.product_name', 'agent_order_items.size_set_id', 'agent_order_items.size_set_name')
    ->distinct()
    ->leftJoin('production_goods_variants', function($join) {
        $join->on('agent_order_items.product_id', '=', 'production_goods_variants.production_goods_id')
             ->on('agent_order_items.size_set_id', '=', 'production_goods_variants.master_size_measurement_id');
    })
    ->whereNull('production_goods_variants.id')
    ->get();

$missing_from_inventory = DB::table('domestic_inventories')
    ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
    ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
    ->select('domestic_inventories.product_id', 'master_size_measurements.name as size_set_name', 'domestic_inventories.size_set_id')
    ->where('domestic_inventories.total_boxes', '>', 0)
    ->distinct()
    ->leftJoin('production_goods_variants', function($join) {
        $join->on('domestic_inventories.product_id', '=', 'production_goods_variants.production_goods_id')
             ->on('domestic_inventories.size_set_id', '=', 'production_goods_variants.master_size_measurement_id');
    })
    ->whereNull('production_goods_variants.id')
    ->get();

$all_missing = [];

foreach ($missing_from_agent_orders as $item) {
    if (!$item->product_id || !$item->size_set_id) continue;
    $key = "{$item->product_id}-{$item->size_set_id}";
    $all_missing[$key] = [
        'product_id' => $item->product_id,
        'product_name' => $item->product_name ?? 'Unknown',
        'size_set_id' => $item->size_set_id,
        'size_set_name' => $item->size_set_name ?? 'Unknown',
        'found_in' => ['Agent Orders']
    ];
}

foreach ($missing_from_inventory as $item) {
    if (!$item->product_id || !$item->size_set_id) continue;
    $key = "{$item->product_id}-{$item->size_set_id}";
    if (isset($all_missing[$key])) {
        if (!in_array('Inventory', $all_missing[$key]['found_in'])) {
            $all_missing[$key]['found_in'][] = 'Inventory';
        }
    } else {
        $all_missing[$key] = [
            'product_id' => $item->product_id,
            'product_name' => 'Unknown (Inv)',
            'size_set_id' => $item->size_set_id,
            'size_set_name' => $item->size_set_name ?? 'Unknown',
            'found_in' => ['Inventory']
        ];
    }
}

if (empty($all_missing)) {
    echo "NO_MISSING_DATA\n";
} else {
    echo "MISSING_DATA_FOUND:\n";
    foreach ($all_missing as $item) {
        echo "- Product ID: {$item['product_id']} ({$item['product_name']}) | Size Set ID: {$item['size_set_id']} ({$item['size_set_name']}) | Found in: " . implode(', ', $item['found_in']) . "\n";
    }
}
