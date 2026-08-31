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
                ->where('order_stage_transactions.remaining_quantity', '>', 0)
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

    public function processNew(Request $request, $slip_id)
    {
        $slip = $this->service->getSlipDetails($slip_id);
        $packing = $this->service->getPackingMainWithStructure($slip_id);

        if ($slip->status == 1 || ($packing && $packing->status == 1)) {
            if ($packing) {
                return redirect()->route('admin.packing.view', $packing->id)->with('info', 'This packing session is finalized.');
            }
            return redirect()->back()->withError('Already digitized slip.');
        }

        // If lots have already been selected or cartons saved for this session, lock Step 1 and redirect to Step 2
        $hasSelectedLots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->exists();
        $hasCartons = $packing && \App\Models\PackingCarton::where('packing_main_id', $packing->id)->exists();
        if ($hasSelectedLots || $hasCartons) {
            return redirect()->route('admin.packing.packLots', $slip_id);
        }

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

        // Both domestic and corporate handled in same view now

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
                ->where('order_stage_transactions.remaining_quantity', '>', 0)
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

        $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();

        return view('admin.packing.process_new', compact('slip', 'order', 'packing', 'storerooms', 'active_orders', 'packed_quantities', 'order_sets', 'unit_available', 'outflows', 'reworks', 'domestic_masters', 'unit_lots', 'unit_available_per_lot', 'selected_lots'));
    }

    public function saveSelectedLots(Request $request, $slip_id)
    {
        $request->validate([
            'order_id' => 'required|exists:order_main,id',
            'lots' => 'nullable|array'
        ]);

        $packing = $this->service->getOrCreatePackingMain($slip_id, $request->order_id);
        
        if (!$packing) {
            return redirect()->back()->withError('Could not create packing session.');
        }

        // Prevent re-submitting lot selection if already selected
        $hasSelectedLots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->exists();
        $hasCartons = \App\Models\PackingCarton::where('packing_main_id', $packing->id)->exists();
        if ($hasCartons) {
            return redirect()->route('admin.packing.packLots', $slip_id)->with('error', 'Cartons have already been created for this session. Please Reset Slip first if you wish to change lot selection.');
        }

        // Save order selection
        $packing->order_main_id = $request->order_id;
        $packing->save();

        // Clear existing lots for this slip
        \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->delete();

        // Save new lots
        if (!empty($request->lots)) {
            $unique_lots = array_unique($request->lots);
            foreach ($unique_lots as $lot_no) {
                \App\Models\PackingSelectedLot::create([
                    'packing_main_id' => $packing->id,
                    'slip_id' => $slip_id,
                    'lot_no' => $lot_no
                ]);
            }
        }

        return redirect()->route('admin.packing.packLots', $slip_id)->with('success', 'Lots successfully selected and saved.');
    }

    public function packLots(Request $request, $slip_id)
    {
        $packing = $this->service->getPackingMainWithStructure($slip_id);
        if (!$packing) {
            return redirect()->route('admin.packing.processNew', $slip_id)->withError('Packing session not found.');
        }

        if ($packing->status == 1) {
            return redirect()->route('admin.packing.view', $packing->id)->with('info', 'This packing session is finalized.');
        }

        $order = $packing->order;
        $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->unique()->toArray();

        if (empty($selected_lots)) {
            return redirect()->route('admin.packing.processNew', $slip_id)->withError('No lots selected. Please select lots first.');
        }

        // Fetch detailed info for these lots including sizes
        $lots_data = \Illuminate\Support\Facades\DB::table('order_lots')
            ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
            ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
            ->leftJoin('master_colors', 'order_products_sets.color_id', '=', 'master_colors.id')
            ->whereIn('order_lots.lot_no', $selected_lots)
            ->select(
                'order_lots.lot_no',
                'order_products_sets.id as set_id',
                'order_products_sets.design_number',
                'master_size_measurements.name as size_set_name',
                'order_products_sets.color_id',
                'master_colors.name as color_name',
                'order_products_sets.set_quantity as set_total_qty',
                'order_products_sets.set_size as master_size_set_id'
            )
            ->get();

        $stage_transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
            ->whereIn('lot_no', $selected_lots)
            ->with('details')
            ->get()
            ->groupBy('lot_no');

        $packed_by_lot_size = \App\Models\PackingItem::join('order_products_set_details', 'packing_items.size_id', '=', 'order_products_set_details.id')
            ->whereIn('packing_items.lot_no', $selected_lots)
            ->where('packing_items.packing_main_id', $packing->id)
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
            ->where('order_stage_transactions.production_slip_digitization_id', $slip_id)
            ->where('order_stage_transactions.from_stage_id', 11)
            ->where('order_stage_transactions.type', 'rework')
            ->select('order_stage_transactions.lot_no', 'order_stage_transaction_details.size', DB::raw('SUM(order_stage_transaction_details.quantity) as total'))
            ->groupBy('order_stage_transactions.lot_no', 'order_stage_transaction_details.size')
            ->get()
            ->groupBy('lot_no');

        $outflow_by_lot_size = \App\Models\ProductionOutflowInventory::join('order_products_set_details', 'production_outflow_inventories.size_id', '=', 'order_products_set_details.id')
            ->whereIn('production_outflow_inventories.lot_no', $selected_lots)
            ->where('production_outflow_inventories.slip_id', $slip_id)
            ->select('production_outflow_inventories.lot_no', 'order_products_set_details.size', DB::raw('SUM(production_outflow_inventories.quantity) as total'))
            ->groupBy('production_outflow_inventories.lot_no', 'order_products_set_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            })
            ->groupBy('lot_no');

        foreach ($lots_data as $lot) {
            $lot_txs = $stage_transactions->get($lot->lot_no, collect());
            $lot->remaining_quantity = (int) $lot_txs->sum('remaining_quantity');
            $lot->quantity = (int) $lot_txs->sum('quantity');
            $lot->transaction_id = $lot_txs->first() ? $lot_txs->first()->id : null;

            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;

            $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
            $total_tx_qty = (int) $lot_txs->sum('quantity');
            $ratio = $total_tx_qty > 0 ? min(1.0, $starting_lot_qty / $total_tx_qty) : 1;

            $incoming_sizes = [];
            foreach ($lot_txs as $tx) {
                if ($tx->details->isNotEmpty()) {
                    foreach ($tx->details as $d) {
                        $sz = trim(strtoupper($d->size));
                        $incoming_sizes[$sz] = ($incoming_sizes[$sz] ?? 0) + (int) round($d->quantity * $ratio);
                    }
                }
            }
            $lot->incoming_sizes = $incoming_sizes;
        }

        $set_ids = $lots_data->pluck('set_id')->unique()->toArray();
        $set_details = \App\Models\OrderProductSetDetail::whereIn('order_products_set_id', $set_ids)->get()->groupBy('order_products_set_id');

        $unique_designs = $lots_data->pluck('design_number')->unique()->values();
        $unique_colors = $lots_data->map(function($item) {
            return (object)['id' => $item->color_id, 'name' => $item->color_name];
        })->unique('id')->values();
        $storerooms = \App\Models\Storeroom::where('status', 1)->get();

        $saved_cartons = \App\Models\PackingCarton::with(['items.detail.orderProductSet.size_measurement'])
            ->where('packing_main_id', $packing->id)
            ->where('status', 1)
            ->get();

        $saved_reworks = \App\Models\OrderStageTransaction::whereIn('lot_no', $selected_lots)
            ->where('from_stage_id', 11)
            ->where(function($q) {
                $q->where('type', 'rework')->orWhere('type', 0);
            })
            ->with(['toStage', 'toUnit', 'details'])
            ->get();

        $saved_dead = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
            ->where('type', 'dead')
            ->with(['rack.storeroom', 'size'])
            ->get();

        $saved_sampling = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
            ->where('type', 'sampling')
            ->with(['rack.storeroom', 'size'])
            ->get();

        $saved_debit = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
            ->where('type', 'debit')
            ->with(['rack.storeroom', 'size', 'responsibleStage', 'responsibleUnit'])
            ->get();

        $saved_domestic = \App\Models\DomesticInventory::where('packing_main_id', $packing->id)
            ->with(['product', 'sizeSet', 'color', 'rack.storeroom'])
            ->get();

        $all_master_colors = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $all_size_sets = \App\Models\MasterSizeMeasurement::orderBy('name')->get(['id', 'name', 'size_group']);
        
        $all_designs = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->orderBy('design_number')
            ->pluck('design_number')
            ->unique()
            ->values();

        $lots_data = $lots_data->filter(function($lot) use ($packed_by_lot_size, $rework_by_lot_size, $outflow_by_lot_size) {
            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $has_session_activity = ($packed_for_lot > 0 || $rework_for_lot > 0 || $outflow_for_lot > 0);
            return $lot->remaining_quantity > 0 || $has_session_activity;
        })->values();

        // Calculate available sizes from selected lots
        $available_sizes = [];
        foreach ($set_details as $setId => $details) {
            foreach ($details as $detail) {
                if ($detail->size) {
                    $available_sizes[] = trim(strtoupper($detail->size));
                }
            }
        }
        $available_sizes = array_unique($available_sizes);

        $filtered_size_sets = \App\Models\MasterSizeMeasurement::where('status', 1)->get()->filter(function($sizeSet) use ($available_sizes) {
            if (empty($sizeSet->size_group)) return false;
            $sizes = array_map(function($s) {
                return trim(strtoupper($s));
            }, explode(',', $sizeSet->size_group));
            
            foreach ($sizes as $sz) {
                if (!in_array($sz, $available_sizes)) {
                    return false;
                }
            }
            return true;
        })->values();

        $designs_with_ids = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->orderBy('design_number')
            ->get(['id', 'design_number'])
            ->unique('design_number')
            ->values();

        return view('admin.packing.pack_lots', compact('slip_id', 'packing', 'order', 'lots_data', 'set_details', 'unique_designs', 'unique_colors', 'storerooms', 'saved_cartons', 'saved_reworks', 'saved_dead', 'saved_sampling', 'saved_debit', 'all_master_colors', 'all_size_sets', 'filtered_size_sets', 'designs_with_ids', 'saved_domestic', 'all_designs', 'packed_by_lot_size', 'rework_by_lot_size', 'outflow_by_lot_size'));
    }

    public function resetSlip($slip_id)
    {
        $result = $this->service->deletePackingSession($slip_id);
        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => 'Packing session successfully reset and balances restored.',
                'redirect_url' => route('admin.packing.processNew', $slip_id)
            ]);
        }
        return response()->json(['status' => 'error', 'message' => $result['message']]);
    }

    public function deleteSession($id)
    {
        $packing = \App\Models\PackingMain::find($id);
        if (!$packing) {
            return response()->json(['status' => 'error', 'message' => 'Packing session not found.'], 404);
        }

        // Check if any cartons are dispatched
        $cartonIds = \App\Models\PackingCarton::where('packing_main_id', $packing->id)->pluck('id')->toArray();
        $hasDispatchedCartons = \App\Models\PackingCarton::where('packing_main_id', $packing->id)->where('status', 2)->exists()
            || (!empty($cartonIds) && \App\Models\OrderDispatchDetails::whereIn('carton_packing_id', $cartonIds)->exists())
            || ($packing->order && $packing->order->status == 3);

        if ($hasDispatchedCartons) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete packing session because some or all cartons have already been dispatched.'], 400);
        }

        $result = $this->service->deletePackingSession($packing->slip_id ?: $packing->id);
        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => 'Packing session deleted and stock restored successfully.',
                'redirect_url' => route('admin.packing.index')
            ]);
        }
        return response()->json(['status' => 'error', 'message' => $result['message'] ?? 'Failed to delete packing session.'], 400);
    }

    // --- API Methods for Multi-Carton Planner ---

    public function apiGetSizeSets(Request $request, $slip_id)
    {
        $design = $request->design_number;
        $for_sampling = $request->for_sampling;
        $for_planner = $request->for_planner;

        if ($for_sampling) {
            // For sampling: load size sets strictly tied to the selected lots
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();
            $lots_size_set_ids = \Illuminate\Support\Facades\DB::table('order_lots')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->whereIn('order_lots.lot_no', $selected_lots)
                ->where('order_products_sets.design_number', $design)
                ->pluck('order_products_sets.set_size')
                ->unique()
                ->filter()
                ->toArray();

            $sizeMeasurements = \App\Models\MasterSizeMeasurement::whereIn('id', $lots_size_set_ids)
                ->where('status', 1)
                ->get();

            $size_sets = [];
            foreach ($sizeMeasurements as $sm) {
                $sizes = [];
                if (!empty($sm->size_group)) {
                    $sizes = array_map('trim', explode(',', $sm->size_group));
                }
                $size_sets[] = [
                    'id' => $sm->id,
                    'name' => $sm->name,
                    'required_sizes' => $sizes,
                    'no_of_pcs' => $sm->no_of_pcs
                ];
            }

            return response()->json([
                'status' => 'success',
                'size_sets' => $size_sets
            ]);
        }

        if ($for_planner) {
            // For planner: load all size sets that can be made from the available lot sizes
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();
            $available_sizes = \Illuminate\Support\Facades\DB::table('order_lots')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->join('order_products_set_details', 'order_products_sets.id', '=', 'order_products_set_details.order_products_set_id')
                ->whereIn('order_lots.lot_no', $selected_lots)
                ->where('order_products_sets.design_number', $design)
                ->pluck('order_products_set_details.size')
                ->map(function($size) { return trim(strtoupper($size)); })
                ->unique()
                ->toArray();

            $allSizeMeasurements = \App\Models\MasterSizeMeasurement::where('status', 1)->get();
            $size_sets = [];
            
            foreach ($allSizeMeasurements as $sm) {
                if (empty($sm->size_group)) continue;
                
                $sizes = array_map(function($s) { return trim(strtoupper($s)); }, explode(',', $sm->size_group));
                
                // Check if this size set is a subset of available_sizes
                $canBeMade = empty(array_diff($sizes, $available_sizes));
                
                if ($canBeMade) {
                    $size_sets[] = [
                        'id' => $sm->id,
                        'name' => $sm->name,
                        'required_sizes' => $sizes,
                        'no_of_pcs' => $sm->no_of_pcs
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'size_sets' => $size_sets
            ]);
        }

        $products = \App\Models\ProductionGoods::with(['variants.sizeSet'])
            ->where('design_number', $design)
            ->where('status', 1)
            ->get();

        $size_sets = [];
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                if ($variant->sizeSet) {
                    $sizes = [];
                    if (!empty($variant->sizeSet->size_group)) {
                        $sizes = array_map('trim', explode(',', $variant->sizeSet->size_group));
                    }
                    $size_sets[] = [
                        'id' => $variant->sizeSet->id,
                        'name' => $variant->sizeSet->name,
                        'required_sizes' => $sizes,
                        'no_of_pcs' => $variant->sizeSet->no_of_pcs
                    ];
                }
            }
        }

        // Remove duplicates by ID
        $unique_size_sets = collect($size_sets)->unique('id')->values()->all();

        return response()->json([
            'status' => 'success',
            'size_sets' => $unique_size_sets
        ]);
    }

    public function apiGetMasterData(Request $request, $slip_id)
    {
        $design = $request->design_number;
        $size_set_id = $request->size_set_id;
        
        // Find or create product dynamically
        $product = \App\Models\ProductionGoods::where('design_number', $design)
            ->where('status', 1)
            ->first();
            
        if (!$product) {
            $product = \App\Models\ProductionGoods::create([
                'design_number' => $design,
                'name' => 'Design ' . $design,
                'sku' => 'DSN-' . $design,
                'status' => 1
            ]);
        }
        
        if (is_string($size_set_id) && strpos($size_set_id, 'loose_') === 0) {
            $singleSize = str_replace('loose_', '', $size_set_id);
            $sizeSetModel = \App\Models\MasterSizeMeasurement::where('size_group', $singleSize)
                ->where('no_of_pcs', 1)
                ->where('status', 1)
                ->first();

            if (!$sizeSetModel) {
                $sizeSetModel = \App\Models\MasterSizeMeasurement::create([
                    'name' => "{$singleSize} (1 pcs)",
                    'size_group' => $singleSize,
                    'no_of_pcs' => 1,
                    'status' => 1
                ]);
            }
            $size_set_id = $sizeSetModel->id;
        } else {
            $size_set_id = empty($size_set_id) ? null : $size_set_id;
        }
        
        // Find or create variant dynamically
        $variant = null;
        if ($size_set_id) {
            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $product->id)
                ->where('master_size_measurement_id', $size_set_id)
                ->first();
                
            if (!$variant) {
                $variant = \App\Models\ProductionGoodVariant::create([
                    'production_goods_id' => $product->id,
                    'master_size_measurement_id' => $size_set_id,
                    'mrp' => 0,
                    'price' => 0,
                    'status' => 1
                ]);
            }
        }
        
        $mrp = $variant ? $variant->mrp : 0;
        $price = $variant ? ($variant->price ?? 0) : 0;
        
        if ($request->strict_colors && $variant) {
            $colors = \App\Models\ProductionGoodVariantItem::where('variant_id', $variant->id)
                ->join('master_colors', 'production_goods_variant_colors.master_color_id', '=', 'master_colors.id')
                ->where('master_colors.status', 1)
                ->orderBy('master_colors.name')
                ->get(['master_colors.id', 'master_colors.name']);
        } else {
            $colors = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name']);
        }
        
        // Calculate size-wise available balances from the selected lots (regardless of design number)
        $packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
        $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->unique()->toArray();
        
        $packed_by_lot_size = \App\Models\PackingItem::join('order_products_set_details', 'packing_items.size_id', '=', 'order_products_set_details.id')
            ->whereIn('packing_items.lot_no', $selected_lots)
            ->where('packing_items.packing_main_id', $packing ? $packing->id : 0)
            ->select('packing_items.lot_no', 'order_products_set_details.size', \DB::raw('SUM(packing_items.quantity) as total'))
            ->groupBy('packing_items.lot_no', 'order_products_set_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            })
            ->groupBy('lot_no');

        $rework_by_lot_size = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transaction_details.order_stage_transaction_id', '=', 'order_stage_transactions.id')
            ->whereIn('order_stage_transactions.lot_no', $selected_lots)
            ->where('order_stage_transactions.production_slip_digitization_id', $slip_id)
            ->where('order_stage_transactions.from_stage_id', 11)
            ->where('order_stage_transactions.type', 'rework')
            ->select('order_stage_transactions.lot_no', 'order_stage_transaction_details.size', \DB::raw('SUM(order_stage_transaction_details.quantity) as total'))
            ->groupBy('order_stage_transactions.lot_no', 'order_stage_transaction_details.size')
            ->get()
            ->groupBy('lot_no');

        $outflow_by_lot_size = \App\Models\ProductionOutflowInventory::join('order_products_set_details', 'production_outflow_inventories.size_id', '=', 'order_products_set_details.id')
            ->whereIn('production_outflow_inventories.lot_no', $selected_lots)
            ->where('production_outflow_inventories.slip_id', $slip_id)
            ->select('production_outflow_inventories.lot_no', 'order_products_set_details.size', \DB::raw('SUM(production_outflow_inventories.quantity) as total'))
            ->groupBy('production_outflow_inventories.lot_no', 'order_products_set_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            })
            ->groupBy('lot_no');

        $lots_data = \Illuminate\Support\Facades\DB::table('order_lots')
            ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
            ->whereIn('order_lots.lot_no', $selected_lots)
            ->select('order_lots.lot_no', 'order_products_sets.id as set_id')
            ->get();
            
        $stage_transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
            ->whereIn('lot_no', $selected_lots)
            ->with('details')
            ->get()
            ->groupBy('lot_no');

        $set_ids = $lots_data->pluck('set_id')->unique()->toArray();
        $set_details = \App\Models\OrderProductSetDetail::whereIn('order_products_set_id', $set_ids)->get()->groupBy('order_products_set_id');

        $available_pieces = 0;
        $available_balances = [];
        
        foreach ($lots_data as $lot) {
            $transactions = $stage_transactions->get($lot->lot_no, collect());
            $rem_qty = (int) $transactions->sum('remaining_quantity');
            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;

            $starting_lot_qty = $rem_qty + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
            $total_tx_qty = (int) $transactions->sum('quantity');
            $ratio = $total_tx_qty > 0 ? min(1.0, $starting_lot_qty / $total_tx_qty) : 1;

            $incoming_sizes = [];
            foreach ($transactions as $tx) {
                if ($tx->details->isNotEmpty()) {
                    foreach ($tx->details as $d) {
                        $sz = trim(strtoupper($d->size));
                        $incoming_sizes[$sz] = ($incoming_sizes[$sz] ?? 0) + (int) round($d->quantity * $ratio);
                    }
                }
            }

            if (isset($set_details[$lot->set_id])) {
                $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
                $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
                $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
                $starting_lot_qty = $transactions->sum('remaining_quantity') + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                
                foreach ($set_details[$lot->set_id] as $detail) {
                    $sizeName = trim(strtoupper($detail->size));
                    $incoming_qty = isset($incoming_sizes[$sizeName]) 
                        ? (int) $incoming_sizes[$sizeName] 
                        : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                    
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
                    
                    $live = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);
                    $available_pieces += $live;
                    
                    if (!isset($available_balances[$sizeName])) {
                        $available_balances[$sizeName] = 0;
                    }
                    $available_balances[$sizeName] += $live;
                }
            }
        }
        
        $no_of_pcs = 1;
        if ($size_set_id) {
            $sizeSetModel = \App\Models\MasterSizeMeasurement::find($size_set_id);
            $no_of_pcs = $sizeSetModel ? ($sizeSetModel->no_of_pcs ?: 1) : 1;
        }
        
        $max_sets = $no_of_pcs > 0 ? floor($available_pieces / $no_of_pcs) : $available_pieces;
        
        // Fetch variant details for color dropdown
        $colors_list = [];
        if ($request->for_sampling) {
            $colors_list = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name'])->toArray();
        } else {
            $variant_first = null;
            if ($size_set_id) {
                $variant_first = \App\Models\ProductionGoodVariant::where('production_goods_id', $product->id)
                    ->where('master_size_measurement_id', $size_set_id)
                    ->first();
            }
            
            if ($variant_first) {
                $colors_list = \App\Models\ProductionGoodVariantItem::where('variant_id', $variant_first->id)
                    ->with('color')
                    ->get()
                    ->map(function($item) {
                        return $item->color ? ['id' => $item->color->id, 'name' => $item->color->name] : null;
                    })
                    ->filter()
                    ->values()
                    ->all();
            }
            if (empty($colors_list)) {
                $colors_list = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name'])->toArray();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'product_id' => $product->id,
            'mrp' => $mrp,
            'price' => $price,
            'colors' => $colors_list,
            'available_pieces' => (int) $available_pieces,
            'no_of_pcs' => (int) $no_of_pcs,
            'max_sets' => (int) $max_sets,
            'available_balances' => $available_balances
        ]);
    }

    public function apiSaveCartonPlan(Request $request, $slip_id)
    {
        $packing = $this->service->getPackingMainWithStructure($slip_id);
        if (!$packing) return response()->json(['status' => 'error', 'message' => 'Packing session not found']);

        $cartons = $request->cartons;
        if (empty($cartons)) return response()->json(['status' => 'error', 'message' => 'No cartons provided']);

        \Log::channel('single')->info('Bulk Save Carton Plan Start', ['count' => count($cartons)]);

        \DB::beginTransaction();
        try {
            $now = now();
            
            $stageTransactionsToUpdate = [];

            foreach ($cartons as $carton) {
                // Ensure product variant exists if it's a Box type (numeric size_set_id)
                if (isset($carton['size_set_id']) && is_numeric($carton['size_set_id']) && isset($carton['design']) && isset($carton['color_id'])) {
                    $product = \App\Models\ProductionGoods::where('design_number', $carton['design'])->first();
                    if ($product) {
                        $this->ensureProductVariantExists($product->id, $carton['size_set_id'], $carton['color_id']);
                    }
                }

                $cartonModel = \App\Models\PackingCarton::create([
                    'packing_main_id' => $packing->id,
                    'carton_no' => $carton['carton_no'],
                    'rack_id' => $carton['rack_id'] ?? null,
                    'barcode' => $carton['barcode'] ?? null,
                    'note' => json_encode([
                        'design' => $carton['design'] ?? 'N/A',
                        'size_set_name' => $carton['size_set_name'] ?? 'N/A',
                        'color_name' => $carton['color_name'] ?? 'N/A'
                    ]),
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                if (isset($carton['items']) && is_array($carton['items'])) {
                    foreach ($carton['items'] as $item) {
                        $lot_no = $item['lot_no'] ?? null;

                        if (!$lot_no) {
                            throw new \Exception("Missing lot information for size " . ($item['size_name'] ?? ''));
                        }

                        \App\Models\PackingItem::create([
                            'packing_main_id' => $packing->id,
                            'packing_carton_id' => $cartonModel->id,
                            'size_id' => $item['size_id'] ?? 0,
                            'lot_no' => $lot_no,
                            'total_boxes' => 1,
                            'quantity' => $item['quantity'],
                            'mrp' => $carton['mrp'] ?? 0,
                            'selling_price' => $carton['price'] ?? 0
                        ]);

                        if (!isset($stageTransactionsToUpdate[$lot_no])) {
                            $stageTransactionsToUpdate[$lot_no] = 0;
                        }
                        $stageTransactionsToUpdate[$lot_no] += $item['quantity'];
                    }
                }
            }
            
            // Validate and Deduct remaining quantities across all available stage transactions for each lot
            foreach($stageTransactionsToUpdate as $lot_no => $deductQty) {
                $lotTxs = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                    ->where('lot_no', $lot_no)
                    ->where('to_stage_id', 11)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalAvailable = $lotTxs->sum('remaining_quantity');
                if ($totalAvailable < $deductQty) {
                    throw new \Exception("Insufficient overall quantity in lot {$lot_no}. Available: {$totalAvailable}, Required: {$deductQty}");
                }

                $remainingToDeduct = $deductQty;
                foreach ($lotTxs as $tx) {
                    if ($remainingToDeduct <= 0) break;
                    $take = min($tx->remaining_quantity, $remainingToDeduct);
                    \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                        ->where('id', $tx->id)
                        ->decrement('remaining_quantity', $take);
                    $remainingToDeduct -= $take;
                }
            }
            
            \DB::commit();
            return response()->json(['status' => 'success', 'message' => count($cartons) . ' cartons planned successfully']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::channel('single')->error('Bulk Save Carton Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function apiDeleteCarton(Request $request, $slip_id, $carton_id)
    {
        $carton = \App\Models\PackingCarton::with('items')->find($carton_id);
        if (!$carton) return response()->json(['status' => 'error', 'message' => 'Carton not found']);
        
        \DB::beginTransaction();
        try {
            foreach ($carton->items as $item) {
                // Refund order_stage_transactions.remaining_quantity across transactions for lot_no
                $refundQty = $item->quantity;
                $txs = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                    ->where('lot_no', $item->lot_no)
                    ->where('to_stage_id', 11)
                    ->orderBy('id', 'desc')
                    ->get();

                if ($txs->isNotEmpty()) {
                    foreach ($txs as $tx) {
                        if ($refundQty <= 0) break;
                        $maxAdd = max(0, $tx->quantity - $tx->remaining_quantity);
                        $add = min($refundQty, $maxAdd > 0 ? $maxAdd : $refundQty);
                        \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                            ->where('id', $tx->id)
                            ->increment('remaining_quantity', $add);
                        $refundQty -= $add;
                    }
                    if ($refundQty > 0) {
                        \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                            ->where('id', $txs->first()->id)
                            ->increment('remaining_quantity', $refundQty);
                    }
                }
            }
            
            \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
            $carton->delete();
            
            \DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to delete']);
        }
    }

    public function bulkDeleteCartons(Request $request, $slip_id)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No cartons selected for deletion']);
        }

        \DB::beginTransaction();
        try {
            $cartons = \App\Models\PackingCarton::with('items')->whereIn('id', $ids)->get();
            
            foreach ($cartons as $carton) {
                foreach ($carton->items as $item) {
                    // Refund order_stage_transactions.remaining_quantity across transactions for lot_no
                    $refundQty = $item->quantity;
                    $txs = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                        ->where('lot_no', $item->lot_no)
                        ->where('to_stage_id', 11)
                        ->orderBy('id', 'desc')
                        ->get();

                    if ($txs->isNotEmpty()) {
                        foreach ($txs as $tx) {
                            if ($refundQty <= 0) break;
                            $maxAdd = max(0, $tx->quantity - $tx->remaining_quantity);
                            $add = min($refundQty, $maxAdd > 0 ? $maxAdd : $refundQty);
                            \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                                ->where('id', $tx->id)
                                ->increment('remaining_quantity', $add);
                            $refundQty -= $add;
                        }
                        if ($refundQty > 0) {
                            \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                                ->where('id', $txs->first()->id)
                                ->increment('remaining_quantity', $refundQty);
                        }
                    }
                }
                
                \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
                $carton->delete();
            }
            
            \DB::commit();
            return response()->json(['status' => 'success', 'message' => count($cartons) . ' cartons deleted successfully']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to delete selected cartons.']);
        }
    }

    public function apiDeleteDomestic(Request $request, $slip_id, $id)
    {
        \DB::beginTransaction();
        try {
            $domesticInv = \App\Models\DomesticInventory::findOrFail($id);
            $carton = \App\Models\PackingCarton::find($domesticInv->packing_carton_id);
            
            if ($carton) {
                $packingItems = \App\Models\PackingItem::where('packing_carton_id', $carton->id)->get();
                $slip = \App\Models\ProductionSlipDigitization::find($slip_id);
                $unitId = $slip ? $slip->stage_master_unit_id : null;
                $orderId = $domesticInv->order_main_id;

                foreach ($packingItems as $item) {
                    // Return to Order Balance
                    $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                    if ($od) {
                        $od->remaining_quantity += $item->quantity;
                        $od->save();
                    }

                    // Return to Unit Stock (Stage 11) using the lot_no stored in the item or fallback to transactions
                    $lotNo = $item->lot_no;
                    if ($lotNo) {
                        $stockTx = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                            ->where('sub_stage_id_to', $unitId)
                            ->where('lot_no', $lotNo)
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($stockTx) {
                            $stockTx->remaining_quantity += $item->quantity;
                            $stockTx->save();
                        }
                    } else if ($unitId) {
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

                \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
                $carton->delete();
            }

            $domesticInv->delete();

            \DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Domestic box deleted and inventory restored.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
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
                ->where('order_stage_transactions.remaining_quantity', '>', 0)
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
            // Resolve the DomesticInventory row by ID
            $domesticInv = \App\Models\DomesticInventory::find($id);
            if (!$domesticInv) {
                return response()->json(['status' => 'error', 'message' => 'Domestic inventory record not found.']);
            }

            // Load PackingMain to get slip_id
            $packingMain = \App\Models\PackingMain::find($domesticInv->packing_main_id);
            if ($packingMain && $packingMain->status == 1) {
                throw new \Exception('Cannot delete box from a finalized session.');
            }

            $slipId = $packingMain ? $packingMain->slip_id : null;
            $slip = \App\Models\ProductionSlipDigitization::find($slipId);
            $unitId = $slip ? $slip->stage_master_unit_id : null;

            // Find all cartons in this session for this barcode+rack (new entries have barcode set)
            if (!empty($domesticInv->barcode)) {
                $cartons = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id)
                    ->where('rack_id', $domesticInv->rack_id)
                    ->where('barcode', $domesticInv->barcode)
                    ->get();
            } else {
                // Older entries: match by packing_main_id + rack_id + no barcode
                $cartons = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id)
                    ->where('rack_id', $domesticInv->rack_id)
                    ->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); })
                    ->get();
            }

            if ($cartons->isEmpty()) {
                throw new \Exception('No cartons found for deletion.');
            }

            $totalCartonsToDelete = $cartons->count();

            // Restore stock for each carton
            foreach ($cartons as $c) {
                $packingItems = \App\Models\PackingItem::where('packing_carton_id', $c->id)->get();
                foreach ($packingItems as $item) {
                    // Restore OrderProductSetDetail balance
                    $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                    if ($od) {
                        $od->remaining_quantity += $item->quantity;
                        $od->save();
                    }

                    // Restore OrderStageTransaction remaining quantity
                    if ($unitId && $item->lot_no) {
                        $stockTx = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                            ->where('sub_stage_id_to', $unitId)
                            ->where('lot_no', $item->lot_no)
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($stockTx) {
                            $stockTx->remaining_quantity = min($stockTx->quantity, $stockTx->remaining_quantity + $item->quantity);
                            $stockTx->save();
                        }
                    }
                }

                \App\Models\PackingItem::where('packing_carton_id', $c->id)->delete();
                $c->delete();
            }

            // Decrement or delete the domestic inventory row
            if ($domesticInv->total_boxes > $totalCartonsToDelete) {
                $domesticInv->total_boxes -= $totalCartonsToDelete;
                $domesticInv->save();
            } else {
                $domesticInv->delete();
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Domestic box deleted and inventory restored.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function bulkDeleteDomestic(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No records selected.']);
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $domesticInv = \App\Models\DomesticInventory::find($id);
                if (!$domesticInv) {
                    continue;
                }

                // Load PackingMain to get slip_id
                $packingMain = \App\Models\PackingMain::find($domesticInv->packing_main_id);
                if ($packingMain && $packingMain->status == 1) {
                    throw new \Exception('Cannot delete box from a finalized session.');
                }

                $slipId = $packingMain ? $packingMain->slip_id : null;
                $slip = \App\Models\ProductionSlipDigitization::find($slipId);
                $unitId = $slip ? $slip->stage_master_unit_id : null;

                // Find all cartons for this domestic inventory entry
                if (!empty($domesticInv->barcode)) {
                    $cartons = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id)
                        ->where('rack_id', $domesticInv->rack_id)
                        ->where('barcode', $domesticInv->barcode)
                        ->get();
                } else {
                    $cartons = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id)
                        ->where('rack_id', $domesticInv->rack_id)
                        ->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); })
                        ->get();
                }

                $totalCartonsToDelete = $cartons->count();

                foreach ($cartons as $c) {
                    $packingItems = \App\Models\PackingItem::where('packing_carton_id', $c->id)->get();
                    foreach ($packingItems as $item) {
                        // Restore OrderProductSetDetail balance
                        $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                        if ($od) {
                            $od->remaining_quantity += $item->quantity;
                            $od->save();
                        }

                        // Restore OrderStageTransaction remaining quantity
                        if ($unitId && $item->lot_no) {
                            $stockTx = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
                                ->where('sub_stage_id_to', $unitId)
                                ->where('lot_no', $item->lot_no)
                                ->orderBy('id', 'desc')
                                ->first();
                            if ($stockTx) {
                                $stockTx->remaining_quantity = min($stockTx->quantity, $stockTx->remaining_quantity + $item->quantity);
                                $stockTx->save();
                            }
                        }
                    }
                    \App\Models\PackingItem::where('packing_carton_id', $c->id)->delete();
                    $c->delete();
                }

                if ($totalCartonsToDelete > 0 && $domesticInv->total_boxes > $totalCartonsToDelete) {
                    $domesticInv->total_boxes -= $totalCartonsToDelete;
                    $domesticInv->save();
                } else {
                    $domesticInv->delete();
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Selected domestic entries deleted successfully.']);
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
                ->where('order_stage_transactions.remaining_quantity', '>', 0)
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

    public function bulkDeleteOutflow(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No records selected.']);
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $res = $this->service->deleteOutflow($id);
                if (isset($res['status']) && $res['status'] === 'error') {
                    throw new \Exception($res['message'] ?? 'Failed to delete record ID: ' . $id);
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Selected records deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
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
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();

            $totalBoxesProcessed = 0;

            foreach ($boxesData as $data) {
                $sizeSetMaster = \App\Models\MasterSizeMeasurement::find($data['size_set_id']);
                if (!$sizeSetMaster)
                    continue;

                // Ensure product variant and color mapping exist in database
                $this->ensureProductVariantExists($data['product_id'], $data['size_set_id'], $data['color_id']);

                $total_sets = (int) $data['quantity'];

                // Sync fitting and pattern from corporate order set to production_goods product if not set
                $orderSet = \App\Models\OrderProductSet::where('order_main_id', $order_id)
                    ->where('production_goods_id', $data['product_id'])
                    ->first();
                if ($orderSet) {
                    $prod = \App\Models\ProductionGoods::find($data['product_id']);
                    if ($prod && (empty($prod->master_product_fitting_id) || empty($prod->master_pattern_id))) {
                        $prod->update([
                            'master_product_fitting_id' => $orderSet->master_product_fitting_id ?? $prod->master_product_fitting_id,
                            'master_pattern_id' => $orderSet->master_design_pattern_id ?? $prod->master_pattern_id
                        ]);
                    }
                }

                $barcode = 'D' . $data['product_id'] . 'S' . $data['size_set_id'] . 'C' . $data['color_id'];

                $existingInv = \App\Models\DomesticInventory::where('barcode', $barcode)
                    ->where('rack_id', $data['rack_id'])
                    ->first();

                for ($set_i = 1; $set_i <= $total_sets; $set_i++) {
                    // Auto-generate carton no
                    $lastCarton = \App\Models\PackingCarton::orderByRaw('CAST(carton_no AS UNSIGNED) DESC')->first();
                    $nextCartonNo = ($lastCarton ? (int) $lastCarton->carton_no : 0) + 1;

                    $carton = \App\Models\PackingCarton::create([
                        'packing_main_id' => $main->id,
                        'carton_no' => $nextCartonNo,
                        'rack_id' => $data['rack_id'] ?? null,
                        'barcode' => $barcode,
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

                    \Log::channel('single')->info("Created Domestic Bulk Box: {$box_no} with Barcode: " . (string) $barcode);

                    $actualPiecesInBox = 0;
                    if (!empty($sizeSetMaster->size_group)) {
                        $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                        $sizeCounts = array_count_values($sizesInSet);

                        foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                            $remToDeduct = $pcsPerSet * 1; // 1 set per box
                            $sizePiecesDeducted = 0;

                            // 1. Get Stage Transactions for this size from selected lots and process deductions
                            $stockTransactions = \App\Models\OrderStageTransaction::where('order_stage_transactions.to_stage_id', 11)
                                ->where('order_stage_transactions.sub_stage_id_to', $slip_details->stage_master_unit_id)
                                ->where('order_stage_transactions.remaining_quantity', '>', 0)
                                ->whereIn('order_stage_transactions.lot_no', $selected_lots)
                                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                                ->join('order_products_set_details', 'order_lots.order_products_set_id', '=', 'order_products_set_details.order_products_set_id')
                                ->where('order_products_set_details.size', (string) $sizeName)
                                ->select('order_stage_transactions.*', 'order_products_set_details.id as matching_size_detail_id')
                                ->orderBy('order_stage_transactions.id')
                                ->get();

                            $remTrans = $remToDeduct;
                            foreach ($stockTransactions as $tx) {
                                if ($remTrans <= 0)
                                    break;
                                $dedTrans = min($tx->remaining_quantity, $remTrans);
                                $tx->remaining_quantity -= $dedTrans;
                                $tx->save();
                                $remTrans -= $dedTrans;
                                $sizePiecesDeducted += $dedTrans;

                                if ($dedTrans > 0) {
                                    // Deduct from matching size detail balance
                                    $od = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($tx->matching_size_detail_id);
                                    if ($od) {
                                        $od->remaining_quantity = max(0, $od->remaining_quantity - $dedTrans);
                                        $od->save();
                                        
                                        // Calculate fallback price from order
                                        $fallbackPrice = 0;
                                        if ($od->orderProductSet && $od->orderProductSet->total_quantity > 0) {
                                            $fallbackPrice = $od->orderProductSet->basic_amount / $od->orderProductSet->total_quantity;
                                        }
                                    } else {
                                        $fallbackPrice = 0;
                                    }

                                    \App\Models\PackingItem::create([
                                        'packing_main_id' => $main->id,
                                        'packing_carton_id' => $carton->id,
                                        'size_id' => $tx->matching_size_detail_id,
                                        'lot_no' => $tx->lot_no,
                                        'quantity' => $dedTrans,
                                        'selling_price' => $fallbackPrice,
                                        'mrp' => 0
                                    ]);
                                }
                            }

                            $actualPiecesInBox += $sizePiecesDeducted;
                        }
                    }

                    if ($existingInv) {
                        $existingInv->total_boxes += 1;
                        $existingInv->save();
                    } else {
                        $existingInv = \App\Models\DomesticInventory::create([
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

    public function saveSamplingBulk(Request $request)
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
            $slip_details = \App\Models\ProductionSlipDigitization::findOrFail($slip_id);
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();

            $totalItemsProcessed = 0;

            foreach ($boxesData as $data) {
                $sizeSetMaster = \App\Models\MasterSizeMeasurement::find($data['size_set_id']);
                if (!$sizeSetMaster)
                    continue;

                $total_sets = (int) $data['quantity'];

                if (!empty($sizeSetMaster->size_group)) {
                    $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                    $sizeCounts = array_count_values($sizesInSet);

                    foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                        $remToDeduct = $pcsPerSet * $total_sets;

                        // 1. Get Stage Transactions for this size from selected lots and process deductions
                        $stockTransactions = \App\Models\OrderStageTransaction::where('order_stage_transactions.to_stage_id', 11)
                            ->where('order_stage_transactions.sub_stage_id_to', $slip_details->stage_master_unit_id)
                            ->where('order_stage_transactions.remaining_quantity', '>', 0)
                            ->whereIn('order_stage_transactions.lot_no', $selected_lots)
                            ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                            ->join('order_products_set_details', 'order_lots.order_products_set_id', '=', 'order_products_set_details.order_products_set_id')
                            ->where('order_products_set_details.size', (string) $sizeName)
                            ->select('order_stage_transactions.*', 'order_products_set_details.id as matching_size_detail_id')
                            ->orderBy('order_stage_transactions.id')
                            ->get();

                        $remTrans = $remToDeduct;
                        foreach ($stockTransactions as $tx) {
                            if ($remTrans <= 0)
                                break;
                            $dedTrans = min($tx->remaining_quantity, $remTrans);
                            $tx->remaining_quantity -= $dedTrans;
                            $tx->save();
                            $remTrans -= $dedTrans;

                            if ($dedTrans > 0) {
                                // Deduct from matching size detail balance
                                $od = \App\Models\OrderProductSetDetail::find($tx->matching_size_detail_id);
                                if ($od) {
                                    $od->remaining_quantity = max(0, $od->remaining_quantity - $dedTrans);
                                    $od->save();
                                }

                                // Create ProductionOutflowInventory record of type 'sampling'
                                \App\Models\ProductionOutflowInventory::create([
                                    'type' => 'sampling',
                                    'order_main_id' => $order_id,
                                    'slip_id' => $slip_id,
                                    'lot_no' => $tx->lot_no,
                                    'rack_id' => $data['rack_id'] ?? null,
                                    'product_id' => $data['product_id'],
                                    'color_id' => $data['color_id'],
                                    'size_id' => $tx->matching_size_detail_id,
                                    'quantity' => $dedTrans,
                                    'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                    'remarks' => "Sampling Outflow"
                                ]);
                            }
                        }
                    }
                }
                $totalItemsProcessed++;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed $totalItemsProcessed sampling items."
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

    public function downloadPrn(\Illuminate\Http\Request $request, $id)
    {
        $main = \App\Models\PackingMain::with(['domesticInventories', 'order'])->findOrFail($id);
        $allBarcodes = [];

        foreach ($main->domesticInventories as $box) {
            $boxesCount = (int)$box->total_boxes;
            if ($boxesCount < 1) $boxesCount = 1;
            
            if ($box->barcode) {
                for ($i = 0; $i < $boxesCount; $i++) {
                    $allBarcodes[] = $box->barcode;
                }
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

    private function ensureProductVariantExists($product_id, $size_set_id, $color_id)
    {
        // 1. Find or create ProductionGoodVariant
        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $product_id)
            ->where('master_size_measurement_id', $size_set_id)
            ->first();
            
        if (!$variant) {
            $variant = \App\Models\ProductionGoodVariant::create([
                'production_goods_id' => $product_id,
                'master_size_measurement_id' => $size_set_id,
                'mrp' => 0
            ]);
        }
        
        // 2. Find or create ProductionGoodVariantItem
        $item = \App\Models\ProductionGoodVariantItem::where('variant_id', $variant->id)
            ->where('master_color_id', $color_id)
            ->first();
            
        if (!$item) {
            $barcode = 'D' . $product_id . 'S' . $size_set_id . 'C' . $color_id;
            \App\Models\ProductionGoodVariantItem::create([
                'variant_id' => $variant->id,
                'master_color_id' => $color_id,
                'barcode' => $barcode
            ]);
        }
    }
}