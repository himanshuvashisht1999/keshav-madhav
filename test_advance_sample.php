<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$agent_id = 1;
$isSampleSet = false;

$prices = \DB::table('production_goods_variant_colors')
    ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
    ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', \DB::raw('MAX(mrp) as mrp'))
    ->groupBy('production_goods_id', 'master_size_measurement_id');

$allocated = \DB::table('agent_order_items')
    ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
    ->where('agent_orders.status', 'pending')
    ->select('product_id', 'color_id', 'size_set_id', \DB::raw('SUM(box_qty) as total_allocated'))
    ->groupBy('product_id', 'color_id', 'size_set_id');

$query = \App\Models\DomesticInventory::where('domestic_inventories.status', 1)
    ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
    ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
    ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
    ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
    ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
    ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
    ->leftJoinSub($prices, 'ip', function ($join) {
        $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
            ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
    })
    ->leftJoin('sales_agent_brand_discounts', function ($join) use ($agent_id) {
        $join->on('production_goods.brand_id', '=', 'sales_agent_brand_discounts.brand_id')
            ->where('sales_agent_brand_discounts.sales_agent_id', '=', $agent_id);
    })
    ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
    ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
    ->leftJoinSub($allocated, 'alloc', function ($join) {
        $join->on('domestic_inventories.product_id', '=', 'alloc.product_id')
             ->on('domestic_inventories.color_id', '=', 'alloc.color_id')
             ->on('domestic_inventories.size_set_id', '=', 'alloc.size_set_id');
    })
    ->select(
        'domestic_inventories.product_id',
        'production_goods.name_of_garment',
        \DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes'),
        \DB::raw('COALESCE(MAX(alloc.total_allocated), 0) as total_allocated'),
        \DB::raw('GROUP_CONCAT(storerooms.name) as storerooms'),
        \DB::raw('SUM(CASE WHEN storerooms.name = "ADVANCE SAMPLE" THEN 1 ELSE 0 END) as is_advance')
    )
    ->groupBy(
        'domestic_inventories.product_id',
        'domestic_inventories.color_id',
        'domestic_inventories.size_set_id',
        'production_goods.design_number',
        'production_goods.name_of_garment',
        'master_series.name',
        'master_colors.name',
        'master_size_measurements.name',
        'master_product_fittings.name',
        'master_design_patterns.name',
        \DB::raw('COALESCE(sales_agent_brand_discounts.discount_percentage, 0)')
    )
    ->where('production_goods.name_of_garment', 'LIKE', '%CAMPIN 1%');

print_r($query->get()->toArray());
