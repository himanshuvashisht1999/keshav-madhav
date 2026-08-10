<?php
$c = file_get_contents('app/Services/Admin/PackingService.php');

$new_method = <<<'EOD'
    public function getAvailableQuantitiesAtUnitPerLot($order_id, $unit_id)
    {
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)
            ->pluck('lot_no')
            ->toArray();

        if (empty($lots)) {
            return [];
        }

        // 1. Sum incoming quantities
        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('tx.lot_no', 'det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('tx.lot_no', 'det.size')
            ->get();

        // 2. Subtract outgoing quantities
        $outgoing = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.from_stage_id', 11)
            ->where('tx.sub_stage_id', $unit_id)
            ->where('tx.status', '>=', 0)
            ->select('tx.lot_no', 'det.size', DB::raw('SUM(det.quantity) as total_outgoing'))
            ->groupBy('tx.lot_no', 'det.size')
            ->get();

        // 3. Sum Corporate Packed
        $corporatePacked = DB::table('packing_items as pi')
            ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
            ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
            ->where('pm.order_main_id', $order_id)
            ->where('psd.stage_master_unit_id', $unit_id)
            ->whereNotNull('pi.lot_no')
            ->select('pi.lot_no', 'pi.size_id', DB::raw('SUM(pi.quantity) as total_packed'))
            ->groupBy('pi.lot_no', 'pi.size_id')
            ->get();

        // 4. Sum Domestic Packed
        $domesticPacked = DB::table('production_outflow_inventories')
            ->where('order_main_id', $order_id)
            ->where('responsible_unit_id', $unit_id)
            ->where('type', 'packing')
            ->whereNotNull('lot_no')
            ->select('lot_no', 'size_id', DB::raw('SUM(quantity) as total_packed'))
            ->groupBy('lot_no', 'size_id')
            ->get();

        // Group everything together
        $inMap = [];
        foreach($incoming as $row) {
            $inMap[$row->lot_no][$row->size] = $row->total_incoming;
        }

        $outMap = [];
        foreach($outgoing as $row) {
            $outMap[$row->lot_no][$row->size] = $row->total_outgoing;
        }

        $corpMap = [];
        foreach($corporatePacked as $row) {
            $corpMap[$row->lot_no][$row->size_id] = $row->total_packed;
        }

        $domMap = [];
        foreach($domesticPacked as $row) {
            $domMap[$row->lot_no][$row->size_id] = $row->total_packed;
        }

        // Output Structure: ['LOT-123' => [detail_id => qty]]
        $availablePerLot = [];
        
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($lots as $lot) {
            $availablePerLot[$lot] = [];
            foreach ($details as $detail) {
                $inQty = $inMap[$lot][$detail->size] ?? 0;
                $outQty = $outMap[$lot][$detail->size] ?? 0;
                $corpQty = $corpMap[$lot][$detail->id] ?? 0;
                $domQty = $domMap[$lot][$detail->id] ?? 0;

                $avail = (int) max(0, $inQty - $outQty - $corpQty - $domQty);
                $availablePerLot[$lot][$detail->id] = $avail;
            }
        }

        return $availablePerLot;
    }
EOD;

$c = str_replace('public function getIncomingQuantitiesAtUnit($order_id, $unit_id)', $new_method . "\n\n    public function getIncomingQuantitiesAtUnit(\$order_id, \$unit_id)", $c);
file_put_contents('app/Services/Admin/PackingService.php', $c);
echo "Added per-lot method";
