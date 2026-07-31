<?php

namespace App\Services\Admin;

use App\Models\ProductionSlipDigitization;
use App\Models\PackingMain;
use App\Models\PackingCarton;
use App\Models\PackingBox;
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
            'packingMains.cartons' => function($q) {
                // Filter to only include cartons that contain corporate boxes (exclude domestic/manual)
                $q->whereHas('boxes', function($bq) {
                    $bq->whereNotIn('box_type', ['domestic', 'manual', 'corporate_domestic']);
                });
            },
            'packingMains.cartons.boxes' => function($q) {
                $q->whereNotIn('box_type', ['domestic', 'manual', 'corporate_domestic']);
            },
            'packingMains.cartons.boxes.items.detail.orderProductSet.product',
            'packingMains.cartons.boxes.items.detail.orderProductSet.colors',
            'packingMains.cartons.boxes.items.detail.orderProductSet.size_measurement',
            'packingMains.cartons.boxes.domesticInventory.product',
            'packingMains.cartons.boxes.domesticInventory.color',
            'packingMains.cartons.boxes.domesticInventory.sizeSet',
            'packingMains.cartons.items.detail.orderProductSet.product',
            'packingMains.cartons.items.detail.orderProductSet.colors',
            'packingMains.cartons.items.detail.orderProductSet.size_measurement',
            'packingMains.cartons.rack.storeroom'
        ])->findOrFail($order_id);
    }

    public function getPackingSessionDetails($id)
    {
        return PackingMain::with([
            'order.customer',
            'cartons.boxes.items.detail.orderProductSet.product',
            'cartons.boxes.items.detail.orderProductSet.colors',
            'cartons.boxes.items.detail.orderProductSet.size_measurement',
            'cartons.boxes.domesticInventory.product',
            'cartons.boxes.domesticInventory.color',
            'cartons.boxes.domesticInventory.sizeSet',
            'cartons.items.detail.orderProductSet.product',
            'cartons.items.detail.orderProductSet.colors',
            'cartons.items.detail.orderProductSet.size_measurement',
            'cartons.rack.storeroom',
            'outflows.product',
            'outflows.color',
            'outflows.size'
        ])->findOrFail($id);
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
                'boxes' => function ($q) {
                    $q->whereNull('packing_carton_id')
                      ->with([
                          'domesticInventory.product.pattern', 'domesticInventory.product.fitting', 'domesticInventory.color', 'domesticInventory.sizeSet', 
                          'items.detail.orderProductSet.product', 'items.detail.orderProductSet.colors', 'items.detail.orderProductSet.size_measurement', 
                          'items.detail.orderProductSet.master_design_pattern', 'items.detail.orderProductSet.master_product_fitting'
                      ]);
                },
                'cartons.boxes' => function($q) {
                    $q->with([
                        'domesticInventory.product.pattern', 'domesticInventory.product.fitting', 'domesticInventory.color', 'domesticInventory.sizeSet', 
                        'items.detail.orderProductSet.product', 'items.detail.orderProductSet.colors', 'items.detail.orderProductSet.size_measurement',
                        'items.detail.orderProductSet.master_design_pattern', 'items.detail.orderProductSet.master_product_fitting'
                    ]);
                },
                'cartons.items'
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

                // SOFT LIMIT: Overage packing for order is allowed as requested by user
                /* if ($total_needed > $available_for_order) {
                    // Just proceed
                } */
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

            foreach ($grouped_plans as $rid => $compositions) {
                $actual_rack_id = $rid ?: null;
                $boxes_in_group = count($compositions);
                $boxes_remaining_in_group = $boxes_in_group;
                $comp_idx = 0;

                while ($boxes_remaining_in_group > 0) {
                    $carton_no_str = (string) $next_available_no;

                    $carton = PackingCarton::create([
                        'packing_main_id' => $main->id,
                        'carton_no' => $carton_no_str,
                        'rack_id' => $actual_rack_id,
                        'status' => 0, // Draft
                    ]);

                    $boxes_in_this_carton = min($boxes_per_carton, $boxes_remaining_in_group);

                    // Box Sequence Reset/Fetch for naming
                    $latestBox = PackingBox::where('box_no', 'LIKE', "BX-$datePrefix-%")
                        ->orderBy('id', 'desc')
                        ->first();
                    $nextSeq = 1;
                    if ($latestBox) {
                        $parts = explode('-', $latestBox->box_no);
                        $nextSeq = (int) end($parts) + 1;
                    }

                    for ($b = 0; $b < $boxes_in_this_carton; $b++) {
                        $box_composition = $compositions[$comp_idx++];
                        $box_no = "BX-$datePrefix-" . str_pad($nextSeq++, 4, '0', STR_PAD_LEFT);

                        $box = PackingBox::create([
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'box_no' => $box_no,
                            'box_type' => 'bulk'
                        ]);

                        foreach ($box_composition as $item) {
                            $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['id']);
                            $fallbackPrice = ($detail && $detail->orderProductSet && $detail->orderProductSet->total_quantity > 0) ? ($detail->orderProductSet->basic_amount / $detail->orderProductSet->total_quantity) : 0;
                            
                            PackingItem::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'packing_box_id' => $box->id,
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
            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $orderLots = OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();

            $orderStageTransactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
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
        DB::beginTransaction();
        try {
            $packed_pcs = 0;
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'rack_id' => $data['rack_id'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => 0,
            ]);

            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            // If manual items/sets are provided, we wrap them in a single auto-generated box for this carton
            if ((isset($data['items']) && count($data['items']) > 0) || (isset($data['sets']) && count($data['sets']) > 0)) {
                $datePrefix = date('ymd');
                $latestBox = PackingBox::where('box_no', 'LIKE', "BX-$datePrefix-%")->orderBy('id', 'desc')->first();
                $nextSeq = 1;
                if ($latestBox) {
                    $parts = explode('-', $latestBox->box_no);
                    $nextSeq = (int) end($parts) + 1;
                }
                $box_no = "BX-$datePrefix-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                $box = PackingBox::create([
                    'packing_main_id' => $main->id,
                    'packing_carton_id' => $carton->id,
                    'box_no' => $box_no,
                    'box_type' => 'manual'
                ]);

                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if ($item['quantity'] <= 0)
                            continue;
                        
                        $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['size_id']);
                        $fallbackPrice = ($detail && $detail->orderProductSet && $detail->orderProductSet->total_quantity > 0) ? ($detail->orderProductSet->basic_amount / $detail->orderProductSet->total_quantity) : 0;

                        PackingItem::create([
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'packing_box_id' => $box->id,
                            'size_id' => $item['size_id'],
                            'quantity' => $item['quantity'],
                            'selling_price' => $fallbackPrice,
                            'mrp' => 0
                        ]);
                        // (Relaxed) Quantity Validation - Allow overages as requested by user
                        /* if (($unit_available[$item['size_id']] ?? 0) < $item['quantity']) {
                            throw new \Exception("Insufficient quantity available at unit for size ID " . $item['size_id']);
                        } */
                        $unit_available[$item['size_id']] -= $item['quantity'];
                        $packed_pcs += $item['quantity'];
                    }
                }

                if (isset($data['sets']) && is_array($data['sets'])) {
                    foreach ($data['sets'] as $set_req) {
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_req['set_id']);
                        if ($set) {
                            $pack_qty = $set_req['quantity'];
                            $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                            foreach ($set->product_set_details as $detail) {
                                $ratio = $detail->total_quantity / $set_total_qty;
                                $qty_to_pack = ceil($ratio * $pack_qty);
                                if ($qty_to_pack > 0) {
                                    $fallbackPrice = ($set && $set->total_quantity > 0) ? ($set->basic_amount / $set->total_quantity) : 0;
                                    PackingItem::create([
                                        'packing_main_id' => $main->id,
                                        'packing_carton_id' => $carton->id,
                                        'packing_box_id' => $box->id,
                                        'size_id' => $detail->id,
                                        'quantity' => $qty_to_pack,
                                        'selling_price' => $fallbackPrice,
                                        'mrp' => 0
                                    ]);
                                    /* if (($unit_available[$detail->id] ?? 0) < $qty_to_pack) {
                                        throw new \Exception("Insufficient quantity available at unit for size '{$detail->size}'");
                                    } */
                                    $unit_available[$detail->id] -= $qty_to_pack;
                                    $packed_pcs += $qty_to_pack;
                                }
                            }
                        }
                    }
                }
            }

            if (isset($data['box_ids']) && is_array($data['box_ids'])) {
                PackingBox::whereIn('id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
                PackingItem::whereIn('packing_box_id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
                // No need to deduct transactions here as boxes should already be deducted when created? 
                // Actually, saveBox deducts. So we are fine.
            }

            if ($packed_pcs > 0) {
                $orderLots = OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();
                $orderStageTransactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                    ->where(function($q) use ($slip_details) {
                        $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->whereIn('lot_no', $orderLots)->orderBy('id')->get();

                $rem = $packed_pcs;
                foreach ($orderStageTransactions as $tx) {
                    if ($rem <= 0)
                        break;
                    if ($tx->remaining_quantity <= 0)
                        continue;
                    if ($tx->remaining_quantity > $rem) {
                        $tx->remaining_quantity -= $rem;
                        $rem = 0;
                    } else {
                        $rem -= $tx->remaining_quantity;
                        $tx->remaining_quantity = 0;
                    }
                    $tx->save();
                }
            }

            DB::commit();
            return ['status' => 'success', 'carton' => $carton];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function saveMultiCartonPlan($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $order = \App\Models\OrderMain::find($data['order_id']);
            $is_domestic = $order && strtolower(trim($order->order_type)) === 'domestic';
            $plan = $data['plan'] ?? [];
            if (empty($plan))
                throw new \Exception("Empty plan provided.");

            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            $carton_groups = [];
            $requested_pcs = []; // To track total pieces needed per size detail for validation
            foreach ($plan as $entry) {
                $carton_groups[$entry['carton_no']][] = $entry;

                // Pre-calculate needed pieces
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

            // Hard Limit Validation: Compare against Unit availability
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
            $datePrefix = date('ymd');

            foreach ($carton_groups as $cno => $entries) {
                $rack_id = $entries[0]['rack_id'] ?? null;
                $carton = PackingCarton::create([
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
                        // Create specified number of domestic boxes
                        $stockSet = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$stockSet)
                            throw new \Exception("Stock Set not found for domestic entry.");

                        $ss_total = $stockSet->set_quantity > 0 ? $stockSet->set_quantity : 1;
                        $pcs_per_box = 0;
                        foreach ($stockSet->product_set_details as $detail) {
                            $pcs_per_box += ceil($detail->total_quantity / $ss_total);
                        }

                        $gen_barcode = 'D' . $entry['product_id'] . 'S' . $entry['size_set_id'] . 'C' . $entry['color_id'];
                        $final_barcode = $barcode ?: $gen_barcode;

                        // Check for existing consolidated inventory entry
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

                        for ($i = 0; $i < $qty; $i++) {
                            $box_no = "BX-$datePrefix-" . str_pad($this->getNextBoxSeq($datePrefix), 4, '0', STR_PAD_LEFT);
                            // Create PackingBox with explicit property assignment
                            $box = new PackingBox();
                            $box->packing_main_id = $main->id;
                            $box->packing_carton_id = $carton->id;
                            $box->box_no = $box_no;
                            $box->box_type = 'domestic_planner';
                            $box->barcode = $final_barcode;
                            $box->save();

                            // 4. Create PackingItem records for DISPATCH compatibility
                            foreach ($stockSet->product_set_details as $detail) {
                                $pcs = ceil($detail->total_quantity / $ss_total);

                                // Map by Size Name string to the Order's record ID
                                $mappedItem = $orderEntries->where('size', $detail->size)->first();
                                $finalSizeId = $mappedItem ? $mappedItem->id : $detail->id;

                                // Calculate fallback price from order
                                $fallbackPrice = 0;
                                if ($mappedItem && $mappedItem->orderProductSet && $mappedItem->orderProductSet->total_quantity > 0) {
                                    $fallbackPrice = $mappedItem->orderProductSet->basic_amount / $mappedItem->orderProductSet->total_quantity;
                                }

                                \App\Models\PackingItem::create([
                                    'packing_main_id' => $main->id,
                                    'packing_carton_id' => $carton->id,
                                    'packing_box_id' => $box->id,
                                    'size_id' => $finalSizeId,
                                    'quantity' => $pcs,
                                    'selling_price' => $fallbackPrice,
                                    'mrp' => 0
                                ]);
                            }

                            // DEDUCT FROM UNIT STOCK
                            // We must map sizes from the PACKED set to the IDs of the ORDERED set
                            $orderEntries = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
                                ->where('ops.order_main_id', $data['order_id'])
                                ->select('order_products_set_details.*')
                                ->get();

                            foreach ($stockSet->product_set_details as $detail) {
                                $pcs = ceil($detail->total_quantity / $ss_total);

                                // Map by Size Name string to the Order's record ID
                                $mappedItem = $orderEntries->where('size', $detail->size)->first();
                                $finalSizeId = $mappedItem ? $mappedItem->id : $detail->id;

                                \App\Models\ProductionOutflowInventory::create([
                                    'order_main_id' => $data['order_id'],
                                    'slip_id' => $data['slip_id'],
                                    'product_id' => $entry['product_id'],
                                    'color_id' => $entry['color_id'],
                                    'size_id' => $finalSizeId,
                                    'quantity' => $pcs,
                                    'type' => 'packing',
                                    'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                    'remarks' => "Domestic Packing: Box $box_no",
                                ]);
                                $total_pcs += $pcs;
                            }
                        }
                    } else if ($entry['type'] === 'set') {
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$set)
                            throw new \Exception("Set Pattern not found.");
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;

                        // Create specified number of sets as separate boxes
                        for ($i = 0; $i < $qty; $i++) {
                            $box_no = "BX-$datePrefix-" . str_pad($this->getNextBoxSeq($datePrefix), 4, '0', STR_PAD_LEFT);
                            $box = PackingBox::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'box_no' => $box_no,
                                'box_type' => 'planner',
                                'barcode' => $barcode // Link barcode to each box from this row
                            ]);

                            foreach ($set->product_set_details as $detail) {
                                $ratio = $detail->total_quantity / $set_total_qty;
                                $count = ceil($ratio * 1); // 1 set per box

                                if ($count > 0) {
                                    PackingItem::create([
                                        'packing_main_id' => $main->id,
                                        'packing_carton_id' => $carton->id,
                                        'packing_box_id' => $box->id,
                                        'size_id' => $detail->id,
                                        'quantity' => $count,
                                        'selling_price' => $entry['price'] ?? 0,
                                        'mrp' => $entry['mrp'] ?? 0
                                    ]);
                                    $unit_available[$detail->id] = ($unit_available[$detail->id] ?? 0) - $count;
                                    $total_pcs += $count;
                                }
                            }
                        }
                    } else {
                        // Loose: Pack all qty pieces into one planner box for this entry
                        $did = $entry['content_id'];
                        $box_no = "BX-$datePrefix-" . str_pad($this->getNextBoxSeq($datePrefix), 4, '0', STR_PAD_LEFT);
                        $box = PackingBox::create([
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'box_no' => $box_no,
                            'box_type' => 'planner_loose',
                            'barcode' => $barcode
                        ]);

                        PackingItem::create([
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'packing_box_id' => $box->id,
                            'size_id' => $did,
                            'quantity' => $qty,
                            'selling_price' => $entry['price'] ?? 0,
                            'mrp' => $entry['mrp'] ?? 0
                        ]);
                        $unit_available[$did] = ($unit_available[$did] ?? 0) - $qty;
                        $total_pcs += $qty;
                    }
                }
                $carton_count++;
            }

            if ($total_pcs > 0) {
                $orderLots = OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();
                $orderStageTransactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                    ->where(function($q) use ($slip_details) {
                        $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->whereIn('lot_no', $orderLots)->orderBy('id')->get();

                $rem = $total_pcs;
                foreach ($orderStageTransactions as $tx) {
                    if ($rem <= 0)
                        break;
                    if ($tx->remaining_quantity <= 0)
                        continue;
                    if ($tx->remaining_quantity > $rem) {
                        $tx->remaining_quantity -= $rem;
                        $rem = 0;
                    } else {
                        $rem -= $tx->remaining_quantity;
                        $tx->remaining_quantity = 0;
                    }
                    $tx->save();
                }
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully processed $carton_count cartons ($total_pcs pieces total)."];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function saveBox($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $datePrefix = date('ymd');
            $box_no = $data['box_no'] ?? '';

            // If user provides a custom manual name, we check it, but if empty, we auto-generate
            if (empty($box_no)) {
                $latestBox = PackingBox::where('box_no', 'LIKE', "BX-$datePrefix-%")
                    ->orderBy('id', 'desc')
                    ->first();
                $nextSeq = 1;
                if ($latestBox) {
                    $parts = explode('-', $latestBox->box_no);
                    $nextSeq = (int) end($parts) + 1;
                }
                $box_no = "BX-$datePrefix-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            }

            $box = PackingBox::create([
                'packing_main_id' => $main->id,
                'box_no' => $box_no,
                'box_type' => 'mixed'
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                $slip_details = ProductionSlipDigitization::find($data['slip_id']);
                $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

                foreach ($data['items'] as $item) {
                    PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_box_id' => $box->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);

                    // (Relaxed) Quantity Validation - Allow overages as requested by user
                    /* if (($unit_available[$item['size_id']] ?? 0) < $item['quantity']) {
                        throw new \Exception("Insufficient quantity available at unit for size ID " . $item['size_id']);
                    } */
                    $unit_available[$item['size_id']] -= $item['quantity'];
                }
            }



            DB::commit();
            return ['status' => 'success', 'box' => $box];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizePacking($main_id, $completion_date = null)
    {
        DB::beginTransaction();
        try {
            $packing_main = PackingMain::with('cartons.boxes.items', 'order')->find($main_id);
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
        DB::beginTransaction();
        try {
            $carton = PackingCarton::with(['items', 'main'])->findOrFail($carton_id);

            // Safety Check: Cannot delete finalized cartons
            if ($carton->main && $carton->main->status == 1) {
                throw new \Exception("Cannot delete carton from a finalized session.");
            }

            // 1. Calculate and Reverse Stock Deductions
            // We need to add back quantities to OrderStageTransaction
            $slip_id = $carton->main->slip_id;
            $order_id = $carton->main->order_main_id;
            $slip_details = ProductionSlipDigitization::find($slip_id);

            if (!$slip_details) {
                throw new \Exception("Production slip details not found.");
            }

            // Aggregate pieces by detail_id (size_id)
            $carton_items = PackingItem::where('packing_carton_id', $carton->id)->get();
            $totals = [];
            foreach ($carton_items as $item) {
                $totals[$item->size_id] = ($totals[$item->size_id] ?? 0) + $item->quantity;
            }

            // Reverse deductions in OrderStageTransaction
            $orderLots = OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
            foreach ($totals as $did => $qty_to_return) {
                // Find transactions where this qty was likely deducted from
                // We add back to the 'remaining_quantity' of the source transactions
                $transactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                    ->where(function($q) use ($slip_details) {
                        $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->whereIn('lot_no', $orderLots)
                    ->orderBy('id', 'desc') // Return to latest transactions first (optional strategy)
                    ->get();

                $remaining_to_return = $qty_to_return;
                foreach ($transactions as $transaction) {
                    if ($remaining_to_return <= 0)
                        break;

                    // How much can we return to this transaction? 
                    // Technically, there might not be a "max" record on remaining_quantity, 
                    // but we should probably not exceed the original 'quantity' if we had it.
                    // However, 'remaining_quantity' is just a float field.
                    $transaction->remaining_quantity += $remaining_to_return;
                    $transaction->save();
                    $remaining_to_return = 0;
                }
            }

            // 2. Cleanup Database and Adjust Domestic Inventory if needed
            $boxes = PackingBox::with('domesticInventory')->where('packing_carton_id', $carton->id)->get();
            foreach ($boxes as $box) {
                if ($box->box_type === 'corporate_domestic' && $box->barcode) {
                    // Try to find the exact inventory record for this carton/rack/barcode
                    $inventory = \App\Models\DomesticInventory::where('barcode', $box->barcode)
                        ->where('packing_carton_id', $carton->id)
                        ->first();
                    
                    if (!$inventory) {
                        // Fallback: search by barcode and order (standard relationship)
                        $inventory = $box->domesticInventory;
                    }

                    if ($inventory) {
                        if ($inventory->total_boxes > 1) {
                            $inventory->decrement('total_boxes');
                        } else {
                            $inventory->delete();
                        }
                    }
                }
                
                // Delete all items associated with this box
                PackingItem::where('packing_box_id', $box->id)->delete();
                $box->delete();
            }

            // Also delete loose items (not in boxes) associated with this carton
            PackingItem::where('packing_carton_id', $carton->id)->whereNull('packing_box_id')->delete();

            // Delete the Carton
            $carton->delete();

            DB::commit();
            return ['status' => 'success'];
        } catch (\Exception $e) {
            DB::rollBack();
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
            $items = $data['items']; // [{detail_id, qty}]
            $remarks = $data['remarks'] ?? 'Defect return for rework';

            $slipMain = ProductionSlipDigitization::findOrFail($slipId);

            // 1. Calculate Total Pcs to return
            $totalPcs = 0;
            foreach ($items as $item) {
                $totalPcs += (int) $item['qty'];
            }

            if ($totalPcs <= 0) {
                throw new \Exception("Quantity must be greater than zero.");
            }

            // 2. Identify the source Lot Number
            // We'll pick the most recent lot number received at this unit for this order
            $orderLots = \App\Models\OrderLot::where('order_main_id', $orderMainId)->pluck('lot_no')->toArray();

            $sourceTx = OrderStageTransaction::where('to_stage_id', 11) // Packing
                ->where(function($q) use ($slipMain) {
                    $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                      ->orWhereNull('sub_stage_id_to');
                })
                ->whereIn('lot_no', $orderLots)
                ->orderBy('id', 'desc')
                ->first();

            if (!$sourceTx) {
                throw new \Exception("No incoming production pieces found to return.");
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
                'lot_no' => $sourceTx->lot_no,
                'quantity' => $totalPcs,
                'remaining_quantity' => $totalPcs,
                'remarks' => $remarks,
                'production_datetime' => now(),
                'status' => 1,
                'type' => 'rework' // KEY: mark as rework
            ]);

            // 4. Create Transaction Details
            foreach ($items as $item) {
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
                // We do this by reducing the 'remaining_quantity' of incoming transactions 
                $incomingTxs = OrderStageTransaction::where('to_stage_id', 11)
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->where('lot_no', $sourceTx->lot_no)
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

    private function getNextBoxSeq($datePrefix)
    {
        $latestBox = PackingBox::where('box_no', 'LIKE', "BX-$datePrefix-%")
            ->orderBy('id', 'desc')
            ->first();
        $nextSeq = 1;
        if ($latestBox) {
            $parts = explode('-', $latestBox->box_no);
            $nextSeq = (int) end($parts) + 1;
        }
        return $nextSeq;
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
            $items = $data['items']; // [{detail_id, qty}]
            $remarks = $data['remarks'] ?? ($type . ' outflow');

            $slipMain = \App\Models\ProductionSlipDigitization::findOrFail($slipId);

            // 1. Identify valid Lot for this order at this unit
            $orderLots = \App\Models\OrderLot::where('order_main_id', $orderMainId)->pluck('lot_no')->toArray();

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
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
                    ->whereIn('lot_no', $orderLots)
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
                        ->whereIn('lot_no', $orderLots)
                        ->orderBy('id', 'desc')
                        ->first();
                }

                if (!$sourceTx)
                    throw new \Exception("No incoming production pieces found for piece: " . $detail->size);

                // 2. Create Log Record
                $outflowLog = \App\Models\ProductionOutflowInventory::create([
                    'type' => $type,
                    'order_main_id' => $orderMainId,
                    'slip_id' => $slipId,
                    'lot_no' => $sourceTx->lot_no,
                    'rack_id' => $rackId,
                    'product_id' => $set->production_goods_id,
                    'color_id' => $set->color_id,

                    'size_id' => $detail->id,
                    'quantity' => $qty,
                    'per_piece_amount' => $item['per_piece_amount'] ?? $data['per_piece_amount'] ?? null,
                    'total_amount' => $data['total_amount'] ?? null,
                    'discount' => $data['discount'] ?? null,
                    'responsible_stage_id' => $data['stage_id'] ?? 11, // Packing
                    'responsible_unit_id' => $data['unit_id'] ?? $slipMain->stage_master_unit_id,
                    'barcode' => strtoupper($type) . '-' . $detail->id . '-' . uniqid(),
                    'remarks' => $remarks
                ]);

                // 3. DEDUCT from current unit (validation pool)
                $incomingTxs = OrderStageTransaction::where('sku', $sourceTx->sku)
                    ->where('to_stage_id', 11)
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
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

                if ($rem > 0) {
                    throw new \Exception("Insufficient stock at unit for size '{$detail->size}'. Required: $qty, Available: " . ($qty - $rem));
                }

                // 4. Create Mirror TX (Linked by Barcode in Remarks for precise restoration)
                $outflowType = ($type === 'dead') ? 'damage' : $type;
                $outflowTx = OrderStageTransaction::create([
                    'company_id' => $sourceTx->company_id,
                    'sub_company_id' => $sourceTx->sub_company_id,
                    'project_id' => $sourceTx->project_id,
                    'sku' => $sourceTx->sku,
                    'order_product_id' => $sourceTx->order_product_id,
                    'from_stage_id' => 11,
                    'to_stage_id' => 13, // Virtual Stage for Outflows
                    'sub_stage_id' => $slipMain->stage_master_unit_id,
                    'lot_no' => $sourceTx->lot_no,
                    'quantity' => $qty,
                    'remaining_quantity' => 0,
                    'remarks' => $remarks . " [MirrorLink:" . $outflowLog->barcode . "]",
                    'production_datetime' => now(),
                    'status' => 1,
                    'type' => $outflowType
                ]);

                \App\Models\OrderStageTransactionDetail::create([
                    'order_stage_transaction_id' => $outflowTx->id,
                    'order_products_set_id' => $detail->order_products_set_id,
                    'size' => $detail->size,
                    'quantity' => $qty
                ]);
            }
            DB::commit();
            return ['status' => 'success', 'message' => "Successfully recorded $type outflow."];
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
            $receivedTxs = \App\Models\OrderStageTransaction::where('sku', $outflow->orderMain->sku)
                ->where('to_stage_id', 11) // In Packing
                ->where(function($q) use ($unitId) {
                    $q->where('sub_stage_id_to', $unitId)
                      ->orWhereNull('sub_stage_id_to');
                })
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->get();

            $rem = $outflow->quantity;
            foreach ($receivedTxs as $tx) {
                if ($rem <= 0)
                    break;
                // Add back to remaining_quantity
                $tx->remaining_quantity += $rem;
                $tx->save();
                $rem = 0;
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
            if ($rework->type !== 'rework') {
                throw new \Exception("Only rework movement records can be deleted from here.");
            }

            // 1. Revert Deduction from Incoming Packing pool
            $sourceTxs = \App\Models\OrderStageTransaction::where('sku', $rework->sku)
                ->where('to_stage_id', 11)
                ->where(function($q) use ($rework) {
                    $q->where('sub_stage_id_to', $rework->sub_stage_id)
                      ->orWhereNull('sub_stage_id_to');
                })
                ->orderBy('id', 'desc')
                ->get();

            $rem = $rework->quantity;
            foreach ($sourceTxs as $tx) {
                if ($rem <= 0)
                    break;
                $tx->remaining_quantity += $rem;
                $tx->save();
                $rem = 0;
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
                $this->deleteCarton($carton->id);
            }

            // 4. Clear Timing
            foreach ($orderLots as $lotNo) {
                \App\Models\OrderLotStageTiming::where('lot_no', $lotNo)
                    ->where('master_stage_id', 11)
                    ->update(['complete_date' => null]);
            }

            // 5. Delete Domestic Inventory and Main Record
            \App\Models\DomesticInventory::where('packing_main_id', $main->id)->delete();
            $main->delete();

            DB::commit();
            return ['status' => 'success', 'message' => 'Packing session deleted and stock restored successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
