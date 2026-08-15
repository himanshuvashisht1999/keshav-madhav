<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

$selected_lots = DB::table('packing_selected_lots')->where('slip_id', 1640)->pluck('lot_no')->toArray();
$selected_lots = DB::table('packing_selected_lots')->where('slip_id', 1640)->pluck('lot_no')->toArray();
$lots_data = DB::table('order_stage_transactions')
    ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
    ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
    ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
    ->leftJoin('master_colors', 'order_products_sets.color_id', '=', 'master_colors.id')
    ->where('order_stage_transactions.to_stage_id', 11)
    ->whereIn('order_stage_transactions.lot_no', $selected_lots)
    ->select(
        'order_stage_transactions.id as transaction_id',
        'order_stage_transactions.lot_no',
        'order_products_sets.id as set_id',
        'order_products_sets.design_number',
        'master_size_measurements.name as size_set_name',
        'order_products_sets.color_id',
        'master_colors.name as color_name',
        'order_stage_transactions.quantity',
        'order_stage_transactions.remaining_quantity',
        'order_products_sets.set_quantity as set_total_qty',
        'order_products_sets.set_size as master_size_set_id'
    )
    ->get();

$set_ids = $lots_data->pluck('set_id')->unique()->toArray();
$set_details = \App\Models\OrderProductSetDetail::whereIn('order_products_set_id', $set_ids)->get()->groupBy('order_products_set_id');

$packed_by_lot_size = \App\Models\PackingItem::join('order_products_set_details', 'packing_items.size_id', '=', 'order_products_set_details.id')
    ->whereIn('packing_items.lot_no', $selected_lots)
    ->where('packing_items.packing_main_id', 887)
    ->select('packing_items.lot_no', 'order_products_set_details.size', DB::raw('SUM(packing_items.quantity) as total'))
    ->groupBy('packing_items.lot_no', 'order_products_set_details.size')
    ->get()
    ->map(function($item) {
        $item->size = trim(strtoupper($item->size));
        return $item;
    })
    ->groupBy('lot_no');

$rework_by_lot_size = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transaction_details.order_stage_transaction_id', '=', 'order_stage_transactions.id')
    ->whereIn('order_stage_transactions.lot_no', $selected_lots)
    ->where('order_stage_transactions.production_slip_digitization_id', 1640)
    ->where('order_stage_transactions.from_stage_id', 11)
    ->where('order_stage_transactions.type', 'rework')
    ->select('order_stage_transactions.lot_no', 'order_stage_transaction_details.size', DB::raw('SUM(order_stage_transaction_details.quantity) as total'))
    ->groupBy('order_stage_transactions.lot_no', 'order_stage_transaction_details.size')
    ->get()
    ->groupBy('lot_no');

$outflow_by_lot_size = \App\Models\ProductionOutflowInventory::join('order_products_set_details', 'production_outflow_inventories.size_id', '=', 'order_products_set_details.id')
    ->whereIn('production_outflow_inventories.lot_no', $selected_lots)
    ->where('production_outflow_inventories.slip_id', 1640)
    ->select('production_outflow_inventories.lot_no', 'order_products_set_details.size', DB::raw('SUM(production_outflow_inventories.quantity) as total'))
    ->groupBy('production_outflow_inventories.lot_no', 'order_products_set_details.size')
    ->get()
    ->map(function($item) {
        $item->size = trim(strtoupper($item->size));
        return $item;
    })
    ->groupBy('lot_no');

echo "=== calculation details ===\n";
foreach ($lots_data as $lot) {
    if (isset($set_details[$lot->set_id])) {
        $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
        $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
        $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
        $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
        $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
        
        echo "Lot: {$lot->lot_no} (Txn: {$lot->transaction_id})\n";
        echo "  remaining_quantity: {$lot->remaining_quantity}\n";
        echo "  packed: {$packed_for_lot}, rework: {$rework_for_lot}, outflow: {$outflow_for_lot}\n";
        echo "  starting_lot_qty: {$starting_lot_qty}\n";
        
        foreach ($set_details[$lot->set_id] as $detail) {
            $sizeName = trim(strtoupper($detail->size));
            $original_size_qty = $total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0;
            
            $packed_qty = 0;
            if (isset($packed_by_lot_size[$lot->lot_no])) {
                $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                if ($item) $packed_qty = $item->total;
            }
            $rework_qty = 0;
            if (isset($rework_by_lot_size[$lot->lot_no])) {
                $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                if ($item) $rework_qty = $item->total;
            }
            $outflow_qty = 0;
            if (isset($outflow_by_lot_size[$lot->lot_no])) {
                $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                if ($item) $outflow_qty = $item->total;
            }
            
            $live = max(0, $original_size_qty - $packed_qty - $rework_qty - $outflow_qty);
            echo "    Size: {$sizeName} => original: {$original_size_qty}, packed: {$packed_qty}, live: {$live}\n";
        }
    }
}
