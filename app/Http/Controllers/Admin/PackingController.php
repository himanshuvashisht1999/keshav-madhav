<?php
namespace App\Http\Controllers\Admin;

use App\Models\OrderMain;
use App\Models\MasterDesign;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterColor;
use App\Models\MasterProductFitting;
use App\Models\MasterPattern;
use App\Models\Storeroom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\Admin\PackingService;

class PackingController extends Controller
{
    protected $service;

    public function __construct(PackingService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return view('admin.packing.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view($id)
    {
        $session = $this->service->getPackingSessionDetails($id);
        return view('admin.packing.view', compact('session'));
    }

    public function clearOrder(Request $request, $slip_id)
    {
        $packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
        if ($packing) {
            // Check if cartons exist
            $cartonCount = \App\Models\PackingCarton::where('packing_main_id', $packing->id)->count();
            if ($cartonCount > 0) {
                return redirect()->back()->with('error', 'Cannot change order. Cartons have already been created for this packing session.');
            }
            $packing->order_main_id = null;
            $packing->save();
        }
        return redirect()->route('admin.packing.process', ['slip_id' => $slip_id])->with('success', 'Order selection cleared successfully.');
    }

    public function process(Request $request, $slip_id)
    {
        $slip = $this->service->getSlipDetails($slip_id);
        if ($slip->status == 1) {
            return redirect()->back()->withError('Already digitized slip.');
        }

        $packing = $this->service->getPackingMainWithStructure($slip_id);

        // Determine which order to load: prefer request(?order_id=) but fallback to linked packing session
        $orderId = $request->order_id ?: ($packing->order_main_id ?? null);
        $order = null;
        if ($orderId) {
            $order = \App\Models\OrderMain::with([
                'customer',
                'OrderProductSets.product_set_details',
                'OrderProductSets.colors',
                'OrderProductSets.size_measurement'
            ])->find($orderId);
        } else if ($slip->sku) {
            // Legacy fallback if no specific order link/request yet
            $order = $this->service->getOrderDetails($slip->sku);
        }

        if ($order && strtolower(trim($order->order_type)) == 'domestic') {
            return redirect()->route('admin.packing.processDomestic', ['slip_id' => $slip_id, 'order_id' => ($order->id ?? $request->order_id)]);
        }

        $active_orders = [];
        $packed_quantities = [];
        $order_sets = collect();
        $unit_available = [];
        $unit_available_per_lot = [];

        if (!$order) {
            $validOrderIds = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                ->where('order_stage_transactions.to_stage_id', 11) // Packing
                ->where('order_stage_transactions.sub_stage_id_to', $slip->stage_master_unit_id)
                ->where('order_stage_transactions.remaining_quantity', '>', 0)
                ->pluck('order_lots.order_main_id')
                ->unique()
                ->toArray();

            // Fetch ALL active orders for dropdown (Corporate & Domestic)
            $active_orders = \App\Models\OrderMain::with('customer')
                ->whereIn('id', $validOrderIds)
                ->whereIn('status', [0, 1, 2]) // Pending, Confirmed, Partial
                ->orderBy('id', 'desc')->get();
        } else {
            $packed_quantities = $this->service->getPackedQuantitiesForOrder($order->id);
            $unit_available = $this->service->getAvailableQuantitiesAtUnit($order->id, $slip->stage_master_unit_id);
        $unit_available_per_lot = $this->service->getAvailableQuantitiesAtUnitPerLot($order->id, $slip->stage_master_unit_id);
            $unit_incoming = $this->service->getIncomingQuantitiesAtUnit($order->id, $slip->stage_master_unit_id);

            // Logic to prepare sets (duplicated from JSON method for initial load)
            $order_sets = $order->OrderProductSets->map(function ($set) use ($packed_quantities, $unit_available, $unit_incoming) {
                $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                $min_packed_sets = null;
                $details = $set->product_set_details->map(function ($detail) use ($packed_quantities, $set_total_qty, $set, $unit_available, $unit_incoming) {
                    $item = $detail->toArray();
                    $item['packed_qty'] = $packed_quantities[$detail->id] ?? 0;
                    $item['qty_per_set'] = $detail->total_quantity / $set_total_qty;
                    $item['design_number'] = $set->design_number;
                    $item['color_name'] = $set->colors ? $set->colors->name : 'N/A';
                    $item['unit_available_qty'] = $unit_available[$detail->id] ?? 0;
                    $item['unit_incoming_qty'] = $unit_incoming[$detail->id] ?? 0;
                    $item['product_id'] = $set->production_goods_id;
                    return (object) $item;
                });
                foreach ($details as $detail) {
                    if ($detail->qty_per_set > 0) {
                        $sets_packed_for_this_detail = floor($detail->packed_qty / $detail->qty_per_set);
                        if ($min_packed_sets === null || $sets_packed_for_this_detail < $min_packed_sets) {
                            $min_packed_sets = $sets_packed_for_this_detail;
                        }
                    }
                }
                $set->setAttribute('packed_sets', $min_packed_sets ?? 0);
                $set->setAttribute('details_data', $details);
                return $set;
            })->values();
        }

        $unit_lots = [];
        if ($order) {
            $unit_lots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
                ->where('order_stage_transactions.to_stage_id', 11)
                ->where('order_stage_transactions.sub_stage_id_to', $slip->stage_master_unit_id)
                ->where('order_lots.order_main_id', $order->id)
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    'order_stage_transactions.quantity',
                    'order_stage_transactions.remaining_quantity'
                )
                ->get();
        }

        $storerooms = \App\Models\Storeroom::with('racks')->where('status', 1)->get();

        $outflows = collect();
        $reworks = collect();

        if ($order) {
            // Fetch non-packing outflows (Dead, Sampling, Debit, Damage) for THIS order on this slip
            $outflows = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
                ->where('order_main_id', $order->id)
                ->whereNotIn('type', ['packing', 'packing_divert']) // Filter out packing and divert movements per user request
                ->with(['product', 'color', 'size', 'rack.storeroom', 'responsibleStage', 'responsibleUnit'])
                ->get();

            // Fetch reworks specifically for THIS order (sent back to other units)
            // Strictly filter for 'rework' type and excludes Godam/Loss (stage 13) 
            $orderLots = \App\Models\OrderLot::where('order_main_id', $order->id)->pluck('lot_no')->toArray();
            $reworks = \App\Models\OrderStageTransaction::whereIn('lot_no', $orderLots)
                ->where('from_stage_id', 11) // From Packing
                ->where('sub_stage_id', $slip->stage_master_unit_id)
                ->where('status', 1)
                ->where('type', 'rework') // Strict rework filter
                ->where('to_stage_id', '!=', 13) // Exclude movements to Godam/Outflow
                ->with(['toStage', 'toUnit', 'details'])
                ->get();
        }

        // Restrict domestic masters products to ONLY designs in the current corporate order
        $orderDesignNumbers = [];
        if ($order) {
            $orderDesignNumbers = $order->OrderProductSets->pluck('design_number')->unique()->toArray();
        }

        // Restrict domestic masters products, size sets, and colors to ONLY those in the current corporate order
        $orderDesignNumbers = [];
        $orderSizeSetIds = [];
        $orderColorIds = [];

        if ($order && strtolower(trim($order->order_type)) != 'domestic') {
            $orderDesignNumbers = $order->OrderProductSets->pluck('design_number')->unique()->toArray();
            $orderSizeSetIds = $order->OrderProductSets->pluck('set_size')->unique()->toArray();
            $orderColorIds = $order->OrderProductSets->flatMap(function ($set) {
                return $set->colors ? [$set->colors->id] : [];
            })->unique()->toArray();
        }

        // Always load domestic masters to enable dynamic mode switching in UI
        $domestic_masters = [
            'products' => \App\Models\ProductionGoods::with('series')
                ->where('status', 1)
                ->when(!empty($orderSizeSetIds), function ($query) use ($orderSizeSetIds) {
                    $query->whereHas('variants', function ($q) use ($orderSizeSetIds) {
                        $q->whereIn('master_size_measurement_id', $orderSizeSetIds);
                    });
                })
                ->get(),
            'colors' => $order ? \App\Models\MasterColor::whereIn('id', $orderColorIds)->get() : \App\Models\MasterColor::all(),
            'size_sets' => $order ? \App\Models\MasterSizeMeasurement::whereIn('id', $orderSizeSetIds)->get() : \App\Models\MasterSizeMeasurement::all()
        ];
        $order_type = strtolower(trim($order->order_type ?? ''));

        return view('admin.packing.process', compact('slip', 'order', 'packing', 'storerooms', 'active_orders', 'packed_quantities', 'order_sets', 'unit_available', 'outflows', 'reworks', 'domestic_masters', 'unit_lots', 'unit_available_per_lot'));
    }

    public function processDomestic(Request $request, $slip_id)
    {
        $slip = $this->service->getSlipDetails($slip_id);
        if ($slip->status == 1) {
            return redirect()->back()->withError('Already digitized slip.');
        }

        $packing = $this->service->getPackingMainWithStructure($slip_id);
        $orderId = $request->order_id ?: ($packing->order_main_id ?? null);
        $order = null;
        if ($orderId) {
            $order = \App\Models\OrderMain::with([
                'customer',
                'OrderProductSets.product_set_details',
                'OrderProductSets.colors',
                'OrderProductSets.size_measurement'
            ])->find($orderId);
        }

        if ($order && strtolower(trim($order->order_type)) != 'domestic') {
            return redirect()->route('admin.packing.process', $slip_id);
        }

        $validOrderIds = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
            ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
            ->where('order_stage_transactions.to_stage_id', 11) // Packing
            ->where('order_stage_transactions.sub_stage_id_to', $slip->stage_master_unit_id)
            ->where('order_stage_transactions.remaining_quantity', '>', 0)
            ->pluck('order_lots.order_main_id')
            ->unique()
            ->toArray();

        $active_orders = \App\Models\OrderMain::with('customer')
            ->whereIn('id', $validOrderIds)
            ->where('order_type', 'domestic')
            ->orderBy('id', 'desc')
            ->get();

        $packed_quantities = [];
        $order_sets = collect();
        $unit_available = [];

        if ($order) {
            $packed_quantities = $this->service->getPackedQuantitiesForOrder($order->id);
            $unit_available = $this->service->getAvailableQuantitiesAtUnit($order->id, $slip->stage_master_unit_id);
        $unit_available_per_lot = $this->service->getAvailableQuantitiesAtUnitPerLot($order->id, $slip->stage_master_unit_id);
            $unit_incoming = $this->service->getIncomingQuantitiesAtUnit($order->id, $slip->stage_master_unit_id);

            $order_sets = $order->OrderProductSets->map(function ($set) use ($packed_quantities, $unit_available, $unit_incoming) {
                $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                $min_packed_sets = null;
                $details = $set->product_set_details->map(function ($detail) use ($packed_quantities, $set_total_qty, $set, $unit_available, $unit_incoming) {
                    $item = $detail->toArray();
                    $item['packed_qty'] = $packed_quantities[$detail->id] ?? 0;
                    $item['qty_per_set'] = $detail->total_quantity / $set_total_qty;
                    $item['design_number'] = $set->design_number;
                    $item['color_name'] = $set->colors ? $set->colors->name : 'N/A';
                    $item['unit_available_qty'] = $unit_available[$detail->id] ?? 0;
                    $item['unit_incoming_qty'] = $unit_incoming[$detail->id] ?? 0;
                    $item['product_id'] = $set->production_goods_id;
                    return (object) $item;
                });
                foreach ($details as $detail) {
                    if ($detail->qty_per_set > 0) {
                        $sets_packed_for_this_detail = floor($detail->packed_qty / $detail->qty_per_set);
                        if ($min_packed_sets === null || $sets_packed_for_this_detail < $min_packed_sets) {
                            $min_packed_sets = $sets_packed_for_this_detail;
                        }
                    }
                }
                $set->setAttribute('details_data', $details);
                $set->setAttribute('packed_sets', $min_packed_sets ?? 0);
                return $set;
            })->filter(function($set) {
                return $set->details_data->contains(function($detail) {
                    return $detail->unit_incoming_qty > 0;
                });
            })->values();
        }

        $storerooms = \App\Models\Storeroom::with('racks')->where('status', 1)->get();

        $isCorporate = $order && strtolower(trim($order->order_type)) != 'domestic';
        $orderSizeSetIds = $isCorporate ? $order->OrderProductSets->pluck('set_size')->unique()->toArray() : [];
        $availableProductIds = [];
        if ($isCorporate) {
            foreach ($order_sets as $set) {
                if ($set->production_goods_id) {
                    $availableProductIds[] = $set->production_goods_id;
                }
            }
            $availableProductIds = array_unique($availableProductIds);
        }

        $domestic_masters = [
            'products' => \App\Models\ProductionGoods::with('series')
                ->where('status', 1)
                ->when($isCorporate, function ($query) use ($availableProductIds) {
                    $query->whereIn('id', $availableProductIds);
                })
                ->when(!$order && !empty($orderSizeSetIds), function ($query) use ($orderSizeSetIds) {
                    $query->whereHas('variants', function ($q) use ($orderSizeSetIds) {
                        $q->whereIn('master_size_measurement_id', $orderSizeSetIds);
                    });
                })
                ->get(),
            'size_sets' => \App\Models\MasterSizeMeasurement::all(),
            'colors' => \App\Models\MasterColor::all(),
            'fittings' => \App\Models\MasterProductFitting::all(),
            'patterns' => \App\Models\MasterPattern::all(),
        ];

        $outflows = collect();
        $reworks = collect();
        if ($order) {
            // Fetch non-packing outflows (Dead, Sampling, Debit, Damage) for THIS order on this slip
            $outflows = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
                ->where('order_main_id', $order->id)
                ->whereNotIn('type', ['packing', 'packing_divert']) // Filter out packing and divert movements
                ->with(['product', 'color', 'size', 'rack.storeroom', 'responsibleStage', 'responsibleUnit'])
                ->get();

            // Fetch reworks specifically for THIS order
            $orderLots = \App\Models\OrderLot::where('order_main_id', $order->id)->pluck('lot_no')->toArray();
            $reworks = \App\Models\OrderStageTransaction::whereIn('lot_no', $orderLots)
                ->where('from_stage_id', 11) // From Packing
                ->where('sub_stage_id', $slip->stage_master_unit_id)
                ->where('status', 1)
                ->where('type', 'rework')
                ->where('to_stage_id', '!=', 13) // Exclude Godam/Outflow
                ->with(['toStage', 'toUnit', 'details'])
                ->get();
        }

        $unit_lots = [];
        if ($order) {
            $unit_lots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
                ->where('order_stage_transactions.to_stage_id', 11)
                ->where('order_stage_transactions.sub_stage_id_to', $slip->stage_master_unit_id)
                ->where('order_lots.order_main_id', $order->id)
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    'order_stage_transactions.quantity',
                    'order_stage_transactions.remaining_quantity'
                )
                ->get();
        }

        return view('admin.packing.process_domestic', compact('slip', 'packing', 'order', 'active_orders', 'packed_quantities', 'order_sets', 'unit_available', 'storerooms', 'outflows', 'reworks', 'domestic_masters', 'unit_available_per_lot', 'unit_lots'));
    }

