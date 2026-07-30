<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$request = Request::create('/agent/orders/340/edit', 'GET');
app()->instance('request', $request);

$id = 340;
$order = \App\Models\AgentOrder::find($id);
$agent_id = $order->sales_agent_id;

$discount_col = 'discount_percentage'; 

$existingItemVariations = \App\Models\AgentOrderItem::where('agent_order_id', $id)
    ->select('product_id', 'color_id', 'size_set_id')
    ->get();

$prices = \DB::table('production_goods_variant_colors')
    ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
    ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', \DB::raw('MAX(mrp) as mrp'))
    ->groupBy('production_goods_id', 'master_size_measurement_id');

$allocated = \DB::table('agent_order_items')
    ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
    ->where('agent_orders.status', 'pending')
    ->where('agent_orders.id', '!=', $id)
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
    });

$query->select(
    'domestic_inventories.product_id',
    'domestic_inventories.color_id',
    'domestic_inventories.size_set_id',
    'production_goods.design_number',
    'production_goods.name_of_garment',
    'master_series.name as series_name',
    'master_colors.name as color_name',
    'master_size_measurements.name as size_set_name',
    'master_product_fittings.name as fitting_name',
    'master_design_patterns.name as pattern_name',
    \DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
    \DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
    \DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
    \DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
);

$query->where(function($q) use ($existingItemVariations) {
    foreach($existingItemVariations as $v) {
        $q->orWhere(function($sq) use ($v) {
            $sq->where('domestic_inventories.product_id', $v->product_id)
               ->where('domestic_inventories.color_id', $v->color_id)
               ->where('domestic_inventories.size_set_id', $v->size_set_id);
        });
    }
});

$boxes = $query->groupBy(
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
->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0');

$settings = \DB::table('settings')->first();
$allowGlobal = true; // Hardcoded to true for test

$boxes = $query->orderBy('production_goods.design_number')
    ->paginate(50)
    ->appends($request->except('page'));

echo "Paginator returned count: " . count($boxes) . "\n";
foreach($boxes as $b) {
    echo $b->product_id . '-' . $b->color_id . '-' . $b->size_set_id . "\n";
}
