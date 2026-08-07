<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = DB::table('production_goods')->where('design_number', '91644')->first(); 
$color = DB::table('master_colors')->where('name', 'GREY')->first(); 
$size = DB::table('master_size_measurements')->where('name', '22*30')->first(); 

echo "Product: " . ($product->id ?? 'null') . "\n";
echo "Color: " . ($color->id ?? 'null') . "\n";
echo "Size: " . ($size->id ?? 'null') . "\n";

$inv = DB::table('domestic_inventories')
        ->where('product_id', $product->id ?? 0)
        ->where('color_id', $color->id ?? 0)
        ->where('size_set_id', $size->id ?? 0)
        ->get();
        
foreach ($inv as $i) {
    echo "Inventory ID: {$i->id}, Rack ID: {$i->rack_id}, Total Boxes: {$i->total_boxes}\n";
}

$allocated_qty = DB::table('agent_order_items')
    ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
    ->where('agent_orders.status', 'pending')
    // ->where('agent_order_items.id', '!=', $item->id)
    ->where('agent_order_items.product_id', $product->id ?? 0)
    ->where('agent_order_items.color_id', $color->id ?? 0)
    ->where('agent_order_items.size_set_id', $size->id ?? 0)
    ->sum('agent_order_items.box_qty') ?? 0;
    
echo "Allocated: $allocated_qty\n";