    public function saveDomesticBox(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'product_id' => 'required',
            'size_set_id' => 'required',
            'color_id' => 'required',

            'quantity' => 'required|integer|min:1',
            'box_no' => 'nullable',
            'rack_id' => 'nullable'
        ]);

        \Log::channel('single')->info('Domestic Packing Start', $data);

        DB::beginTransaction();
        try {
            $main = $this->service->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $sizeSetMaster = \App\Models\MasterSizeMeasurement::find($data['size_set_id']);

            if (!$sizeSetMaster) {
                throw new \Exception("The selected size set does not exist.");
            }

            // Total pieces = sets quantity * pieces per set
            $total_sets = (int) $data['quantity'];
            $total_pieces_in_box = $total_sets * (int) $sizeSetMaster->no_of_pcs;

            \Log::channel('single')->info('Size Master pieces: ' . $sizeSetMaster->no_of_pcs . ', total sets: ' . $total_sets . ', total pieces in box: ' . $total_pieces_in_box);

            // Auto-generate carton no for domestic (1 box = 1 unique carton record)
            $lastCarton = \App\Models\PackingCarton::orderByRaw('CAST(carton_no AS UNSIGNED) DESC')->first();
            $nextCartonNo = ($lastCarton ? (int) $lastCarton->carton_no : 0) + 1;

            $carton = \App\Models\PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $nextCartonNo,
                'rack_id' => $data['rack_id'] ?? null,
                'status' => 1
            ]);

            $datePrefix = date('ymd');
            $box_no = $data['box_no'] ?? null;
            if (empty($box_no)) {
                $lastInv = \App\Models\DomesticInventory::where('box_no', 'LIKE', "BX-$datePrefix-%")
                    ->orderByRaw('CAST(SUBSTRING(box_no, 11) AS UNSIGNED) DESC')
                    ->first();
                $nextSeq = 1;
                if ($lastInv) {
                    $parts = explode('-', $lastInv->box_no);
                    $nextSeq = (int) end($parts) + 1;
                }
                $box_no = "BX-$datePrefix-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            }

            // Calculate barcode BEFORE creating box to link them properly
            $barcode = 'D' . $data['product_id'] . 'S' . $data['size_set_id'] . 'C' . $data['color_id'];

            \Log::channel('single')->info("Created Domestic Box: {$box_no} with Barcode: " . (string) $barcode);

            // Create or Update Domestic Inventory Record (stores total pieces)

            $inventory = \App\Models\DomesticInventory::where([
                'barcode' => $barcode,
                // 'quantity' => $total_pieces_in_box,
                // 'order_main_id' => $data['order_id'],
                'rack_id' => $data['rack_id'],
            ])->first();

            if ($inventory) {
                $inventory->increment('total_boxes');
            } else {
                $inventory = \App\Models\DomesticInventory::create([
                    // 'order_main_id' => $data['order_id'],
                    'packing_main_id' => $main->id,
                    'packing_carton_id' => $carton->id,
                    'rack_id' => $data['rack_id'],
                    'product_id' => $data['product_id'],
                    'color_id' => $data['color_id'],

                    'size_set_id' => $data['size_set_id'],
                    'quantity' => $total_pieces_in_box,
                    'box_no' => $box_no,
                    'carton_no' => $nextCartonNo,
                    'barcode' => $barcode,
                    'status' => 1
                ]);
            }

            // Log History
            \App\Models\DomesticInventoryHistory::create([
                'user_id' => auth()->id(),
                'new_product_id' => $data['product_id'],
                'new_size_set_id' => $data['size_set_id'],
                'new_color_id' => $data['color_id'],

                'new_rack_id' => $data['rack_id'] ?? null,
                'box_quantity' => 1,
                'type' => 'packing'
            ]);

            // Granular Deduction logic: Size-wise math for both Order and Unit Stock
            if (!empty($sizeSetMaster->size_group)) {
                $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                $sizeCounts = array_count_values($sizesInSet);
                $orderLots = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();

                \Log::channel('single')->info('Sizes in set:', $sizesInSet);
                \Log::channel('single')->info('Lots found: ' . implode(',', $orderLots));

                foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                    $remToDeduct = $pcsPerSet * $total_sets;

                    \Log::channel('single')->info("Processing Size: $sizeName to deduct total: $remToDeduct");

                    // 1. Deduct from Order Balance (OrderProductSetDetail)
                    // Matches by size string. Crucial for domestic orders with placeholder designs.
                    $orderDetails = \App\Models\OrderProductSetDetail::whereHas('orderProductSet', function ($q) use ($data) {
                        $q->where('order_main_id', $data['order_id']);
                    })
                        ->where('size', (string) $sizeName)
                        ->where('remaining_quantity', '>', 0)
                        ->get();

                    if ($orderDetails->isEmpty()) {
                        \Log::channel('single')->warning("No order details found for size: $sizeName and order: " . $data['order_id']);
                    }

                    $remOrder = $remToDeduct;
                    $firstDetailId = 0;
                    $firstDetailRecord = null;
                    foreach ($orderDetails as $od) {
                        if ($remOrder <= 0)
                            break;
                        if ($firstDetailId == 0) {
                            $firstDetailId = $od->id;
                            $firstDetailRecord = $od;
                        }
                        $deduct = min($od->remaining_quantity, $remOrder);
                        $od->remaining_quantity -= $deduct;
                        $od->save();
                        $remOrder -= $deduct;
                        \Log::channel('single')->info("Deducted $deduct from Detail ID: $od->id - Size: $od->size - Total Remaining Order Qty to deduct: $remOrder");
                    }

                    // 2. Deduct from Unit-Side Stock (OrderStageTransaction at Stage 11: Packing)
                    $stockTransactionsQuery = \App\Models\OrderStageTransaction::where('to_stage_id', 11) // Packing Stage
                        ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                        ->where('remaining_quantity', '>', 0);

                    if (!empty($data['lot_no'])) {
                        $stockTransactionsQuery->where('lot_no', $data['lot_no']);
                    } else {
                        $stockTransactionsQuery->whereIn('lot_no', $orderLots);
                    }

                    $stockTransactions = $stockTransactionsQuery->orderBy('id')->get();

                    if ($stockTransactions->isEmpty()) {
                        \Log::channel('single')->warning("No unit stock found for Stage 11 and Unit: " . $slip_details->stage_master_unit_id . " for Lot " . implode(',', $orderLots));
                    }

                    $remTrans = $remToDeduct;
                    foreach ($stockTransactions as $tx) {
                        if ($remTrans <= 0)
                            break;
                        $dedTrans = min($tx->remaining_quantity, $remTrans);
                        $tx->remaining_quantity -= $dedTrans;
                        $tx->save();
                        $remTrans -= $dedTrans;
                        \Log::channel('single')->info("Deducted $dedTrans from Unit Stock Transaction ID: $tx->id - New Balance: $tx->remaining_quantity");
                    }
                    if ($remTrans > 0) {
                        throw new \Exception("Insufficient stock at unit for size '{$sizeName}'. Required for this box: " . ($pcsPerSet * $total_sets) . ", Available at unit: " . (($pcsPerSet * $total_sets) - $remTrans));
                    }

                    // 3. Increment Log (ProductionOutflowInventory)
                    \App\Models\ProductionOutflowInventory::create([
                        'order_main_id' => $data['order_id'],
                        'slip_id' => $data['slip_id'],
                        'product_id' => $data['product_id'],
                        'color_id' => $data['color_id'],

                        'size_id' => $firstDetailId ?: 0,
                        'quantity' => $remToDeduct,
                        'per_piece_amount' => 0,
                        'total_amount' => 0,
                        'type' => 'packing',
                        'lot_no' => !empty($data['lot_no']) ? $data['lot_no'] : null,
                        'responsible_unit_id' => $slip_details->stage_master_unit_id,
                        'remarks' => "Box $box_no (Domestic) - Size: $sizeName",
                    ]);

                    // 4. Create PackingItem record for DISPATCH and REPORTING compatibility
                    // Calculate fallback price from order
                    $fallbackPrice = 0;
                    if ($firstDetailRecord && $firstDetailRecord->orderProductSet && $firstDetailRecord->orderProductSet->total_quantity > 0) {
                        $fallbackPrice = $firstDetailRecord->orderProductSet->basic_amount / $firstDetailRecord->orderProductSet->total_quantity;
                    }

                    \App\Models\PackingItem::create([
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'size_id' => $firstDetailId ?: 0,
                        'quantity' => ($pcsPerSet * $total_sets),
                        'selling_price' => $fallbackPrice,
                        'mrp' => 0
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Successfully packed Box $box_no with $total_sets sets ($total_pieces_in_box pieces)."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * NEW HYBRID LOGIC FOR CORPORATE ORDERS
     * This handles domestic-style packing but specifically for a corporate order.
     * It uses design matching to ensure pieces are deducted from the correct set in the corporate order.
     */
    public function saveCorporateDomesticBulk(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'boxes' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $main = $this->service->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $orderLots = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();

            // 1. Initial counters matching InventoryController logic
            $currentCartonNo = (int) \App\Models\DomesticInventory::max('carton_no') ?? 0;
            $lastInv = \App\Models\DomesticInventory::orderByRaw('CAST(SUBSTRING(box_no, 4) AS UNSIGNED) DESC')->first();
            $currentBoxNoInt = $lastInv ? (int) str_replace('BX-', '', $lastInv->box_no) : 0;

            $totalBoxesCreated = 0;
            foreach ($data['boxes'] as $box_plan) {
                $productMaster = \App\Models\ProductionGoods::findOrFail($box_plan['product_id']);
                $designNumber = $productMaster->design_number;
                $sizeSetMaster = \App\Models\MasterSizeMeasurement::findOrFail($box_plan['size_set_id']);

                $num_boxes_to_create = (int) $box_plan['quantity'];
                $pcs_per_set = (int) $sizeSetMaster->no_of_pcs;

                for ($i = 0; $i < $num_boxes_to_create; $i++) {
                    $currentCartonNo++;
                    $currentBoxNoInt++;
                    $box_no = 'BX-' . $currentBoxNoInt;

                    // 2. New Carton for EACH box (matching manual entry workflow)
                    $carton = \App\Models\PackingCarton::create([
                        'packing_main_id' => $main->id,
                        'carton_no' => $currentCartonNo,
                        'rack_id' => $box_plan['rack_id'] ?? null,
                        'status' => 1
                    ]);

                    // 4. Barcode Calculation
                    $barcode = 'D' . $box_plan['product_id'] . 'S' . $box_plan['size_set_id'] . 'C' . $box_plan['color_id'];

                    $currentRackId = $box_plan['rack_id'] ?? null;

                    $inventory = \App\Models\DomesticInventory::where([
                        'barcode' => $barcode,
                        'rack_id' => $currentRackId
                    ])->first();

                    if ($inventory) {
                        $inventory->increment('total_boxes');
                    } else {
                        $inventory = \App\Models\DomesticInventory::create([
                            // 'order_main_id' => 0,
                            'packing_main_id' => $main->id,
                            'packing_carton_id' => $carton->id,
                            'rack_id' => $currentRackId,
                            'product_id' => $box_plan['product_id'],
                            'color_id' => $box_plan['color_id'],
                            'size_set_id' => $box_plan['size_set_id'],

                            'quantity' => $pcs_per_set,
                            'box_no' => $box_no,
                            'carton_no' => $currentCartonNo,
                            'total_boxes' => 1,
                            'barcode' => $barcode,
                            'status' => 1
                        ]);
                    }

                    // Log History
                    \App\Models\DomesticInventoryHistory::create([
                        'user_id' => auth()->id(),
                        'new_product_id' => $box_plan['product_id'],
                        'new_size_set_id' => $box_plan['size_set_id'],
                        'new_color_id' => $box_plan['color_id'],

                        'new_rack_id' => $currentRackId,
                        'box_quantity' => 1,
                        'type' => 'packing'
                    ]);

                    // Redundant but safe

                    // 5. Piece Deductions from Original Corporate Order
                    if (!empty($sizeSetMaster->size_group)) {
                        $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                        $sizeCounts = array_count_values($sizesInSet);

                        foreach ($sizeCounts as $sizeName => $pcsInSet) {
                            $qtyToDeduct = $pcsInSet;

                            $orderDetails = \App\Models\OrderProductSetDetail::whereHas('orderProductSet', function ($q) use ($data, $designNumber) {
                                $q->where('order_main_id', $data['order_id'])->where('design_number', $designNumber);
                            })
                                ->where('size', (string) $sizeName)
                                ->where('remaining_quantity', '>', 0)->get();

                            if ($orderDetails->isEmpty()) {
                                throw new \Exception("Deduction failed for Design #$designNumber Size '$sizeName'.");
                            }

                            $remVal = $qtyToDeduct;
                            $usedDetailId = 0;
                            $priceSource = null;
                            foreach ($orderDetails as $od) {
                                if ($remVal <= 0)
                                    break;
                                if ($usedDetailId == 0) {
                                    $usedDetailId = $od->id;
                                    $priceSource = $od;
                                }
                                $canTake = min($od->remaining_quantity, $remVal);
                                $od->remaining_quantity -= $canTake;
                                $od->save();
                                $remVal -= $canTake;
                            }

                            $sellPrice = 0;
                            if ($priceSource && $priceSource->orderProductSet && $priceSource->orderProductSet->total_quantity > 0) {
                                $sellPrice = round($priceSource->orderProductSet->basic_amount / $priceSource->orderProductSet->total_quantity, 2);
                            }

                            \App\Models\PackingItem::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'size_id' => $usedDetailId ?: 0,
                                'quantity' => $qtyToDeduct,
                                'selling_price' => $sellPrice,
                                'mrp' => 0
                            ]);

                            // Outflow logging
                            \App\Models\ProductionOutflowInventory::create([
                                'order_main_id' => $data['order_id'],
                                'slip_id' => $data['slip_id'],
                                'product_id' => $box_plan['product_id'],
                                'color_id' => $box_plan['color_id'],
                                'size_set_id' => $box_plan['size_set_id'],
                                'size_id' => $usedDetailId ?: 0,
                                'quantity' => $qtyToDeduct,
                                'type' => 'packing_divert',
                                'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                'remarks' => "Bulk Divert to Domestic - Box $box_no",
                            ]);

                            // Stock from Unit stage
                            $stockTrans = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                                ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                                ->where('remaining_quantity', '>', 0)
                                ->whereIn('lot_no', $orderLots)
                                ->get();

                            $remStk = $qtyToDeduct;
                            foreach ($stockTrans as $tx) {
                                if ($remStk <= 0)
                                    break;
                                $take = min($tx->remaining_quantity, $remStk);
                                $tx->remaining_quantity -= $take;
                                $tx->save();
                                $remStk -= $take;
                            }
                        }
                    }
                    $totalBoxesCreated++;
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Successfully Diverted $totalBoxesCreated boxes."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    public function deleteDomesticBox($id)
    {
        DB::beginTransaction();
        try {
            // Find PackingItem instead of PackingBox
            $item = \App\Models\PackingItem::with(['carton.main', 'carton.rack'])->findOrFail($id);
            $carton = $item->carton;

            if ($carton && $carton->main && $carton->main->status == 1) {
                throw new \Exception("Cannot delete box from a finalized session.");
            }

            $orderId = ($carton->main) ? $carton->main->order_main_id : null;
            $slipId = ($carton->main) ? $carton->main->slip_id : null;
            
            // Re-fetch box No if needed
            $inv = \App\Models\DomesticInventory::where('packing_carton_id', $carton->id)->first();
            $boxNo = $inv ? $inv->box_no : null;

            $slip = \App\Models\ProductionSlipDigitization::find($slipId);
            $unitId = $slip ? $slip->stage_master_unit_id : null;

            // 1. Revert Inventories & Deductions using Packing Items
            $packingItems = \App\Models\PackingItem::where('packing_carton_id', $carton->id)->get();

            foreach ($packingItems as $item) {
                // Return to Order Balance
                $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                if ($od) {
                    $od->remaining_quantity += $item->quantity;
                    $od->save();
                }

                // Return to Unit Stock (Stage 11)
                if ($unitId) {
                    $orderLots = \App\Models\OrderLot::where('order_main_id', $orderId)->pluck('lot_no')->toArray();
                    $stockTx = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                        ->where('sub_stage_id_to', $unitId)
                        ->whereIn('lot_no', $orderLots)
                        ->where('status', 1)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($stockTx) {
                        $stockTx->remaining_quantity += $item->quantity;
                        $stockTx->save();
                    }
                }
            }

            $rackId = $carton ? $carton->rack_id : null;

            // 2. Adjust Domestic Inventory linked to this carton specifically for its rack
            $domesticInv = \App\Models\DomesticInventory::where('packing_carton_id', $carton->id)
                ->first();

            if ($domesticInv) {
                if ($domesticInv->total_boxes > 1) {
                    $domesticInv->decrement('total_boxes');
                } else {
                    $domesticInv->delete();
                }
            }

            \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();

            $cartonId = $carton->id;

            // In Domestic, each box has its own carton
            if ($cartonId) {
                \App\Models\PackingCarton::where('id', $cartonId)->delete();
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Domestic box deleted and inventory restored.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function downloadDomesticBarcodeTxt($id)
    {
        $inventory = \App\Models\DomesticInventory::with(['product.series', 'product.fitting', 'product.pattern', 'color', 'sizeSet'])->findOrFail($id);

        $label = (object) [
            'product_name' => ($inventory->product->series->name ?? '') . ' ' . ($inventory->product->name ?? ''),
            'fitting_name' => $inventory->fitting_name ?? 'N/A',
            'pattern_name' => $inventory->pattern_name ?? 'N/A',
            'size_group' => $inventory->sizeSet->name ?? 'N/A',
            'no_of_pcs' => $inventory->quantity ?? '0',
            'color_name' => ($inventory->color->name ?? 'N/A') . ' (' . ($inventory->color_id ?? '') . ')',
            'design_number' => $inventory->product->design_number ?? 'N/A',
            'barcode' => $inventory->barcode ?? ''
        ];

        $tspl = $this->buildTsplForLabels([$label]);
        $fileName = "barcode_" . ($inventory->box_no ?? $inventory->id) . ".txt";

        return response($tspl)
            ->withHeaders([
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
    }

    public function downloadSlipBarcodeTxt($packing_main_id)
    {
        $items = \App\Models\DomesticInventory::with(['product.series', 'product.fitting', 'product.pattern', 'color', 'sizeSet'])
            ->where('packing_main_id', $packing_main_id)
            ->get();

        if ($items->isEmpty()) {
            return response("No domestic labels found for this packing session.", 404);
        }

        $labels = [];
        foreach ($items as $item) {
            $labels[] = (object) [
                'product_name' => ($item->product->series->name ?? '') . ' ' . ($item->product->name ?? $item->product->name_of_garment ?? ''),
                'fitting_name' => $item->fitting_name ?? 'N/A',
                'pattern_name' => $item->pattern_name ?? 'N/A',
                'size_group' => $item->size_set_name ?? 'N/A',
                'no_of_pcs' => $item->quantity,
                'color_name' => ($item->color_name ?? 'N/A') . ' (' . $item->color_id . ')',
                'design_number' => $item->design_number ?? 'N/A',
                'barcode' => $item->barcode
            ];
        }

        $tspl = $this->buildTsplForLabels($labels);
        $fileName = 'slip_barcodes_' . $packing_main_id . '_' . time() . '.txt';

        return response($tspl, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    public function downloadOutflowBarcode($id)
    {
        $outflow = \App\Models\ProductionOutflowInventory::with([
            'product.series',
            'orderMain.OrderProductSets.product_set_details',
            'fitting',
            'pattern'
        ])->findOrFail($id);

        // We find the specific design and size from the related collections
        $detail = \App\Models\OrderProductSetDetail::find($outflow->size_id);
        $set = $detail ? $detail->orderProductSet : null;

        $label = (object) [
            'product_name' => ($outflow->product->series->name ?? '') . ' ' . ($outflow->product->name ?? $outflow->product->name_of_garment ?? ''),
            'fitting_name' => $outflow->fitting->name ?? 'N/A',
            'pattern_name' => $outflow->pattern->name ?? 'N/A',
            'size_group' => $detail->size ?? 'N/A',
            'no_of_pcs' => $outflow->quantity,
            'color_name' => ($set->colors->name ?? 'N/A') . ' (' . $outflow->color_id . ')',
            'design_number' => $set->design_number ?? 'N/A',
            'barcode' => $outflow->barcode
        ];

        $tspl = $this->buildTsplForLabels([$label]);
        $fileName = 'outflow_' . $outflow->id . '_' . time() . '.txt';

        return response($tspl, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    private function buildTsplForLabels($labels)
    {
        $tspl = "SIZE 100 mm,90 mm\r\nGAP 2 mm,0\r\nDIRECTION 1\r\nREFERENCE 0,0\r\nSPEED 2\r\nDENSITY 15\r\nCLS\r\n";
        $chunks = array_chunk($labels, 2);

        foreach ($chunks as $pair) {
            $left = $pair[0] ?? null;
            $right = $pair[1] ?? null;
            $tspl .= "CLS\r\nALIGN CENTER\r\n";

            if ($left) {
                $tspl .= "TEXT 80,90,\"3\",0,1,2,\"" . addslashes($left->product_name) . "\"\r\n";
                $tspl .= "TEXT 80,150,\"3\",0,1,2,\"" . addslashes($left->size_group) . "\"\r\n";
                $tspl .= "TEXT 80,210,\"3\",0,1,2,\"" . $left->no_of_pcs . " PCS\"\r\n";
                $tspl .= "TEXT 80,270,\"3\",0,1,2,\"" . addslashes($left->color_name) . "\"\r\n";
                $tspl .= "TEXT 50,340,\"2\",0,1,1,\"" . addslashes($left->pattern_name) . "\"\r\n";
                $tspl .= "TEXT 50,380,\"2\",0,1,1,\"" . addslashes($left->fitting_name) . "\"\r\n";
                $tspl .= "TEXT 50,420,\"1\",0,1,1,\"# " . $left->design_number . "\"\r\n";
                $tspl .= "BARCODE 50,470,\"128\",90,1,0,2,3,\"" . $left->barcode . "\"\r\n";
            }
            if ($right) {
                $tspl .= "TEXT 470,90,\"3\",0,1,2,\"" . addslashes($right->product_name) . "\"\r\n";
                $tspl .= "TEXT 470,150,\"3\",0,1,2,\"" . addslashes($right->size_group) . "\"\r\n";
                $tspl .= "TEXT 470,210,\"3\",0,1,2,\"" . $right->no_of_pcs . " PCS\"\r\n";
                $tspl .= "TEXT 470,270,\"3\",0,1,2,\"" . addslashes($right->color_name) . "\"\r\n";
                $tspl .= "TEXT 450,340,\"2\",0,1,1,\"" . addslashes($right->pattern_name) . "\"\r\n";
                $tspl .= "TEXT 450,380,\"2\",0,1,1,\"" . addslashes($right->fitting_name) . "\"\r\n";
                $tspl .= "TEXT 450,420,\"1\",0,1,1,\"# " . $right->design_number . "\"\r\n";
                $tspl .= "BARCODE 450,470,\"128\",90,1,0,2,3,\"" . $right->barcode . "\"\r\n";
            }
            $tspl .= "PRINT 1\r\n";
        }
        return $tspl;
    }

    public function getOrderDetailsJson($id, Request $request)
    {
        $unit_id = $request->unit_id;
        $order = \App\Models\OrderMain::with(['OrderProductSets.product_set_details', 'OrderProductSets.colors', 'OrderProductSets.size_measurement'])->findOrFail($id);

        $packed = $this->service->getPackedQuantitiesForOrder($id); // [detail_id => packed_qty]

        // Prepare Sets Data
        $sets = $order->OrderProductSets->map(function ($set) use ($packed) {
            $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
            $min_packed_sets = null;

            $set->details = $set->product_set_details->map(function ($detail) use ($packed, $set_total_qty, $set) {
                $item = $detail->toArray();
                $item['packed_qty'] = $packed[$detail->id] ?? 0;
                $item['qty_per_set'] = $detail->total_quantity / $set_total_qty;
                $item['design_number'] = $set->design_number;
                $item['color_name'] = $set->colors ? $set->colors->name : 'N/A';
                return (object) $item;
            });

            // Calculate how many full sets have been packed
            // Based on the detail with the LOWEST relative completion
            foreach ($set->details as $detail) {
                if ($detail->qty_per_set > 0) {
                    $sets_packed_for_this_detail = floor($detail->packed_qty / $detail->qty_per_set);
                    if ($min_packed_sets === null || $sets_packed_for_this_detail < $min_packed_sets) {
                        $min_packed_sets = $sets_packed_for_this_detail;
                    }
                }
            }
            $set->setAttribute('packed_sets', $min_packed_sets ?? 0);
            return $set;
        });

        $unit_available = $unit_id ? $this->service->getAvailableQuantitiesAtUnit($id, $unit_id) : [];

        // Flatten items for legacy view (if needed)
        $items = $sets->flatMap(function ($set) {
            return $set->details;
        });

        $items = $items->map(function ($item) use ($unit_available) {
            $item->unit_available_qty = $unit_available[$item->id] ?? 0;
            return $item;
        });

        // Also update sets
        $sets = $sets->map(function ($set) use ($unit_available) {
            $set->details = $set->details->map(function ($detail) use ($unit_available) {
                $detail->unit_available_qty = $unit_available[$detail->id] ?? 0;
                return $detail;
            });
            return $set;
        });

        $unit_lots = [];
        if ($unit_id) {
            $unit_lots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
                ->where('order_stage_transactions.to_stage_id', 11)
                ->where('order_stage_transactions.sub_stage_id_to', $unit_id)
                ->where('order_lots.order_main_id', $id)
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    'order_stage_transactions.quantity',
                    'order_stage_transactions.remaining_quantity'
                )
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'items' => $items,
            'sets' => $sets,
            'unit_available' => $unit_available,
            'unit_lots' => $unit_lots,
            'packing' => $this->service->getPackingMainWithStructure($request->slip_id)
        ]);
    }

    public function checkCartonNo(Request $request)
    {
        $result = $this->service->checkCartonNo($request->carton_no);
        return response()->json([
            'exists' => $result
        ]);
    }
    // API/AJAX Methods
    public function saveCarton(Request $request)
    {
        $data = $request->all();
        // Validation logic here
        $result = $this->service->saveCarton($data);
        return response()->json($result);
    }

    public function bulkSaveCarton(Request $request)
    {
        $data = $request->all();
        $result = $this->service->bulkSaveCarton($data);
        return response()->json($result);
    }

    public function saveBox(Request $request)
    {
        $data = $request->all();
        $result = $this->service->saveBox($data);
        return response()->json($result);
    }

    public function finalize(Request $request)
    {
        try {
            $result = $this->service->finalizePacking($request->packing_main_id, $request->completion_date);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createSet(Request $request)
    {
        $data = $request->all();
        $result = $this->service->createAdHocSet($data);
        return response()->json($result);
    }

    public function labels($type, $id)
    {
        $query = \App\Models\DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->select(
                'domestic_inventories.*',
                'production_goods.design_number',
                'production_goods.name_of_garment as product_name',
                'master_colors.name as color_name',
                'master_size_measurements.name as size_set_name'
            );

        if ($type == 'main') {
            $query->where('domestic_inventories.packing_main_id', $id);
        } elseif ($type == 'carton') {
            $query->where('domestic_inventories.packing_carton_id', $id);
        } elseif ($type == 'box') {
            $query->where('domestic_inventories.packing_carton_id', $id);
        } else {
            return abort(404);
        }

        $labels = $query->get();

        if ($labels->isEmpty()) {
            return redirect()->back()->withError('No labels found for this record.');
        }

        // Use DomPDF to generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing.labels_print', compact('labels', 'unit_available_per_lot'));

        // Set paper size to A4
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('labels-' . $type . '-' . $id . '.pdf');
    }

    public function saveMultiCartonPlan(Request $request)
    {
        $response = $this->service->saveMultiCartonPlan($request->all());
        return response()->json($response);
    }

    public function deleteCarton(Request $request)
    {
        $result = $this->service->deleteCarton($request->carton_id);
        return response()->json($result);
    }

    public function reassignRework(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'to_stage_id' => 'required',
            'to_unit_id' => 'required',
            'items' => 'required|array',
            'items.*.detail_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.lot_no' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->reassignRework($data);
        return response()->json($result);
    }

    public function deleteOutflow($id)
    {
        $result = $this->service->deleteOutflow($id);
        return response()->json($result);
    }

    public function deleteRework($id)
    {
        $result = $this->service->deleteRework($id);
        return response()->json($result);
    }

    public function getReworkStages(Request $request)
    {
        $stages = \App\Models\MasterProductStage::where('status', 1)
            ->whereIn('id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]) // All production stages before Packing
            ->get();
        return response()->json([
            'status' => 'success',
            'stages' => $stages
        ]);
    }

    public function getStageUnits(Request $request, $stageId)
    {
        $query = \App\Models\StageMasterUnit::where('status', 1);
        if (!$request->all) {
            $query->where('master_stage_id', $stageId);
        }
        $units = $query->get();
        return response()->json([
            'status' => 'success',
            'units' => $units
        ]);
    }
    public function recordDeadStock(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'rack_id' => 'required',
            'items' => 'required|array',
            'items.*.detail_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.lot_no' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->recordDeadStock($data);
        return response()->json($result);
    }

    public function recordSamplingStock(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'rack_id' => 'required',
            'items' => 'required|array',
            'items.*.detail_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.lot_no' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->recordSamplingStock($data);
        return response()->json($result);
    }
    public function recordUnitDebit(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'stage_id' => 'required',
            'unit_id' => 'required',
            'rack_id' => 'required',
            'discount' => 'nullable|numeric',
            'total_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.detail_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.per_piece_amount' => 'required|numeric|min:0',
            'items.*.lot_no' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->recordUnitDebit($data);
        return response()->json($result);
    }

    public function saveDomesticBulk(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'boxes' => 'required|array|min:1',
        ]);

        $slip_id = $request->slip_id;
        $order_id = $request->order_id;
        $boxesData = $request->boxes;

        DB::beginTransaction();
        try {
            $main = $this->service->getOrCreatePackingMain($slip_id, $order_id);
            $slip_details = \App\Models\ProductionSlipDigitization::find($slip_id);
            $orderLots = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();

            $totalBoxesProcessed = 0;

            foreach ($boxesData as $data) {
                $sizeSetMaster = \App\Models\MasterSizeMeasurement::find($data['size_set_id']);
                if (!$sizeSetMaster)
                    continue;

                $total_sets = (int) $data['quantity'];

                // Auto-generate carton no
                $lastCarton = \App\Models\PackingCarton::orderByRaw('CAST(carton_no AS UNSIGNED) DESC')->first();
                $nextCartonNo = ($lastCarton ? (int) $lastCarton->carton_no : 0) + 1;

                $carton = \App\Models\PackingCarton::create([
                    'packing_main_id' => $main->id,
                    'carton_no' => $nextCartonNo,
                    'rack_id' => $data['rack_id'] ?? null,
                    'status' => 1
                ]);

                $datePrefix = date('ymd');
                $lastInv = \App\Models\DomesticInventory::where('box_no', 'LIKE', "BX-$datePrefix-%")
                    ->orderByRaw('CAST(SUBSTRING(box_no, 11) AS UNSIGNED) DESC')
                    ->first();
                $nextSeq = 1;
                if ($lastInv) {
                    $parts = explode('-', $lastInv->box_no);
                    $nextSeq = (int) end($parts) + 1;
                }
                $box_no = "BX-$datePrefix-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                $barcode = 'D' . $data['product_id'] . 'S' . $data['size_set_id'] . 'C' . $data['color_id'];

                \Log::channel('single')->info("Created Domestic Bulk Box: {$box_no} with Barcode: " . (string) $barcode);

                $actualPiecesInBox = 0;
                if (!empty($sizeSetMaster->size_group)) {
                    $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                    $sizeCounts = array_count_values($sizesInSet);

                    foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                        $remToDeduct = $pcsPerSet * $total_sets;
                        $sizePiecesDeducted = 0;

                        // 1. Order Deduction
                        $orderDetails = \App\Models\OrderProductSetDetail::whereHas('orderProductSet', function ($q) use ($order_id) {
                            $q->where('order_main_id', $order_id);
                        })
                            ->where('size', (string) $sizeName)
                            ->where('remaining_quantity', '>', 0)
                            ->get();

                        $remOrder = $remToDeduct;
                        $firstDetailId = 0;
                        $firstDetailRecord = null;
                        foreach ($orderDetails as $od) {
                            if ($remOrder <= 0)
                                break;
                            if ($firstDetailId == 0) {
                                $firstDetailId = $od->id;
                                $firstDetailRecord = $od;
                            }
                            $deduct = min($od->remaining_quantity, $remOrder);
                            $od->remaining_quantity -= $deduct;
                            $od->save();
                            $remOrder -= $deduct;
                        }

                        // 2. Unit Stock Deduction
                        $stockTransactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                            ->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                            ->where('remaining_quantity', '>', 0)
                            ->whereIn('lot_no', $orderLots)
                            ->orderBy('id')
                            ->get();

                        $remTrans = $remToDeduct;
                        $sizePiecesDeducted = 0;
                        foreach ($stockTransactions as $tx) {
                            if ($remTrans <= 0)
                                break;
                            $dedTrans = min($tx->remaining_quantity, $remTrans);
                            $tx->remaining_quantity -= $dedTrans;
                            $tx->save();
                            $remTrans -= $dedTrans;
                            $sizePiecesDeducted += $dedTrans;
                        }

                        $actualPiecesInBox += $sizePiecesDeducted;

                        if ($sizePiecesDeducted > 0) {
                            // Calculate fallback price from order
                            $fallbackPrice = 0;
                            if ($firstDetailRecord && $firstDetailRecord->orderProductSet && $firstDetailRecord->orderProductSet->total_quantity > 0) {
                                $fallbackPrice = $firstDetailRecord->orderProductSet->basic_amount / $firstDetailRecord->orderProductSet->total_quantity;
                            }

                            \App\Models\PackingItem::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'size_id' => $firstDetailId ?: 0,
                                'quantity' => $sizePiecesDeducted,
                                'selling_price' => $fallbackPrice,
                                'mrp' => 0
                            ]);
                        }
                    }
                }

                // Check for existing record to group
                $inventoryResult = \App\Models\DomesticInventory::where([
                    // 'packing_main_id' => $main->id,
                    // 'packing_carton_id' => $carton->id,
                    'barcode' => $barcode,
                    'rack_id' => $data['rack_id']
                ])->first();

                if ($inventoryResult) {
                    $inventoryResult->increment('total_boxes');
                } else {
                    \App\Models\DomesticInventory::create([
                        'order_main_id' => $order_id,
                        'packing_main_id' => $main->id,
                        'packing_carton_id' => $carton->id,
                        'rack_id' => $data['rack_id'],
                        'product_id' => $data['product_id'],
                        'color_id' => $data['color_id'],

                        'size_set_id' => $data['size_set_id'],
                        'quantity' => $actualPiecesInBox,
                        'box_no' => $box_no,
                        'carton_no' => $nextCartonNo,
                        'total_boxes' => 1,
                        'barcode' => $barcode,
                        'status' => 1
                    ]);
                }

                $totalBoxesProcessed++;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed $totalBoxesProcessed boxes."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function downloadAllDomesticTxt($slip_id)
    {
        $packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
        if (!$packing) {
            return "No packing found.";
        }

        $inventories = \App\Models\DomesticInventory::where('packing_main_id', $packing->id)
            ->with(['product', 'color', 'sizeSet', 'pattern', 'fitting'])
            ->get();

        if ($inventories->isEmpty()) {
            return "No inventory found.";
        }

        $content = "";
        foreach ($inventories as $inv) {
            $content .= $inv->barcode . "\n";
        }

        $fileName = "slip_" . $slip_id . "_all_barcodes.txt";

        return response($content)
            ->withHeaders([
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
    }

    public function downloadAllDomesticBarcode($slip_id)
    {
        $packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
        if (!$packing) {
            return back()->withError("Packing session not found.");
        }

        $inventoryIds = \App\Models\DomesticInventory::where('packing_main_id', $packing->id)->pluck('id')->toArray();

        if (empty($inventoryIds)) {
            return back()->withError("No inventory items found for this slip.");
        }

        // Call the user's existing logic in BarcodeGeneratorController
        $request = new \Illuminate\Http\Request();
        $request->merge(['ids' => $inventoryIds]);

        return (new \App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController())->generateBulkTspl($request);
    }

    public function downloadPrn($id)
    {
        $main = \App\Models\PackingMain::with('cartons.items')->findOrFail($id);
        $allBarcodes = [];

        foreach ($main->cartons as $carton) {
            // As per updated user request: "if 5 boxes then 5 barcode" - now cartons
            if ($carton->barcode) {
                $allBarcodes[] = $carton->barcode;
            }
        }

        if (empty($allBarcodes)) {
            return back()->withError('No labels found to generate.');
        }

        // Use the global helper method
        $tspl = generateBulkTsplByBarcodes($allBarcodes);

        if (!$tspl) {
            return back()->withError('Failed to generate TSPL content.');
        }

        $filename = "bulk_labels_" . time() . ".prn";
        return response((string) $tspl, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}