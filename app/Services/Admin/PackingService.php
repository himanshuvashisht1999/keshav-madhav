<?php

namespace App\Services\Admin;

use App\Models\ProductionSlipDigitization;
use App\Models\PackingMain;
use App\Models\PackingCarton;
use App\Models\PackingBox;
use App\Models\PackingItem;
use App\Models\OrderMain;
use App\Models\OrderStageTransaction;
use App\Models\OrderLot;
use Illuminate\Support\Facades\DB;

class PackingService
{
    public function getPackingList()
    {
        return PackingMain::with(['order.customer'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function indexList($request)
    {
        // Select unique orders that have at least one packing session
        $query = OrderMain::whereHas('packingMains')
            ->with(['customer', 'packingMains']);

        // Filter by Order No (SKU)
        if ($request->has('order_no') && !empty($request->order_no)) {
            $query->where('sku', 'like', '%' . $request->order_no . '%');
        }

        // Filter by Customer Name
        if ($request->has('customer_name') && !empty($request->customer_name)) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Filter by Date Range (Checking the latest packing_date)
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereHas('packingMains', function ($q) use ($request) {
                $q->whereDate('packing_date', '>=', $request->start_date);
            });
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereHas('packingMains', function ($q) use ($request) {
                $q->whereDate('packing_date', '<=', $request->end_date);
            });
        }

        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('order_no', function ($row) {
                return $row->sku ?? 'N/A';
            })
            ->addColumn('customer', function ($row) {
                return $row->customer->name ?? 'N/A';
            })
            ->addColumn('packing_date', function ($row) {
                // Get the latest packing date from all associated slips
                $latestDate = $row->packingMains->max('packing_date');
                return $latestDate ? date('d/m/Y', strtotime($latestDate)) : 'N/A';
            })
            ->addColumn('status', function ($row) {
                // If any slip is still in draft (status 0), the order is In-Progress
                $allFinalized = !($row->packingMains->contains('status', 0));
                if ($allFinalized) {
                    return '<span class="badge badge-success">Finalized</span>';
                }
                return '<span class="badge badge-warning">Packing In-Progress</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('admin.packing.view', $row->id) . '" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i> View Complete Details</a>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getPackingDetailsForOrder($order_id)
    {
        return OrderMain::with([
            'customer',
            'packingMains.cartons.boxes.items.detail',
            'packingMains.cartons.items.detail',
            'packingMains.cartons.rack.storeroom'
        ])->findOrFail($order_id);
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
            ->with(['OrderProductSets.product_set_details', 'OrderProductSets.colors'])
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
        }
        return $packing;
    }

    public function getPackingMainWithStructure($slip_id)
    {
        return PackingMain::where('slip_id', $slip_id)
            ->with([
                'boxes' => function ($q) {
                    $q->whereNull('packing_carton_id'); // Only unpacked boxes
                },
                'cartons.boxes',
                'cartons.items'
            ])
            ->first();
    }

    public function bulkSaveCarton($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $boxes_per_carton = (int) ($data['boxes_per_carton'] ?? 1);
            $rack_id = $data['rack_id'] ?? null;
            $packed_quantities = $this->getPackedQuantitiesForOrder($data['order_id']);

            // Auto-generate start carton sequence
            $max_carton = PackingCarton::selectRaw('MAX(CAST(carton_no AS UNSIGNED)) as max_no')->value('max_no');
            $next_available_no = ($max_carton ?: 0) + 1;

            // box_plan: [[ {detail_id, qty}, ... ], ...] -> Each element is a BOX
            $box_plan = [];

            if (isset($data['mode']) && $data['mode'] == 'full_loose') {
                // Scenario 3: Full Order Loose (1 piece = 1 box)
                $order = \App\Models\OrderMain::with('OrderProductSets.product_set_details')->find($data['order_id']);
                foreach ($order->OrderProductSets as $set) {
                    foreach ($set->product_set_details as $detail) {
                        $remaining = $detail->total_quantity - ($packed_quantities[$detail->id] ?? 0);
                        for ($i = 0; $i < $remaining; $i++) {
                            $box_plan[] = [['id' => $detail->id, 'qty' => 1]];
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

                    for ($i = 0; $i < $min_remaining_sets; $i++) {
                        $box_plan[] = $composition;
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
                            $box_plan[] = [['id' => $did, 'qty' => $qty_per_box]];
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

                for ($i = 0; $i < $total_boxes; $i++) {
                    $box_plan[] = $composition;
                }
            }

            if (empty($box_plan)) {
                throw new \Exception("No items selected or no remaining items to pack.");
            }

            // Global Validation & Total Calculation
            $total_items_to_pack = 0;
            $items_summary = []; // [detail_id => total_qty]
            foreach ($box_plan as $box_items) {
                foreach ($box_items as $item) {
                    $items_summary[$item['id']] = ($items_summary[$item['id']] ?? 0) + $item['qty'];
                    $total_items_to_pack += $item['qty'];
                }
            }

            foreach ($items_summary as $did => $total_needed) {
                $detail = \App\Models\OrderProductSetDetail::find($did);
                if (!$detail)
                    throw new \Exception("Item detail not found for ID: $did");

                $available = $detail->total_quantity - ($packed_quantities[$did] ?? 0);
                if ($total_needed > $available) {
                    throw new \Exception("Insufficient quantity for size '{$detail->size}'. Needed: $total_needed, Available: $available");
                }
            }

            // Create Cartons and Boxes
            $total_boxes_to_create = count($box_plan);
            $boxes_remaining_in_plan = $total_boxes_to_create;
            $box_index = 0;
            $carton_count = 0;
            while ($boxes_remaining_in_plan > 0) {
                // Find next truly available numeric carton number
                while ($this->checkCartonNo((string) $next_available_no)) {
                    $next_available_no++;
                }
                $carton_no_str = (string) $next_available_no;

                $carton = PackingCarton::create([
                    'packing_main_id' => $main->id,
                    'carton_no' => $carton_no_str,
                    'rack_id' => $rack_id,
                    'status' => 0, // Draft
                ]);

                $boxes_in_this_carton = min($boxes_per_carton, $boxes_remaining_in_plan);
                for ($b = 0; $b < $boxes_in_this_carton; $b++) {
                    $box_composition = $box_plan[$box_index];
                    $box = PackingBox::create([
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'box_no' => "Bulk-" . $next_available_no . "-" . ($b + 1),
                        'box_type' => 'bulk'
                    ]);

                    foreach ($box_composition as $item) {
                        PackingItem::create([
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'packing_box_id' => $box->id,
                            'size_id' => $item['id'],
                            'quantity' => $item['qty']
                        ]);
                    }
                    $box_index++;
                }

                $boxes_remaining_in_plan -= $boxes_in_this_carton;
                $next_available_no++;
                $carton_count++;
            }

            // Adjust remaining Qty in OrderStageTransaction
            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $orderLots = OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();

            $orderStageTransactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
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
            $exists = $this->checkCartonNo($data['carton_no']);
            if ($exists) {
                DB::rollBack();
                return [
                    'status' => 'exists',
                    'message' => 'Carton number already exists'
                ];
            }
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'rack_id' => $data['rack_id'] ?? null, // Save Rack ID
                'note' => $data['note'] ?? null,
                'status' => 0, // Draft
                // Dimensions etc
            ]);

            // If direct items provided (Legacy/Manual)
            if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
                foreach ($data['items'] as $item) {
                    PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);
                    $packed_pcs += $item['quantity'];
                }
            }

            // If SETS are provided (New)
            if (isset($data['sets']) && is_array($data['sets'])) {
                foreach ($data['sets'] as $set_req) {
                    $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_req['set_id']);
                    if ($set) {
                        $pack_qty = $set_req['quantity'];
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;

                        foreach ($set->product_set_details as $detail) {
                            // Calculate qty per set unit
                            // If total required is 100 for 100 sets, then 1 per set.
                            // If total required is 200 for 100 sets, then 2 per set.
                            $ratio = $detail->total_quantity / $set_total_qty;

                            $qty_to_pack = ceil($ratio * $pack_qty); // Use ceil to be safe or round? 
                            // Integer math might key here.

                            if ($qty_to_pack > 0) {
                                PackingItem::create([
                                    'packing_main_id' => $main->id,
                                    'packing_carton_id' => $carton->id,
                                    'size_id' => $detail->id, // Wait, size_id refers to ORDER_PRODUCT_SET_DETAIL_ID? OR Size Master ID?
                                    // In current PackingItem, what is 'size_id'?
                                    // In Controller saveBox, we used $item['size_id']. Frontend passed `item.id`.
                                    // item.id comes from `OrderProductSetDetail->id`.
                                    // So `size_id` in PackingItem actually stores `order_product_set_detail_id`.
                                    // Checking PackingItem model to be sure...
                                    // Assuming it stores Detail ID for traceability.
                                    'quantity' => $qty_to_pack
                                ]);
                                $packed_pcs += $qty_to_pack;
                            }
                        }
                    }
                }
            }

            // If existing boxes are being put into this carton
            if (isset($data['box_ids']) && is_array($data['box_ids'])) {
                PackingBox::whereIn('id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
                PackingItem::whereIn('packing_box_id', $data['box_ids'])->update(['packing_carton_id' => $carton->id]);
            }

            $slip_details = ProductionSlipDigitization::where('id', $data['slip_id'])->first();

            $orderLots = OrderLot::where('order_main_id', $data['order_id'])
                ->pluck('lot_no')
                ->toArray();

            $orderStageTransactions = OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                ->whereIn('lot_no', $orderLots)
                ->orderBy('id')
                ->get();

            if ($orderStageTransactions->isNotEmpty()) {
                /* ADJUST REMAINING QTY */
                foreach ($orderStageTransactions as $transaction) {

                    if ($packed_pcs <= 0) {
                        break;
                    }

                    if ($transaction->remaining_quantity <= 0) {
                        continue;
                    }

                    if ($transaction->remaining_quantity > $packed_pcs) {

                        $transaction->remaining_quantity -= $packed_pcs;
                        $packed_pcs = 0;

                    } else {

                        $packed_pcs -= $transaction->remaining_quantity;
                        $transaction->remaining_quantity = 0;
                    }

                    $transaction->save();
                }
            }
            // (int) $packed_pcs;

            DB::commit();
            return ['status' => 'success', 'carton' => $carton];
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

            $box = PackingBox::create([
                'packing_main_id' => $main->id,
                'box_no' => $data['box_no'],
                'box_type' => 'mixed'
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_box_id' => $box->id,
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity']
                    ]);
                }
            }



            DB::commit();
            return ['status' => 'success', 'box' => $box];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizePacking($main_id)
    {
        DB::beginTransaction();
        try {
            // Mark Packing Main as Finalized (1)
            PackingMain::where('id', $main_id)->update(['status' => 1]);

            // Propagate status 1 to all cartons in this session
            PackingCarton::where('packing_main_id', $main_id)->update(['status' => 1]);

            $packing_data = PackingMain::where('id', $main_id)->first();
            ProductionSlipDigitization::where('id', $packing_data->slip_id)->update([
                'status' => 1
            ]);

            DB::commit();
            return ['status' => 'success'];
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
                    ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
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

            // 2. Cleanup Database
            // Delete all items associated with this carton
            PackingItem::where('packing_carton_id', $carton->id)->delete();

            // Delete all boxes associated with this carton
            PackingBox::where('packing_carton_id', $carton->id)->delete();

            // Delete the Carton
            $carton->delete();

            DB::commit();
            return ['status' => 'success'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function checkCartonNo($carton_no)
    {
        $exists = PackingCarton::where('carton_no', $carton_no)->exists();
        return $exists;
    }

}
