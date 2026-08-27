<?php

namespace App\Services\Admin;

use App\Models\ProductionSlipDigitization;
use App\Models\PackingMain;
use App\Models\PackingCarton;

use App\Models\PackingItem;
use App\Models\OrderMain;
use App\Models\OrderStageTransaction;
use App\Models\OrderStageTransactionDetail;
use App\Models\OrderLot;
use App\Models\DomesticInventory;

use Illuminate\Support\Facades\DB;

class PackingService
{
    public function getPackingList()
    {
        return PackingMain::with(['order.customer'])
            ->where('slip_id', '!=', 0)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function indexList($request)
    {
        // List individual Packing Sessions (Slips)
        $query = PackingMain::with(['order.customer'])->where('slip_id', '!=', 0);

        // Filter by Order No (SKU)
        if ($request->has('order_no') && !empty($request->order_no)) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            });
        }

        // Filter by Customer Name
        if ($request->has('customer_name') && !empty($request->customer_name)) {
            $query->whereHas('order.customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Filter by Date Range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('packing_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('packing_date', '<=', $request->end_date);
        }

        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('order_no', function ($row) {
                return $row->order->sku ?? 'N/A';
            })
            ->addColumn('customer', function ($row) {
                return $row->order->customer->name ?? 'N/A';
            })
            ->addColumn('slip_id', function ($row) {
                return '#' . $row->slip_id;
            })
            ->addColumn('packing_date', function ($row) {
                return $row->packing_date ? date('d/m/Y', strtotime($row->packing_date)) : 'N/A';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Finalized</span>';
                }
                return '<span class="badge badge-warning">In-Progress</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('admin.packing.view', $row->id) . '" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i> View Details</a>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

        public function getPackingDetailsForOrder($order_id)
    {
        return OrderMain::with([
            'customer',
            'packingMains.cartons.items.detail.orderProductSet.product',
            'packingMains.cartons.items.detail.orderProductSet.colors',
            'packingMains.cartons.items.detail.orderProductSet.size_measurement',
            'packingMains.cartons.rack.storeroom'
        ])->findOrFail($order_id);
    }

    public function getPackingSessionDetails($id)
    {
        $session = PackingMain::with([
            'order.customer',
            'cartons.items.detail.orderProductSet.product',
            'cartons.items.detail.orderProductSet.colors',
            'cartons.items.detail.orderProductSet.size_measurement',
            'cartons.rack.storeroom',
            'outflows.product',
            'outflows.color',
            'outflows.size',
            'outflows.responsibleUnit',
            'domesticInventories.product',
            'domesticInventories.sizeSet',
            'domesticInventories.color',
            'domesticInventories.rack.storeroom'
        ])->findOrFail($id);

        $reworks = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $session->slip_id)
            ->where('from_stage_id', 11)
            ->where(function($q) {
                $q->where('type', 'rework')->orWhere('type', 0);
            })
            ->with(['toStage', 'toUnit', 'details'])
            ->get();

        $lotNos = $reworks->pluck('lot_no')->unique()->toArray();
        $lots = \App\Models\OrderLot::whereIn('lot_no', $lotNos)
            ->with(['orderProductSet.product', 'orderProductSet.colors', 'orderProductSet.size_measurement'])
            ->get()
            ->keyBy('lot_no');

        foreach ($reworks as $rw) {
            $rw->lot_info = $lots->get($rw->lot_no);
        }

        $session->setRelation('reworks', $reworks);

        return $session;
    }
    /**
     * Get pending packing slips (Stage 11)
     */
    public function getPendingSlips()
    {
        // Check if already packed?
        // Maybe we need a status in ProductionSlipDigitization or just check if it exists in PackingMain

        return ProductionSlipDigitization::where('from_stage_id', 11) // Packing Stage
            ->whereDoesntHave('packingMain') // Assuming we add relation to ProductionSlipDigitization
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getSlipDetails($id)
    {
        return ProductionSlipDigitization::findOrFail($id);
    }

    public function getOrderDetails($sku)
    {
        return OrderMain::where('sku', $sku)
            ->with(['OrderProductSets.product_set_details', 'OrderProductSets.colors', 'OrderProductSets.size_measurement'])
            ->first();
    }

    public function getOrCreatePackingMain($slip_id, $order_id)
    {
        $packing = PackingMain::where('slip_id', $slip_id)->first();
        if (!$packing) {
            $packing = PackingMain::create([
                'slip_id' => $slip_id,
                'order_main_id' => $order_id,
                'packing_date' => now(),
                'status' => 0 // Draft
            ]);
        } elseif (!$packing->order_main_id && $order_id) {
            $packing->order_main_id = $order_id;
            $packing->save();
        }
        return $packing;
    }

        public function getPackingMainWithStructure($slip_id)
    {
        return PackingMain::where('slip_id', $slip_id)
            ->with([
                'cartons.items.detail.orderProductSet.product',
                'cartons.items.detail.orderProductSet.colors',
                'cartons.items.detail.orderProductSet.size_measurement'
            ])
            ->first();
    }

    public function getAvailableQuantitiesAtUnit($order_id, $unit_id)
    {
        // 1. Get all lot numbers for this order to trace pieces (including Pending status 0)
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)
            ->pluck('lot_no')
            ->toArray();

        if (empty($lots)) {
            return [];
        }

        // 2. Sum incoming quantities from OrderStageTransaction to this specific unit (Including Status 0)
        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('det.size')
            ->pluck('total_incoming', 'det.size')
            ->toArray();

        // 2.1 Subtract outgoing quantities (ALL Transfers Out - including outflows)
        $outgoing = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.from_stage_id', 11) // From Packing
            ->where('tx.sub_stage_id', $unit_id)
            ->where('tx.status', '>=', 0) // ALL moves out
            ->select('det.size', DB::raw('SUM(det.quantity) as total_outgoing'))
            ->groupBy('det.size')
            ->pluck('total_outgoing', 'det.size')
            ->toArray();

        // 3. Sum already packed quantities (Corporate via packing_items)
        $corporatePacked = DB::table('packing_items as pi')
            ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
            ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
            ->where('pm.order_main_id', $order_id)
            ->where('psd.stage_master_unit_id', $unit_id)
            ->select('pi.size_id', DB::raw('SUM(pi.quantity) as total_packed'))
            ->groupBy('pi.size_id')
            ->pluck('total_packed', 'pi.size_id')
            ->toArray();

        // 3. Sum Domestic Packed ONLY (via ProductionOutflowInventory type='packing')
        // This is necessary because domestic packing does NOT create a mirrored transaction.
        $domesticPacked = DB::table('production_outflow_inventories')
            ->where('order_main_id', $order_id)
            ->where('responsible_unit_id', $unit_id)
            ->where('type', 'packing')
            ->select('size_id', DB::raw('SUM(quantity) as total_packed'))
            ->groupBy('size_id')
            ->pluck('total_packed', 'size_id')
            ->toArray();

        // 4. Map size names and calculate availability
        $available = [];
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($details as $detail) {
            $inQty = $incoming[$detail->size] ?? 0;
            $outQty = $outgoing[$detail->size] ?? 0;
            $corpQty = $corporatePacked[$detail->id] ?? 0;
            $domQty = $domesticPacked[$detail->id] ?? 0;

            // Available = [In] - [Out (Mirror)] - [Packed Corporate] - [Packed Domestic (Log)]
            $available[$detail->id] = (int) max(0, $inQty - $outQty - $corpQty - $domQty);
        }

        return $available;
    }

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

    public function getIncomingQuantitiesAtUnit($order_id, $unit_id)
    {
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
        if (empty($lots)) return [];

        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('det.size')
            ->pluck('total_incoming', 'det.size')
            ->toArray();

        $incoming_mapped = [];
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($details as $detail) {
            $incoming_mapped[$detail->id] = (int) ($incoming[$detail->size] ?? 0);
        }

        return $incoming_mapped;
    }

    public function bulkSaveCarton($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $boxes_per_carton = (int) ($data['boxes_per_carton'] ?? 1);
            $global_rack_id = $data['rack_id'] ?? null;
            $set_racks = $data['set_racks'] ?? []; // [set_id => rack_id]
            $packed_quantities = $this->getPackedQuantitiesForOrder($data['order_id']);

            // Get unit details for validation
            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            // Auto-generate start carton sequence
            $max_carton = PackingCarton::selectRaw('MAX(CAST(carton_no AS UNSIGNED)) as max_no')->value('max_no');
            $next_available_no = ($max_carton ?: 0) + 1;

            // box_plan: [[ 'composition' => [ {id, qty}, ... ], 'rack_id' => ... ], ...]
            $box_plan = [];

            if (isset($data['mode']) && $data['mode'] == 'full_loose') {
                // Scenario 3: Full Order Loose (1 piece = 1 box)
                $order = \App\Models\OrderMain::with('OrderProductSets.product_set_details')->find($data['order_id']);
                foreach ($order->OrderProductSets as $set) {
                    foreach ($set->product_set_details as $detail) {
                        $remaining = $detail->total_quantity - ($packed_quantities[$detail->id] ?? 0);
                        for ($i = 0; $i < $remaining; $i++) {
                            $box_plan[] = [
                                'composition' => [['id' => $detail->id, 'qty' => 1]],
                                'rack_id' => $global_rack_id
                            ];
                        }
                    }
                }
            } elseif (isset($data['mode']) && $data['mode'] == 'full_sets') {
                // Scenario 4: Full Order Sets
                $order = \App\Models\OrderMain::with('OrderProductSets.product_set_details')->find($data['order_id']);
                foreach ($order->OrderProductSets as $set) {
                    // Logic to find how many full sets remain for this set
                    $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                    $min_remaining_sets = null;
                    $composition = []; // [{id, qty_per_box}]

                    foreach ($set->product_set_details as $detail) {
                        $remaining = $detail->total_quantity - ($packed_quantities[$detail->id] ?? 0);
                        $qty_per_set = $detail->total_quantity / $set_total_qty;
                        $composition[] = ['id' => $detail->id, 'qty' => $qty_per_set];

                        if ($qty_per_set > 0) {
                            $rem_sets = floor($remaining / $qty_per_set);
                            if ($min_remaining_sets === null || $rem_sets < $min_remaining_sets) {
                                $min_remaining_sets = $rem_sets;
                            }
                        }
                    }

                    $set_rack_id = $set_racks[$set->id] ?? $global_rack_id;
                    for ($i = 0; $i < $min_remaining_sets; $i++) {
                        $box_plan[] = [
                            'composition' => $composition,
                            'rack_id' => $set_rack_id
                        ];
                    }
                }
            } elseif (!empty($data['items'])) {
                // Scenario 1: Single Item Loose (Multi-box)
                $total_boxes = (int) ($data['total_boxes'] ?? 1);
                foreach ($data['items'] as $item) {
                    $did = $item['detail_id'];
                    $qty_per_box = (int) $item['qty_per_box'];
                    if ($qty_per_box > 0) {
                        for ($i = 0; $i < $total_boxes; $i++) {
                            $box_plan[] = [
                                'composition' => [['id' => $did, 'qty' => $qty_per_box]],
                                'rack_id' => $global_rack_id
                            ];
                        }
                    }
                }
            } elseif ($set_id = ($data['set_id'] ?? null)) {
                // Scenario 2: Single Set (Multi-box)
                $total_boxes = (int) ($data['total_boxes'] ?? 1);
                $size_set_str = $data['size_set'] ?? '';
                $sizes = preg_split('/[,\s\.]+/', $size_set_str);
                $size_freq = [];
                foreach ($sizes as $s) {
                    $s = trim($s);
                    if ($s === '')
                        continue;
                    $size_freq[$s] = ($size_freq[$s] ?? 0) + 1;
                }

                $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_id);
                if (!$set)
                    throw new \Exception("Set not found.");

                $detail_map = [];
                foreach ($set->product_set_details as $detail) {
                    $detail_map[$detail->size] = $detail->id;
                }

                $composition = [];
                foreach ($size_freq as $size_name => $count) {
                    if (isset($detail_map[$size_name])) {
                        $composition[] = ['id' => $detail_map[$size_name], 'qty' => $count];
                    } else {
                        throw new \Exception("Size '$size_name' not found in the selected set.");
                    }
                }

                $set_rack_id = $set_racks[$set_id] ?? $global_rack_id;
                for ($i = 0; $i < $total_boxes; $i++) {
                    $box_plan[] = [
                        'composition' => $composition,
                        'rack_id' => $set_rack_id
                    ];
                }
            }

            if (empty($box_plan)) {
                throw new \Exception("No items selected or no remaining items to pack.");
            }

            // Global Validation & Total Calculation
            $total_items_to_pack = 0;
            $items_summary = []; // [detail_id => total_qty]
            $items_summary = []; // [detail_id => total_qty]
            foreach ($box_plan as $plan_item) {
                foreach ($plan_item['composition'] as $item) {
                    $items_summary[$item['id']] = ($items_summary[$item['id']] ?? 0) + $item['qty'];
                    $total_items_to_pack += $item['qty'];
                }
            }

            foreach ($items_summary as $did => $total_needed) {
                $detail = \App\Models\OrderProductSetDetail::find($did);
                if (!$detail)
                    throw new \Exception("Item detail not found for ID: $did");

                // HARD LIMIT: Cannot pack more than what was delivered to the unit
                $avl_at_unit = $unit_available[$did] ?? 0;
                if ($total_needed > $avl_at_unit) {
                    throw new \Exception("Insufficient quantity at Unit for '{$detail->size}'. Requested: $total_needed Pcs, Available at Unit: $avl_at_unit Pcs.");
                }
            }

            // Group box_plan by rack_id
            $grouped_plans = [];
            foreach ($box_plan as $plan) {
                $rid = $plan['rack_id'] ?: 0; // Use 0 for no rack
                $grouped_plans[$rid][] = $plan['composition'];
            }

            $carton_count = 0;
            $total_boxes_to_create = count($box_plan);
            $datePrefix = date('ymd');

            $next_available_no = \App\Models\PackingCarton::where('carton_no', 'LIKE', $datePrefix . '%')
                ->max('carton_no') ?? ($datePrefix . '000');
            $next_available_no = (int)$next_available_no + 1;

            foreach ($grouped_plans as $rid => $compositions) {
                $actual_rack_id = $rid ?: null;
                $boxes_in_group = count($compositions);
                $boxes_remaining_in_group = $boxes_in_group;
                $comp_idx = 0;

                while ($boxes_remaining_in_group > 0) {
                    $carton_no_str = (string) $next_available_no;

                    $carton = \App\Models\PackingCarton::create([
                        'packing_main_id' => $main->id,
                        'carton_no' => $carton_no_str,
                        'rack_id' => $actual_rack_id,
                        'status' => 0, // Draft
                    ]);

                    $boxes_in_this_carton = min($boxes_per_carton, $boxes_remaining_in_group);

                    for ($b = 0; $b < $boxes_in_this_carton; $b++) {
                        $box_composition = $compositions[$comp_idx++];

                        foreach ($box_composition as $item) {
                            $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['id']);
                            $fallbackPrice = ($detail && $detail->orderProductSet && $detail->orderProductSet->total_quantity > 0) ? ($detail->orderProductSet->basic_amount / $detail->orderProductSet->total_quantity) : 0;
                            
                            \App\Models\PackingItem::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'size_id' => $item['id'],
                                'quantity' => $item['qty'],
                                'selling_price' => $fallbackPrice,
                                'mrp' => 0
                            ]);

                            $unit_available[$item['id']] -= $item['qty'];
                            // (Relaxed) Quantity Validation - Allow overages as requested by user
                            if ($unit_available[$item['id']] < -50) { // Keep a broad limit or just comment? Let's comment.
                                /* $detail = \App\Models\OrderProductSetDetail::find($item['id']);
                                throw new \Exception("Insufficient quantity available at unit for size '{$detail->size}'"); */
                            }
                        }
                    }

                    $boxes_remaining_in_group -= $boxes_in_this_carton;
                    $next_available_no++;
                    $carton_count++;
                }
            }

            // Adjust remaining Qty in OrderStageTransaction
            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $orderLots = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();

            $orderStageTransactions = \App\Models\OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                ->where(function($q) use ($slip_details) {
                    $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                      ->orWhereNull('sub_stage_id_to');
                })
                ->whereIn('lot_no', $orderLots)
                ->orderBy('id')
                ->get();

            $remaining_to_deduct = $total_items_to_pack;
            foreach ($orderStageTransactions as $transaction) {
                if ($remaining_to_deduct <= 0)
                    break;
                if ($transaction->remaining_quantity <= 0)
                    continue;

                if ($transaction->remaining_quantity > $remaining_to_deduct) {
                    $transaction->remaining_quantity -= $remaining_to_deduct;
                    $remaining_to_deduct = 0;
                } else {
                    $remaining_to_deduct -= $transaction->remaining_quantity;
                    $transaction->remaining_quantity = 0;
                }
                $transaction->save();
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully packed $carton_count cartons and $total_boxes_to_create boxes."];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

        public function saveCarton($data)
    {
        \DB::beginTransaction();
        try {
            $packed_pcs = 0;
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = \App\Models\PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'rack_id' => $data['rack_id'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => 0,
            ]);

            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            if ((isset($data['items']) && count($data['items']) > 0) || (isset($data['sets']) && count($data['sets']) > 0)) {
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if ($item['quantity'] <= 0) continue;
                        $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['size_id']);
                        $needed_pcs = $item['quantity'];
                        
                        $entry = [
                            'selected_lots' => $data['selected_lots'] ?? [],
                            'rack_id' => $data['rack_id'] ?? null,
                        ];
                        
                        $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                        $packed_pcs += $needed_pcs;
                    }
                }

                if (isset($data['sets']) && is_array($data['sets'])) {
                    foreach ($data['sets'] as $set_item) {
                        if ($set_item['quantity'] <= 0) continue;
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_item['set_id']);
                        if (!$set) continue;
                        
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                        foreach ($set->product_set_details as $detail) {
                            $ratio = $detail->total_quantity / $set_total_qty;
                            $needed_pcs = ceil($ratio * $set_item['quantity']);
                            if ($needed_pcs <= 0) continue;
                            
                            $entry = [
                                'selected_lots' => $data['selected_lots'] ?? [],
                                'rack_id' => $data['rack_id'] ?? null,
                            ];
                            $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                            $packed_pcs += $needed_pcs;
                        }
                    }
                }
            }

            \DB::commit();
            return ['status' => 'success', 'message' => "Successfully packed $packed_pcs pieces into Carton {$carton->carton_no}."];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

        private function processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, $requested_boxes, $entry, $slip_details, $data) {
        $validLotsForSize = \App\Models\OrderLot::where('order_products_set_id', $detail->order_products_set_id ?? 0)->pluck('lot_no')->toArray();
        if (empty($validLotsForSize)) {
            $validLotsForSize = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();
        }

        if (!empty($entry['selected_lots'])) {
            $lotsToDeduct = array_intersect($entry['selected_lots'], $validLotsForSize);
            if (empty($lotsToDeduct)) {
                $lotsToDeduct = $validLotsForSize;
            }
        } else {
            $lotsToDeduct = $validLotsForSize;
        }

        $orderStageTransactions = \App\Models\OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
            ->where(function($q) use ($slip_details) {
                $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                  ->orWhereNull('sub_stage_id_to');
            })
            ->whereIn('lot_no', $lotsToDeduct)
            ->orderBy('id')->get();

        $rem = $needed_pcs;
        $pieces_per_box = $needed_pcs > 0 ? ($needed_pcs / $requested_boxes) : 1;

        foreach ($orderStageTransactions as $tx) {
            if ($rem <= 0) break;
            if ($tx->remaining_quantity <= 0) continue;
            
            $take = min($rem, $tx->remaining_quantity);
            
            $tx->remaining_quantity -= $take;
            $tx->save();
            
            $rem -= $take;
            
            $boxes_for_this_lot = round($take / $pieces_per_box, 2);

            \App\Models\PackingItem::create([
                'packing_main_id' => $main->id,
                'packing_carton_id' => $carton->id,
                'size_id' => $detail->id,
                'lot_no' => $tx->lot_no,
                'total_boxes' => $boxes_for_this_lot,
                'rack_id' => $entry['rack_id'] ?? $carton->rack_id,
                'quantity' => $take,
                'selling_price' => $entry['price'] ?? 0,
                'mrp' => $entry['mrp'] ?? 0
            ]);
        }
        
        if ($rem > 0) {
            $boxes_for_this_lot = round($rem / $pieces_per_box, 2);
            \App\Models\PackingItem::create([
                'packing_main_id' => $main->id,
                'packing_carton_id' => $carton->id,
                'size_id' => $detail->id,
                'lot_no' => null,
                'total_boxes' => $boxes_for_this_lot,
                'rack_id' => $entry['rack_id'] ?? $carton->rack_id,
                'quantity' => $rem,
                'selling_price' => $entry['price'] ?? 0,
                'mrp' => $entry['mrp'] ?? 0
            ]);
        }
    }

        public function saveMultiCartonPlan($data)
    {
        \DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $order = \App\Models\OrderMain::find($data['order_id']);
            $is_domestic = $order && strtolower(trim($order->order_type)) === 'domestic';
            $plan = $data['plan'] ?? [];
            if (empty($plan))
                throw new \Exception("Empty plan provided.");

            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            $carton_groups = [];
            $requested_pcs = [];
            foreach ($plan as $entry) {
                $carton_groups[$entry['carton_no']][] = $entry;

                if ($entry['type'] === 'set' || $entry['type'] === 'domestic') {
                    $set = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                    if ($set) {
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                        foreach ($set->product_set_details as $detail) {
                            $pcs_per_set = ceil($detail->total_quantity / $set_total_qty);
                            $requested_pcs[$detail->id] = ($requested_pcs[$detail->id] ?? 0) + ($pcs_per_set * $entry['quantity']);
                        }
                    }
                } else {
                    $did = $entry['content_id'];
                    $requested_pcs[$did] = ($requested_pcs[$did] ?? 0) + $entry['quantity'];
                }
            }

            foreach ($requested_pcs as $did => $needed) {
                $avl = $unit_available[$did] ?? 0;
                if ($needed > $avl) {
                    $detail = \App\Models\OrderProductSetDetail::find($did);
                    $sz = $detail ? $detail->size : 'N/A';
                    throw new \Exception("Insufficient quantity at Unit for '{$sz}'. Needed: $needed Pcs, Available at Unit: $avl Pcs.");
                }
            }

            $total_pcs = 0;
            $carton_count = 0;

            foreach ($carton_groups as $cno => $entries) {
                $rack_id = $entries[0]['rack_id'] ?? null;
                $carton = \App\Models\PackingCarton::create([
                    'packing_main_id' => $main->id,
                    'carton_no' => $cno,
                    'rack_id' => $rack_id,
                    'status' => 0
                ]);

                foreach ($entries as $entry) {
                    $qty = (int) $entry['quantity'];
                    $barcode = $entry['barcode'] ?? null;

                    if ($barcode && !$carton->barcode) {
                        $carton->update(['barcode' => $barcode]);
                    }

                    if ($is_domestic && $entry['type'] === 'domestic') {
                        $stockSet = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$stockSet) throw new \Exception("Stock Set not found for domestic entry.");

                        // Sync fitting and pattern from corporate order set to production_goods product if not set
                        $prod = \App\Models\ProductionGoods::find($entry['product_id']);
                        if ($prod && (empty($prod->master_product_fitting_id) || empty($prod->master_pattern_id))) {
                            $prod->update([
                                'master_product_fitting_id' => $stockSet->master_product_fitting_id ?? $prod->master_product_fitting_id,
                                'master_pattern_id' => $stockSet->master_design_pattern_id ?? $prod->master_pattern_id
                            ]);
                        }

                        $ss_total = $stockSet->set_quantity > 0 ? $stockSet->set_quantity : 1;
                        $pcs_per_box = 0;
                        foreach ($stockSet->product_set_details as $detail) {
                            $pcs_per_box += ceil($detail->total_quantity / $ss_total);
                        }

                        $gen_barcode = 'D' . $entry['product_id'] . 'S' . $entry['size_set_id'] . 'C' . $entry['color_id'];
                        $final_barcode = $barcode ?: $gen_barcode;

                        $inventory = \App\Models\DomesticInventory::where('barcode', $final_barcode)
                            ->where('rack_id', $rack_id)
                            ->where('order_main_id', $data['order_id'])
                            ->first();

                        if ($inventory) {
                            $inventory->total_boxes += $qty;
                            $inventory->save();
                        } else {
                            \App\Models\DomesticInventory::create([
                                'order_main_id' => $data['order_id'],
                                'packing_main_id' => $main->id,
                                'rack_id' => $rack_id,
                                'product_id' => $entry['product_id'],
                                'color_id' => $entry['color_id'],
                                'size_set_id' => $entry['size_set_id'],
                                'quantity' => $pcs_per_box,
                                'total_boxes' => $qty,
                                'barcode' => $final_barcode,
                                'status' => 1
                            ]);
                        }

                        foreach ($stockSet->product_set_details as $detail) {
                            $pieces = ceil($detail->total_quantity / $ss_total) * $qty;
                            $this->processLotDeductionAndCreateItem($main, $carton, $detail, $pieces, $qty, $entry, $slip_details, $data);
                            
                            \App\Models\ProductionOutflowInventory::create([
                                'order_main_id' => $data['order_id'],
                                'slip_id' => $data['slip_id'],
                                'product_id' => $entry['product_id'],
                                'color_id' => $entry['color_id'],
                                'size_id' => $detail->id,
                                'quantity' => $pieces,
                                'type' => 'packing',
                                'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                'remarks' => "Domestic Packing",
                                'lot_no' => !empty($entry['selected_lots']) ? $entry['selected_lots'][0] : null
                            ]);
                            $total_pcs += $pieces;
                        }
                    } else if ($entry['type'] === 'set') {
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$set) throw new \Exception("Set Pattern not found.");
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;

                        foreach ($set->product_set_details as $detail) {
                            $ratio = $detail->total_quantity / $set_total_qty;
                            $needed_pcs = ceil($ratio * $qty); 
                            
                            if ($needed_pcs > 0) {
                                $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, $qty, $entry, $slip_details, $data);
                                $total_pcs += $needed_pcs;
                            }
                        }
                    } else {
                        $did = $entry['content_id'];
                        $detail = \App\Models\OrderProductSetDetail::find($did);
                        $needed_pcs = $qty; 
                        
                        $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                        $total_pcs += $needed_pcs;
                    }
                }
                $carton_count++;
            }

            \DB::commit();
            return ['status' => 'success', 'message' => "Successfully processed $carton_count cartons ($total_pcs pieces total)."];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizePacking($main_id, $completion_date = null)
    {
        DB::beginTransaction();
        try {
            $packing_main = PackingMain::with('cartons.items', 'order')->find($main_id);
            if (!$packing_main) {
                throw new \Exception("Packing record not found.");
            }

            $order = $packing_main->order;
            $is_domestic = $order && strtolower($order->order_type) == 'domestic';

            // Mark Packing Main as Finalized (1)
            $packing_main->update(['status' => 1]);

            // Update Stage Timing for all lots in this slip
            if ($completion_date) {
                $completion_datetime = date('Y-m-d H:i:s', strtotime($completion_date));
                
                // Robust lot identification
                $lotNumbers = \App\Models\OrderLot::where('production_slip_digitization_id', $packing_main->slip_id)
                    ->pluck('lot_no')
                    ->toArray();

                // Fallback 1: Use the lot_no directly from the slip record
                $slip = \App\Models\ProductionSlipDigitization::find($packing_main->slip_id);
                if ($slip && $slip->lot_no) {
                    $slipLots = explode(',', $slip->lot_no);
                    $lotNumbers = array_unique(array_merge($lotNumbers, array_map('trim', $slipLots)));
                }

                // Fallback 2: If still empty, check all lots for the order
                if (empty($lotNumbers) && $packing_main->order_main_id) {
                    $lotNumbers = \App\Models\OrderLot::where('order_main_id', $packing_main->order_main_id)
                        ->pluck('lot_no')
                        ->toArray();
                }

                foreach ($lotNumbers as $lotNo) {
                    if (empty($lotNo)) continue;
                    
                    // We update complete_date. If start/end date are missing, we set them to now as well.
                    $timing = \App\Models\OrderLotStageTiming::where('lot_no', $lotNo)
                        ->where('master_stage_id', 11) // Packing
                        ->first();
                    
                    $timingData = ['complete_date' => $completion_datetime];
                    if ($timing && !$timing->start_date) {
                        $timingData['start_date'] = $completion_datetime;
                    }
                    if ($timing && !$timing->end_date) {
                        $timingData['end_date'] = $completion_datetime;
                    }

                    \App\Models\OrderLotStageTiming::updateOrCreate(
                        ['lot_no' => $lotNo, 'master_stage_id' => 11],
                        $timingData
                    );
                }
            }

            // Determine status for cartons
            $carton_status = $is_domestic ? 3 : 1; // 3=Inventory, 1=Ready for Dispatch

            // Update all cartons in this session
            PackingCarton::where('packing_main_id', $main_id)->update(['status' => $carton_status]);

            // If Domestic, Move to Inventory table (Consolidated)
            // Domestic inventory creation is already handled during box creation (saveDomesticBulk & saveMultiCartonPlan)

            ProductionSlipDigitization::where('id', $packing_main->slip_id)->update([
                'status' => 1
            ]);

            DB::commit();
            return [
                'status' => 'success', 
                'message' => 'Packing finalized successfully.',
                'order_type' => $order ? strtolower(trim($order->order_type)) : '',
                'packing_main_id' => $packing_main->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getPackedQuantitiesForOrder($order_id)
    {
        return PackingItem::join('packing_mains', 'packing_items.packing_main_id', '=', 'packing_mains.id')
            ->where('packing_mains.order_main_id', $order_id)
            ->select('packing_items.size_id', DB::raw('SUM(packing_items.quantity) as total_packed'))
            ->groupBy('packing_items.size_id')
            ->pluck('total_packed', 'packing_items.size_id')
            ->toArray();
    }
    public function createAdHocSet($data)
    {
        DB::beginTransaction();
        try {
            // Check if Order exists
            $order = OrderMain::findOrFail($data['order_id']);

            // Create OrderProductSet
            // We need to fill required fields. Some might be null or dummy for ad-hoc sets.
            // Assuming 0 for price related fields if not critical for packing.
            $set = \App\Models\OrderProductSet::create([
                'order_main_id' => $order->id,
                'order_id' => $order->id, // Legacy field?
                'set_quantity' => $data['quantity'], // Total Sets requested
                'remark' => 'Created via Ad-Hoc Packing',
                'status' => 1,
                // Required fields based on Model fillable (assuming nullable in DB or defaults)
                // If strict mode is on, we might fail if we don't provide everything.
                // Assuming basic fields are enough for now based on context.
                'company_id' => $order->company_id ?? 1,
            ]);

            // Create Details
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty_per_set = $item['qty_per_set'];
                    $total_qty = $data['quantity'] * $qty_per_set;

                    \App\Models\OrderProductSetDetail::create([
                        'order_products_set_id' => $set->id,
                        'size' => $item['size_name'], // String name (e.g., "22")
                        'order_product_id' => $item['product_id'] ?? null, // Link to original product if known
                        'total_quantity' => $total_qty,
                        'remaining_quantity' => $total_qty, // Initially all remaining
                        'color_id' => $item['color_id'] ?? null
                    ]);
                }
            }

            DB::commit();
            return ['status' => 'success', 'set' => $set];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
        public function deleteCarton($carton_id)
    {
        \DB::beginTransaction();
        try {
            $carton = \App\Models\PackingCarton::with(['items', 'main'])->findOrFail($carton_id);

            // Safety Check: Cannot delete finalized cartons
            if ($carton->main && $carton->main->status == 1) {
                throw new \Exception("Cannot delete carton from a finalized session.");
            }

            // Reverse Stock Deductions EXACTLY back to the original lots
            $slip_id = $carton->main->slip_id;
            $order_id = $carton->main->order_main_id;
            $slip_details = \App\Models\ProductionSlipDigitization::find($slip_id);

            if (!$slip_details) {
                throw new \Exception("Production slip details not found.");
            }

            $carton_items = \App\Models\PackingItem::where('packing_carton_id', $carton->id)->get();
            
            foreach ($carton_items as $item) {
                if ($item->quantity <= 0) continue;
                
                if ($item->lot_no) {
                    $transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                        ->where('lot_no', $item->lot_no)
                        ->when($slip_details && $slip_details->stage_master_unit_id, function($q) use ($slip_details) {
                            $q->where(function($sq) use ($slip_details) {
                                $sq->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                                  ->orWhereNull('sub_stage_id_to');
                            });
                        })
                        ->orderBy('id', 'desc')
                        ->get();

                    if ($transactions->isEmpty()) {
                        $transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                            ->where('lot_no', $item->lot_no)
                            ->orderBy('id', 'desc')
                            ->get();
                    }

                    $remaining_to_return = $item->quantity;
                    foreach ($transactions as $transaction) {
                        if ($remaining_to_return <= 0) break;
                        $space_available = max(0, $transaction->quantity - $transaction->remaining_quantity);
                        if ($space_available > 0) {
                            $to_add = min($remaining_to_return, $space_available);
                            $transaction->remaining_quantity += $to_add;
                            $transaction->save();
                            $remaining_to_return -= $to_add;
                        }
                    }

                    if ($remaining_to_return > 0 && $transactions->isNotEmpty()) {
                        $first = $transactions->first();
                        $first->remaining_quantity += $remaining_to_return;
                        $first->save();
                    }
                } else {
                    // Fallback to legacy behavior if lot_no is missing
                    $detail = \App\Models\OrderProductSetDetail::find($item->size_id);
                    $lotsToReturn = \App\Models\OrderLot::where('order_products_set_id', $detail->order_products_set_id ?? 0)->pluck('lot_no')->toArray();
                    if (empty($lotsToReturn)) {
                        $lotsToReturn = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
                    }
                    $transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                        ->whereIn('lot_no', $lotsToReturn)
                        ->orderBy('id', 'desc')
                        ->get();
                    $remaining_to_return = $item->quantity;
                    foreach ($transactions as $transaction) {
                        if ($remaining_to_return <= 0) break;
                        $space_available = max(0, $transaction->quantity - $transaction->remaining_quantity);
                        if ($space_available > 0) {
                            $to_add = min($remaining_to_return, $space_available);
                            $transaction->remaining_quantity += $to_add;
                            $transaction->save();
                            $remaining_to_return -= $to_add;
                        }
                    }

                    if ($remaining_to_return > 0 && $transactions->isNotEmpty()) {
                        $first = $transactions->first();
                        $first->remaining_quantity += $remaining_to_return;
                        $first->save();
                    }
                }
            }
            
            \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
            $carton->delete();

            \DB::commit();
            return ['status' => 'success', 'message' => 'Carton and associated items deleted successfully. Inventory restored.'];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

        public function reassignRework($data)
    {
        DB::beginTransaction();
        try {
            $orderMainId = $data['order_id'];
            $slipId = $data['slip_id'];
            $toStageId = $data['to_stage_id'];
            $toUnitId = $data['to_unit_id'];
            $items = $data['items']; // [{detail_id, qty, lot_no}]
            $remarks = $data['remarks'] ?? 'Defect return for rework';
            $slipMain = \App\Models\ProductionSlipDigitization::findOrFail($slipId);

            // 1. Group by lot_no
            $itemsByLot = collect($items)->groupBy('lot_no');
            
            foreach ($itemsByLot as $lotNo => $lotItems) {
                // Calculate total Pcs for this lot
                $totalPcs = $lotItems->sum('qty');
                
                if ($totalPcs <= 0) continue;

                // 2. Identify the source Lot Number
                $sourceTx = OrderStageTransaction::where('to_stage_id', 11) // Packing
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->where('lot_no', $lotNo)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$sourceTx) {
                    throw new \Exception("No incoming production pieces found to return for Lot $lotNo.");
                }

                // 3. Create NEW OrderStageTransaction for REWORK
                $newTx = OrderStageTransaction::create([
                    'company_id' => $sourceTx->company_id,
                    'sub_company_id' => $sourceTx->sub_company_id,
                    'project_id' => $sourceTx->project_id,
                    'sku' => $sourceTx->sku,
                    'order_product_id' => $sourceTx->order_product_id,
                    'from_stage_id' => 11, // From Packing
                    'to_stage_id' => $toStageId,
                    'sub_stage_id' => $slipMain->stage_master_unit_id,
                    'sub_stage_id_to' => $toUnitId,
                    'lot_no' => $lotNo,
                    'quantity' => $totalPcs,
                    'remaining_quantity' => $totalPcs,
                    'remarks' => $remarks,
                    'production_datetime' => now(),
                    'production_slip_digitization_id' => $slipId,
                    'status' => 1,
                    'type' => 'rework' // KEY: mark as rework
                ]);

                // 4. Create Transaction Details
                foreach ($lotItems as $item) {
                    $qty = (int) $item['qty'];
                    if ($qty <= 0)
                        continue;

                    $detail = \App\Models\OrderProductSetDetail::find($item['detail_id']);

                    OrderStageTransactionDetail::create([
                        'order_stage_transaction_id' => $newTx->id,
                        'size' => $detail->size ?? 'N/A',
                        'quantity' => $qty
                    ]);

                    // 5. DEDUCT from current unit's availability
                    $incomingTxs = OrderStageTransaction::where('to_stage_id', 11)
                        ->where(function($q) use ($slipMain) {
                            $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                              ->orWhereNull('sub_stage_id_to');
                        })
                        ->where('lot_no', $lotNo)
                        ->orderBy('id', 'asc') // FIFO
                        ->get();

                    $rem = $qty;
                    foreach ($incomingTxs as $itx) {
                        if ($rem <= 0)
                            break;
                        if ($itx->remaining_quantity <= 0)
                            continue;

                        $deduct = min($itx->remaining_quantity, $rem);
                        $itx->remaining_quantity -= $deduct;
                        $itx->save();
                        $rem -= $deduct;
                    }
                }
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully reassigned $totalPcs pieces for rework."];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recordDeadStock($data)
    {
        return $this->recordConsolidatedOutflow('dead', $data);
    }



    public function checkCartonNo($carton_no)
    {
        $exists = PackingCarton::where('carton_no', $carton_no)->exists();
        return $exists;
    }

    public function recordSamplingStock($data)
    {
        return $this->recordConsolidatedOutflow('sampling', $data);
    }

    public function recordUnitDebit($data)
    {
        return $this->recordConsolidatedOutflow('debit', $data);
    }

        private function recordConsolidatedOutflow($type, $data)
    {
        DB::beginTransaction();
        try {
            $orderMainId = $data['order_id'];
            $slipId = $data['slip_id'];
            $rackId = $data['rack_id'] ?? null;
            $items = $data['items']; // [{detail_id, qty, lot_no}]
            $remarks = $data['remarks'] ?? ($type . ' outflow');

            $slipMain = \App\Models\ProductionSlipDigitization::findOrFail($slipId);

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $lotNo = $item['lot_no'];
                if ($qty <= 0)
                    continue;

                $detail = \App\Models\OrderProductSetDetail::findOrFail($item['detail_id']);
                $set = \App\Models\OrderProductSet::findOrFail($detail->order_products_set_id);

                // Find a source transaction that contains pieces for this specific SKU (or at least this Lot)
                $sourceTx = OrderStageTransaction::where('to_stage_id', 11)
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->where('lot_no', $lotNo)
                    ->where(function ($q) use ($set) {
                        $q->where('sku', $set->sku)->orWhereNull('sku');
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$sourceTx) {
                    // Fallback to ANY transaction for this lot if SKU mismatch (common in Domestic)
                    $sourceTx = OrderStageTransaction::where('to_stage_id', 11)
                        ->where(function($q) use ($slipMain) {
                            $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                              ->orWhereNull('sub_stage_id_to');
                        })
                        ->where('lot_no', $lotNo)
                        ->orderBy('id', 'desc')
                        ->first();
                }

                if (!$sourceTx)
                    throw new \Exception("No incoming production pieces found for piece: " . $detail->size . " in Lot $lotNo");

                // 2. Create Log Record
                $outflowLog = \App\Models\ProductionOutflowInventory::create([
                    'type' => $type,
                    'order_main_id' => $orderMainId,
                    'slip_id' => $slipId,
                    'lot_no' => $lotNo,
                    'rack_id' => $rackId,
                    'product_id' => $set->production_goods_id,
                    'color_id' => $set->color_id,
                    'size_id' => $detail->id,
                    'quantity' => $qty,
                    'responsible_stage_id' => $data['stage_id'] ?? null,
                    'responsible_unit_id' => $data['unit_id'] ?? $slipMain->stage_master_unit_id,
                    'per_piece_amount' => $item['per_piece_amount'] ?? 0,
                    'total_amount' => ($item['per_piece_amount'] ?? 0) * $qty,
                    'discount' => $data['discount'] ?? 0,
                    'remarks' => $remarks
                ]);

                // 3. Deduct from Available
                $incomingTxs = OrderStageTransaction::where('to_stage_id', 11)
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->where('lot_no', $lotNo)
                    ->orderBy('id', 'asc') // FIFO
                    ->get();

                $rem = $qty;
                foreach ($incomingTxs as $itx) {
                    if ($rem <= 0) break;
                    if ($itx->remaining_quantity <= 0) continue;

                    $deduct = min($itx->remaining_quantity, $rem);
                    $itx->remaining_quantity -= $deduct;
                    $itx->save();
                    $rem -= $deduct;
                }
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully recorded $type pieces."];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function deleteOutflow($id)
    {
        DB::beginTransaction();
        try {
            $outflow = \App\Models\ProductionOutflowInventory::with('orderMain')->findOrFail($id);
            $slip = \App\Models\ProductionSlipDigitization::find($outflow->slip_id);
            $unitId = $outflow->responsible_unit_id ?: $slip->stage_master_unit_id;

            // 1. MIRROR REMOVAL (Linked by Barcode for precise size-wise restoration)
            $mirror = \App\Models\OrderStageTransaction::where('remarks', 'LIKE', "%[MirrorLink:" . $outflow->barcode . "]%")->first();

            if (!$mirror) {
                // Fallback for older records or failed links
                $mirrorType = ($outflow->type == 'dead' ? 'damage' : $outflow->type);
                $mirror = \App\Models\OrderStageTransaction::where('sku', $outflow->orderMain->sku)
                    ->where('sub_stage_id', $unitId)
                    ->where('type', $mirrorType)
                    ->where('quantity', '>=', $outflow->quantity)
                    ->orderBy('id', 'desc')->first();
            }

            if ($mirror) {
                if ($mirror->quantity <= $outflow->quantity) {
                    $mirror->details()->delete();
                    $mirror->delete();
                } else {
                    $mirror->quantity -= $outflow->quantity;
                    $mirror->save();
                    $det = $mirror->details()->first();
                    if ($det) {
                        $det->quantity = max(0, $det->quantity - $outflow->quantity);
                        $det->save();
                    }
                }
            }

            // 2. Revert Deduction from available transactions (for backend validation pool)
            $packingUnitId = $slip ? $slip->stage_master_unit_id : null;
            $receivedTxs = \App\Models\OrderStageTransaction::where('lot_no', $outflow->lot_no)
                ->where('to_stage_id', 11) // In Packing
                ->when($packingUnitId, function($q) use ($packingUnitId) {
                    $q->where(function($sq) use ($packingUnitId) {
                        $sq->where('sub_stage_id_to', $packingUnitId)
                          ->orWhereNull('sub_stage_id_to');
                    });
                })
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->get();

            if ($receivedTxs->isEmpty()) {
                $receivedTxs = \App\Models\OrderStageTransaction::where('lot_no', $outflow->lot_no)
                    ->where('to_stage_id', 11)
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            $rem = $outflow->quantity;
            foreach ($receivedTxs as $tx) {
                if ($rem <= 0) break;
                
                $space_available = max(0, $tx->quantity - $tx->remaining_quantity);
                if ($space_available > 0) {
                    $to_add = min($rem, $space_available);
                    $tx->remaining_quantity += $to_add;
                    $tx->save();
                    $rem -= $to_add;
                }
            }

            if ($rem > 0 && $receivedTxs->isNotEmpty()) {
                $first = $receivedTxs->first();
                $first->remaining_quantity += $rem;
                $first->save();
            }

            $outflow->delete();
            DB::commit();
            return ['status' => 'success', 'message' => 'Record deleted and stock restored.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteRework($id)
    {
        DB::beginTransaction();
        try {
            $rework = \App\Models\OrderStageTransaction::findOrFail($id);
            if ($rework->type !== 'rework' && (int)$rework->type !== 0) {
                throw new \Exception("Only rework movement records can be deleted from here.");
            }

            // 1. Revert Deduction from Incoming Packing pool
            $sourceTxs = \App\Models\OrderStageTransaction::where('lot_no', $rework->lot_no)
                ->where('to_stage_id', 11)
                ->when($rework->sub_stage_id, function($q) use ($rework) {
                    $q->where(function($sq) use ($rework) {
                        $sq->where('sub_stage_id_to', $rework->sub_stage_id)
                          ->orWhereNull('sub_stage_id_to');
                    });
                })
                ->orderBy('id', 'desc')
                ->get();

            if ($sourceTxs->isEmpty()) {
                $sourceTxs = \App\Models\OrderStageTransaction::where('lot_no', $rework->lot_no)
                    ->where('to_stage_id', 11)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            $rem = $rework->quantity;
            foreach ($sourceTxs as $tx) {
                if ($rem <= 0) break;
                $space_available = max(0, $tx->quantity - $tx->remaining_quantity);
                if ($space_available > 0) {
                    $to_add = min($rem, $space_available);
                    $tx->remaining_quantity += $to_add;
                    $tx->save();
                    $rem -= $to_add;
                }
            }

            if ($rem > 0 && $sourceTxs->isNotEmpty()) {
                $first = $sourceTxs->first();
                $first->remaining_quantity += $rem;
                $first->save();
            }

            // 2. Delete the rework record (which is itself an 'outgoing' record in board display)
            $rework->delete();

            DB::commit();
            return ['status' => 'success', 'message' => 'Rework deleted and pieces reverted to stock.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletePackingSession($slipId)
    {
        DB::beginTransaction();
        try {
            $main = PackingMain::where('slip_id', $slipId)->first();
            if (!$main) {
                throw new \Exception("Packing session not found for this slip.");
            }

            $is_domestic = false;
            $order = \App\Models\OrderMain::find($main->order_main_id);
            if ($order && $order->order_type === 'domestic') {
                $is_domestic = true;
            }

            // 1. Revert All Outflows (Dead/Sampling/Debit)
            $outflows = \App\Models\ProductionOutflowInventory::where('slip_id', $slipId)->get();
            foreach ($outflows as $outflow) {
                $this->deleteOutflow($outflow->id);
            }

            // 2. Revert Reworks (though usually reworks are from StageTransaction, but sometimes linked to slip)
            $orderLots = \App\Models\OrderLot::where('order_main_id', $main->order_main_id)->pluck('lot_no')->toArray();
            $reworks = \App\Models\OrderStageTransaction::whereIn('lot_no', $orderLots)
                ->where('from_stage_id', 11)
                ->where('status', 1)
                ->where('type', 'rework')
                ->get();
            foreach ($reworks as $rework) {
                $this->deleteRework($rework->id);
            }

            // 3. Delete All Cartons (This will restore OrderStageTransaction stock)
            $cartons = PackingCarton::where('packing_main_id', $main->id)->get();
            foreach ($cartons as $carton) {
                // We use the existing deleteCarton but remove the finalized check temporarily
                $carton->main->status = 0; // Temporarily un-finalize to allow deletion
                $carton->main->save();

                if ($is_domestic) {
                    $packingItems = \App\Models\PackingItem::where('packing_carton_id', $carton->id)->get();
                    foreach ($packingItems as $item) {
                        $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                        if ($od) {
                            $od->remaining_quantity += $item->quantity;
                            $od->save();
                        }
                    }
                }

                $this->deleteCarton($carton->id);
            }

            // 4. Clear Timing
            foreach ($orderLots as $lotNo) {
                \App\Models\OrderLotStageTiming::where('lot_no', $lotNo)
                    ->where('master_stage_id', 11)
                    ->update(['complete_date' => null]);
            }

            // 5. Delete Domestic Inventory, Selected Lots, and Main Record
            \App\Models\DomesticInventory::where('packing_main_id', $main->id)->delete();
            \App\Models\PackingSelectedLot::where('slip_id', $slipId)->delete();
            $main->delete();

            DB::commit();
            return ['status' => 'success', 'message' => 'Packing session deleted and stock restored successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
