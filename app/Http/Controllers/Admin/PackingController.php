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
        return redirect()->route('admin.packing.processNew', ['slip_id' => $slip_id])->with('success', 'Order selection cleared successfully.');
    }

    /**
     * Old packing screen (no stock checks). Every slip is packed on the new screen now.
     */
    public function process(Request $request, $slip_id)
    {
        return redirect()->route('admin.packing.processNew', array_filter(['slip_id' => $slip_id, 'order_id' => $request->order_id]));
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

        // Lots THIS packing unit still holds (received - packed - dead/sampling/debit/rework), counted for this unit only.
        // This session's own work is left out so the list shows what was pending when the session started.
        $unitId = $slip->stage_master_unit_id;
        $receivedLots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
            ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
            ->where('order_stage_transactions.to_stage_id', 11) // Packing
            ->where('order_stage_transactions.sub_stage_id_to', $unitId)
            ->select('order_lots.order_main_id', 'order_lots.lot_no')
            ->distinct()
            ->get();
        $stockAtStart = $this->service->unitLotStock($receivedLots->pluck('lot_no')->all(), $unitId, $packing->id ?? null, $slip_id);

        $activeOrderIds = $receivedLots->filter(function ($row) use ($stockAtStart) {
            return ($stockAtStart[(string) $row->lot_no]['pending'] ?? 0) > 0;
        })->pluck('order_main_id')->unique()->all();
        if ($order) {
            $activeOrderIds = array_unique(array_merge($activeOrderIds, [$order->id]));
        }

        // Fetch ALL active orders for dropdown (Corporate & Domestic)
        $active_orders = \App\Models\OrderMain::with('customer')
            ->whereIn('id', $activeOrderIds)
            ->whereIn('status', [0, 1, 2]) // Pending, Confirmed, Partial
            ->orderBy('id', 'desc')->get();

        // Fully packed lots do not come; partly packed lots come with the pieces still to pack.
        $unit_lots = collect();
        if ($order) {
            $unit_lots = \Illuminate\Support\Facades\DB::table('order_lots')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
                ->where('order_lots.order_main_id', $order->id)
                ->whereIn('order_lots.lot_no', $receivedLots->pluck('lot_no')->all())
                ->select(
                    'order_lots.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name'
                )
                ->get()
                ->map(function ($lot) use ($stockAtStart) {
                    $s = $stockAtStart[(string) $lot->lot_no] ?? null;
                    $lot->quantity = $s['received'] ?? 0;
                    $lot->remaining_quantity = $s['pending'] ?? 0;
                    return $lot;
                })
                ->filter(function ($lot) { return $lot->remaining_quantity > 0; })
                ->values();
        }

        $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->toArray();

        return view('admin.packing.process_new', compact('slip', 'order', 'packing', 'active_orders', 'unit_lots', 'selected_lots'));
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
            $orderLots = \App\Models\OrderLot::where('order_main_id', $request->order_id)
                ->pluck('lot_no')
                ->map(function($l) { return (string)$l; })
                ->toArray();

            $unique_lots = array_unique($request->lots);
            // only lots this packing unit still has pieces of
            $slipUnitId = \App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id');
            $unitStock = $this->service->unitLotStock($unique_lots, $slipUnitId, $packing->id, $slip_id);
            foreach ($unique_lots as $lot_no) {
                if (!empty($orderLots) && !in_array((string)$lot_no, $orderLots)) {
                    continue;
                }
                if (($unitStock[(string) $lot_no]['pending'] ?? 0) <= 0) {
                    continue;
                }
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

        $slip_unit_id = $packing->slip ? $packing->slip->stage_master_unit_id : (\App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id'));
        $stage_transactions = \App\Models\OrderStageTransaction::where('to_stage_id', 11)
            ->whereIn('lot_no', $selected_lots)
            ->when($slip_unit_id, function($q) use ($slip_unit_id) {
                $q->where(function($sq) use ($slip_unit_id) {
                    $sq->where('sub_stage_id_to', $slip_unit_id)
                      ->orWhereNull('sub_stage_id_to');
                });
            })
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

        $rework_outflow_by_lot_size = \App\Models\ProductionOutflowInventory::join('order_products_set_details', 'production_outflow_inventories.size_id', '=', 'order_products_set_details.id')
            ->whereIn('production_outflow_inventories.lot_no', $selected_lots)
            ->where('production_outflow_inventories.slip_id', $slip_id)
            ->where('production_outflow_inventories.type', 'rework')
            ->select('production_outflow_inventories.lot_no', 'order_products_set_details.size', DB::raw('SUM(production_outflow_inventories.quantity) as total'))
            ->groupBy('production_outflow_inventories.lot_no', 'order_products_set_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            });

        $assigned_tx_ids = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
            ->whereNotNull('assigned_transaction_id')
            ->pluck('assigned_transaction_id')
            ->filter()
            ->toArray();

        $legacy_reworks = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transaction_details.order_stage_transaction_id', '=', 'order_stage_transactions.id')
            ->whereIn('order_stage_transactions.lot_no', $selected_lots)
            ->where('order_stage_transactions.production_slip_digitization_id', $slip_id)
            ->where('order_stage_transactions.from_stage_id', 11)
            ->where('order_stage_transactions.type', 'rework')
            ->when(!empty($assigned_tx_ids), function($q) use ($assigned_tx_ids) {
                $q->whereNotIn('order_stage_transactions.id', $assigned_tx_ids);
            })
            ->select('order_stage_transactions.lot_no', 'order_stage_transaction_details.size', DB::raw('SUM(order_stage_transaction_details.quantity) as total'))
            ->groupBy('order_stage_transactions.lot_no', 'order_stage_transaction_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            });

        $rework_by_lot_size = $rework_outflow_by_lot_size->concat($legacy_reworks)->groupBy('lot_no');

        $outflow_by_lot_size = \App\Models\ProductionOutflowInventory::join('order_products_set_details', 'production_outflow_inventories.size_id', '=', 'order_products_set_details.id')
            ->whereIn('production_outflow_inventories.lot_no', $selected_lots)
            ->where('production_outflow_inventories.slip_id', $slip_id)
            ->whereIn('production_outflow_inventories.type', ['dead', 'sampling', 'debit'])
            ->select('production_outflow_inventories.lot_no', 'order_products_set_details.size', DB::raw('SUM(production_outflow_inventories.quantity) as total'))
            ->groupBy('production_outflow_inventories.lot_no', 'order_products_set_details.size')
            ->get()
            ->map(function($item) {
                $item->size = trim(strtoupper($item->size));
                return $item;
            })
            ->groupBy('lot_no');

        // What THIS packing unit held of each lot when this session started (its other sessions/slips deducted,
        // never another packing unit's work - a lot can be split between two packing units)
        $stockAtStart = $this->service->unitLotStock($selected_lots, $slip_unit_id, $packing->id, $slip_id);

        foreach ($lots_data as $lot) {
            $lot_txs = $stage_transactions->get($lot->lot_no, collect());
            $gross_qty = (int) $lot_txs->sum('quantity');
            $lot->quantity = $gross_qty;
            $lot->transaction_id = $lot_txs->first() ? $lot_txs->first()->id : null;

            $start = $stockAtStart[(string) $lot->lot_no] ?? ['pending' => 0, 'sizes' => []];
            $pending_at_start = (int) $start['pending'];
            $lot->incoming_sizes = $start['sizes'];

            // Deduct current session's live activity
            $currentPacked = (int) ($packed_by_lot_size->get($lot->lot_no, collect())->sum('total'));
            $currentRework = (int) ($rework_by_lot_size->get($lot->lot_no, collect())->sum('total'));
            $currentOutflow = (int) ($outflow_by_lot_size->get($lot->lot_no, collect())->sum('total'));

            $lot->remaining_quantity = max(0, $pending_at_start - $currentPacked - $currentRework - $currentOutflow);
        }

        $set_ids = $lots_data->pluck('set_id')->unique()->toArray();
        $set_details = \App\Models\OrderProductSetDetail::whereIn('order_products_set_id', $set_ids)->get()->groupBy('order_products_set_id');

        $unique_designs = $lots_data->pluck('design_number')->unique()->values();
        $unique_colors = $lots_data->map(function($item) {
            return (object)['id' => $item->color_id, 'name' => $item->color_name];
        })->unique('id')->values();
        $storerooms = \App\Models\Storeroom::with('racks')->where('status', 1)->get();

        $saved_cartons = \App\Models\PackingCarton::with(['items.detail.orderProductSet.size_measurement'])
            ->where('packing_main_id', $packing->id)
            ->where('status', 1)
            ->get();

        $saved_reworks = \App\Models\ProductionOutflowInventory::where('slip_id', $slip_id)
            ->where('type', 'rework')
            ->with(['rack.storeroom', 'size', 'responsibleStage', 'responsibleUnit', 'assignedStage', 'assignedUnit'])
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
                ->whereIn('status', [1, 2])
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

            // Also check order's directly assigned size sets for these lots
            $lots_size_set_ids = \Illuminate\Support\Facades\DB::table('order_lots')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->whereIn('order_lots.lot_no', $selected_lots)
                ->where('order_products_sets.design_number', $design)
                ->pluck('order_products_sets.set_size')
                ->unique()
                ->filter()
                ->toArray();

            // Include status 1 (standard) and status 2 (custom / updated ratio) size sets
            $allSizeMeasurements = \App\Models\MasterSizeMeasurement::whereIn('status', [1, 2])->get();
            $size_sets = [];
            
            foreach ($allSizeMeasurements as $sm) {
                if (empty($sm->size_group)) continue;
                
                $sizes = array_map(function($s) { return trim(strtoupper($s)); }, explode(',', $sm->size_group));
                
                $isDirectLotSet = in_array($sm->id, $lots_size_set_ids);
                // Check if this size set is a subset of available_sizes
                $canBeMade = empty(array_diff($sizes, $available_sizes));
                
                if ($isDirectLotSet || $canBeMade) {
                    $size_sets[] = [
                        'id' => $sm->id,
                        'name' => $sm->name,
                        'required_sizes' => $sizes,
                        'no_of_pcs' => $sm->no_of_pcs,
                        'is_direct' => $isDirectLotSet ? 1 : 0
                    ];
                }
            }

            // Remove duplicates and sort: direct order sets first, then by name and pcs
            $unique_size_sets = collect($size_sets)->unique('id')->sortBy([
                ['is_direct', 'desc'],
                ['name', 'asc'],
                ['no_of_pcs', 'asc']
            ])->values()->all();

            return response()->json([
                'status' => 'success',
                'size_sets' => $unique_size_sets
            ]);
        }

        $size_sets = [];
        if ($request->for_domestic) {
            $allSizeMeasurements = \App\Models\MasterSizeMeasurement::whereIn('status', [1, 2])->orderBy('name')->get();
            foreach ($allSizeMeasurements as $sm) {
                if (empty($sm->size_group)) continue;
                $sizes = array_map('trim', explode(',', $sm->size_group));
                $size_sets[] = [
                    'id' => $sm->id,
                    'name' => $sm->name,
                    'required_sizes' => $sizes,
                    'no_of_pcs' => $sm->no_of_pcs
                ];
            }
        }

        $products = \App\Models\ProductionGoods::with(['variants.sizeSet'])
            ->where('design_number', $design)
            ->where('status', 1)
            ->get();

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
        if (trim((string) $design) === '') {
            return response()->json(['status' => 'error', 'message' => 'Design number is required.']);
        }

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
        
        // Size-wise pieces THIS packing unit still holds in the selected lots (same figures as the pack screen)
        $packing = \App\Models\PackingMain::where('slip_id', $slip_id)->first();
        $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->unique()->toArray();
        $slip_unit_id = $packing && $packing->slip ? $packing->slip->stage_master_unit_id : (\App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id'));

        $lots_data = \Illuminate\Support\Facades\DB::table('order_lots')
            ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
            ->whereIn('order_lots.lot_no', $selected_lots)
            ->select('order_lots.lot_no', 'order_products_sets.id as set_id')
            ->get();
        $set_details = \App\Models\OrderProductSetDetail::whereIn('order_products_set_id', $lots_data->pluck('set_id')->unique()->toArray())
            ->get()->groupBy('order_products_set_id');
        $stockNow = $this->service->unitLotStock($selected_lots, $slip_unit_id);

        $available_pieces = 0;
        $available_balances = [];

        foreach ($lots_data as $lot) {
            $s = $stockNow[(string) $lot->lot_no] ?? null;
            if (!$s || $s['pending'] <= 0) continue;

            $lotSizes = $s['sizes'];
            if (empty($lotSizes) && isset($set_details[$lot->set_id])) {
                // no size breakdown received: spread the lot's pending over its set ratio
                $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                foreach ($set_details[$lot->set_id] as $detail) {
                    $sz = \App\Services\Admin\PackingService::sizeKey($detail->size);
                    $lotSizes[$sz] = ($lotSizes[$sz] ?? 0) + ($total_set_qty > 0 ? (int) floor($s['pending'] * ($detail->total_quantity / $total_set_qty)) : 0);
                }
            }

            foreach ($lotSizes as $sizeName => $live) {
                $available_pieces += $live;
                $available_balances[$sizeName] = ($available_balances[$sizeName] ?? 0) + $live;
            }
        }
        
        $no_of_pcs = 1;
        if ($size_set_id) {
            $sizeSetModel = \App\Models\MasterSizeMeasurement::find($size_set_id);
            $no_of_pcs = $sizeSetModel ? ($sizeSetModel->no_of_pcs ?: 1) : 1;
        }
        
        $max_sets = $no_of_pcs > 0 ? floor($available_pieces / $no_of_pcs) : $available_pieces;
        
        // Fetch colors for color dropdown
        $colors_list = [];
        if ($request->for_sampling) {
            $colors_list = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name'])->toArray();
        } elseif ($request->strict_colors) {
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
        } else {
            $colors_list = \App\Models\MasterColor::where('status', 1)->orderBy('name')->get(['id', 'name'])->toArray();
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
        if ($packing->status == 1) return response()->json(['status' => 'error', 'message' => 'This packing session is finalized.']);

        \Log::channel('single')->info('Bulk Save Carton Plan Start', ['count' => count($cartons)]);

        \DB::beginTransaction();
        try {
            $now = now();
            $slip_unit_id = $packing->slip ? $packing->slip->stage_master_unit_id : (\App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id'));
            $selectedLots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->map(fn($l) => (string) $l)->all();

            // --- Check everything before saving anything ---
            $usedNos = \App\Models\PackingCarton::where('packing_main_id', $packing->id)->pluck('carton_no')->map(fn($n) => trim((string) $n))->all();
            $need = [];
            foreach ($cartons as $carton) {
                $no = trim((string) ($carton['carton_no'] ?? ''));
                if ($no === '') throw new \Exception('Every carton needs a carton number.');
                if (in_array($no, $usedNos, true)) throw new \Exception("Carton number {$no} is already used in this packing session.");
                $usedNos[] = $no;

                foreach (($carton['items'] ?? []) as $item) {
                    $lot = (string) ($item['lot_no'] ?? '');
                    $qty = $item['quantity'] ?? null;
                    if ($lot === '') throw new \Exception("Missing lot information for size " . ($item['size_name'] ?? ''));
                    if (!is_numeric($qty) || (int) $qty != $qty || (int) $qty < 1) throw new \Exception("Carton {$no}: quantity must be a whole number of 1 or more.");
                    if (!empty($selectedLots) && !in_array($lot, $selectedLots, true)) throw new \Exception("Lot {$lot} is not selected for this packing session.");
                    $detail = \App\Models\OrderProductSetDetail::find($item['size_id'] ?? 0);
                    $lotSet = \App\Models\OrderLot::where('lot_no', $lot)->value('order_products_set_id');
                    if (!$detail || ($lotSet && $detail->order_products_set_id != $lotSet)) throw new \Exception("Carton {$no}: size does not belong to lot {$lot}.");
                    $need[$lot][$detail->size] = ($need[$lot][$detail->size] ?? 0) + (int) $qty;
                }
            }
            // A unit can never pack more than it holds (per lot and per size)
            $this->service->assertUnitHolds($need, $slip_unit_id);

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
                            'quantity' => (int) $item['quantity'],
                            'mrp' => $carton['mrp'] ?? 0,
                            'selling_price' => $carton['price'] ?? 0
                        ]);

                        if (!isset($stageTransactionsToUpdate[$lot_no])) {
                            $stageTransactionsToUpdate[$lot_no] = 0;
                        }
                        $stageTransactionsToUpdate[$lot_no] += (int) $item['quantity'];
                    }
                }
            }
            
            // Take the packed pieces off THIS unit's own balance only (never another unit's)
            foreach ($stageTransactionsToUpdate as $lot_no => $deductQty) {
                $this->service->deductUnitBalance((string) $lot_no, $slip_unit_id, (int) $deductQty);
            }
            
            \DB::commit();
            return response()->json(['status' => 'success', 'message' => count($cartons) . ' cartons planned successfully']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::channel('single')->error('Bulk Save Carton Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function apiUpdateCartonPricing(Request $request, $slip_id, $carton_id)
    {
        $carton = \App\Models\PackingCarton::find($carton_id);
        if (!$carton) {
            return response()->json(['status' => 'error', 'message' => 'Carton not found']);
        }

        $mrp = $request->input('mrp');
        $price = $request->input('price');

        $mrpVal = (is_numeric($mrp) && $mrp >= 0) ? (float)$mrp : 0;
        $priceVal = (is_numeric($price) && $price >= 0) ? (float)$price : 0;

        \App\Models\PackingItem::where('packing_carton_id', $carton->id)
            ->update([
                'mrp' => $mrpVal,
                'selling_price' => $priceVal
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing updated for Carton ' . $carton->carton_no,
            'carton_id' => $carton->id,
            'mrp' => $mrpVal,
            'price' => $priceVal
        ]);
    }

    public function apiBulkUpdateCartonsPricing(Request $request, $slip_id)
    {
        $cartonsData = $request->input('cartons');
        $cartonIds = $request->input('carton_ids');
        $commonMrp = $request->input('mrp');
        $commonPrice = $request->input('price');

        \DB::beginTransaction();
        try {
            $updatedCount = 0;

            if (!empty($cartonsData) && is_array($cartonsData)) {
                foreach ($cartonsData as $row) {
                    $cartonId = $row['id'] ?? null;
                    if (!$cartonId) continue;

                    $mrp = isset($row['mrp']) && is_numeric($row['mrp']) ? (float)$row['mrp'] : 0;
                    $price = isset($row['price']) && is_numeric($row['price']) ? (float)$row['price'] : 0;

                    \App\Models\PackingItem::where('packing_carton_id', $cartonId)
                        ->update([
                            'mrp' => $mrp,
                            'selling_price' => $price
                        ]);
                    $updatedCount++;
                }
            } elseif (!empty($cartonIds) && is_array($cartonIds)) {
                $mrp = is_numeric($commonMrp) ? (float)$commonMrp : 0;
                $price = is_numeric($commonPrice) ? (float)$commonPrice : 0;

                \App\Models\PackingItem::whereIn('packing_carton_id', $cartonIds)
                    ->update([
                        'mrp' => $mrp,
                        'selling_price' => $price
                    ]);
                $updatedCount = count($cartonIds);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No carton pricing data provided']);
            }

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully updated pricing for {$updatedCount} carton(s)."
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function apiDeleteCarton(Request $request, $slip_id, $carton_id)
    {
        $carton = \App\Models\PackingCarton::with('items')->find($carton_id);
        if (!$carton) return response()->json(['status' => 'error', 'message' => 'Carton not found']);

        $slip_unit_id = \App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id');

        \DB::beginTransaction();
        try {
            $this->service->assertCartonDeletable($carton);
            foreach ($carton->items as $item) {
                // Give the pieces back to THIS unit's own balance (never above what it received)
                if ($item->lot_no) {
                    $this->service->restoreUnitBalance((string) $item->lot_no, $slip_unit_id, (int) $item->quantity);
                }
            }

            \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
            $carton->delete();

            \DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function bulkDeleteCartons(Request $request, $slip_id)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No cartons selected for deletion']);
        }

        $slip_unit_id = \App\Models\ProductionSlipDigitization::where('id', $slip_id)->value('stage_master_unit_id');

        \DB::beginTransaction();
        try {
            $cartons = \App\Models\PackingCarton::with('items')->whereIn('id', $ids)->get();

            foreach ($cartons as $carton) {
                $this->service->assertCartonDeletable($carton);
                foreach ($carton->items as $item) {
                    // Give the pieces back to THIS unit's own balance (never above what it received)
                    if ($item->lot_no) {
                        $this->service->restoreUnitBalance((string) $item->lot_no, $slip_unit_id, (int) $item->quantity);
                    }
                }

                \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
                $carton->delete();
            }
            
            \DB::commit();
            return response()->json(['status' => 'success', 'message' => count($cartons) . ' cartons deleted successfully']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Nothing was deleted: ' . $e->getMessage()]);
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

                    // Give the pieces back to this packing unit's own balance
                    if ($item->lot_no && $unitId) {
                        $this->service->restoreUnitBalance((string) $item->lot_no, $unitId, (int) $item->quantity);
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




    /**
     * Old domestic packing screen (no stock checks). Every slip is packed on the new screen now.
     */
    public function processDomestic(Request $request, $slip_id)
    {
        return redirect()->route('admin.packing.processNew', array_filter(['slip_id' => $slip_id, 'order_id' => $request->order_id]));
    }

    /**
     * Save buttons of the old packing screens. Those screens are closed (they skipped the stock checks);
     * everything is packed on the new screen, so these refuse instead of saving.
     */
    private function oldScreenClosed()
    {
        return response()->json(['status' => 'error', 'message' => 'This old packing screen is closed. Please open the slip again from Digitized Slips.']);
    }

    public function saveDomesticBox(Request $request)
    {
        return $this->oldScreenClosed();
    }

    public function saveCorporateDomesticBulk(Request $request)
    {
        return $this->oldScreenClosed();
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
            $cartonsQuery = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id);
            if (!empty($domesticInv->barcode)) {
                $cartonsQuery->where('barcode', $domesticInv->barcode);
            } else {
                $cartonsQuery->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); });
            }
            if (!empty($domesticInv->rack_id)) {
                $cartonsQuery->where('rack_id', $domesticInv->rack_id);
            }
            $cartons = $cartonsQuery->get();

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

                    // Give the pieces back to this packing unit's own balance
                    if ($item->lot_no && $unitId) {
                        $this->service->restoreUnitBalance((string) $item->lot_no, $unitId, (int) $item->quantity);
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
                $cartonsQuery = \App\Models\PackingCarton::where('packing_main_id', $domesticInv->packing_main_id);
                if (!empty($domesticInv->barcode)) {
                    $cartonsQuery->where('barcode', $domesticInv->barcode);
                } else {
                    $cartonsQuery->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); });
                }
                if (!empty($domesticInv->rack_id)) {
                    $cartonsQuery->where('rack_id', $domesticInv->rack_id);
                }
                $cartons = $cartonsQuery->get();

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

                        // Give the pieces back to this packing unit's own balance
                        if ($item->lot_no && $unitId) {
                            $this->service->restoreUnitBalance((string) $item->lot_no, $unitId, (int) $item->quantity);
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

        $unit_lots = collect();
        if ($unit_id) {
            $slipId = $request->slip_id ?? 0;
            $currentPacking = $slipId ? \App\Models\PackingMain::where('slip_id', $slipId)->first() : null;
            $currentPackingId = $currentPacking ? $currentPacking->id : 0;

            $rawLots = \Illuminate\Support\Facades\DB::table('order_stage_transactions')
                ->join('order_lots', 'order_stage_transactions.lot_no', '=', 'order_lots.lot_no')
                ->join('order_products_sets', 'order_lots.order_products_set_id', '=', 'order_products_sets.id')
                ->leftJoin('master_size_measurements', 'order_products_sets.set_size', '=', 'master_size_measurements.id')
                ->where('order_stage_transactions.to_stage_id', 11)
                ->where('order_stage_transactions.sub_stage_id_to', $unit_id)
                ->where('order_lots.order_main_id', $id)
                ->groupBy(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name'
                )
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    DB::raw('SUM(order_stage_transactions.quantity) as gross_quantity')
                )
                ->get();

            $orderLotNos = $rawLots->pluck('lot_no')->toArray();

            if (!empty($orderLotNos)) {
                $orderPackedSummary = \DB::table('packing_items')
                    ->whereIn('lot_no', $orderLotNos)
                    ->when($currentPackingId, function($q) use ($currentPackingId) {
                        $q->where('packing_main_id', '!=', $currentPackingId);
                    })
                    ->select('lot_no', DB::raw('SUM(quantity) as packed_qty'))
                    ->groupBy('lot_no')
                    ->pluck('packed_qty', 'lot_no')
                    ->toArray();

                $orderOutflowSummary = \DB::table('production_outflow_inventories')
                    ->whereIn('lot_no', $orderLotNos)
                    ->when($slipId, function($q) use ($slipId) {
                        $q->where('slip_id', '!=', $slipId);
                    })
                    ->select('lot_no', DB::raw('SUM(quantity) as outflow_qty'))
                    ->groupBy('lot_no')
                    ->pluck('outflow_qty', 'lot_no')
                    ->toArray();

                $orderLegacyReworkSummary = \DB::table('order_stage_transactions')
                    ->whereIn('lot_no', $orderLotNos)
                    ->where('from_stage_id', 11)
                    ->where('type', 'rework')
                    ->when($slipId, function($q) use ($slipId) {
                        $q->where('production_slip_digitization_id', '!=', $slipId);
                    })
                    ->select('lot_no', DB::raw('SUM(quantity) as rework_qty'))
                    ->groupBy('lot_no')
                    ->pluck('rework_qty', 'lot_no')
                    ->toArray();

                foreach ($rawLots as $rawLot) {
                    $lno = $rawLot->lot_no;
                    $gross = (int) $rawLot->gross_quantity;
                    $packed = (int) ($orderPackedSummary[$lno] ?? 0);
                    $outflow = (int) ($orderOutflowSummary[$lno] ?? 0) + (int) ($orderLegacyReworkSummary[$lno] ?? 0);

                    $pending = max(0, $gross - $packed - $outflow);

                    if ($pending > 0) {
                        $rawLot->quantity = $gross;
                        $rawLot->remaining_quantity = $pending;
                        $unit_lots->push($rawLot);
                    }
                }
            }
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
        return $this->oldScreenClosed();
    }

    public function bulkSaveCarton(Request $request)
    {
        return $this->oldScreenClosed();
    }

    public function saveBox(Request $request)
    {
        return $this->oldScreenClosed();
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
        return $this->oldScreenClosed();
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
        return $this->oldScreenClosed();
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
            'rack_id' => 'required',
            'to_stage_id' => 'nullable',
            'to_unit_id' => 'nullable',
            'items' => 'required|array',
            'items.*.detail_id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.lot_no' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->reassignRework($data);
        return response()->json($result);
    }

    public function reworkList(Request $request)
    {
        $storerooms = \App\Models\Storeroom::with('racks')->where('status', 1)->get();
        $stages = \App\Models\MasterProductStage::where('status', 1)
            ->whereIn('id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
            ->get();

        // Summary counts
        $totalStoredPieces = \App\Models\ProductionOutflowInventory::where('type', 'rework')
            ->where('status', 'stored')
            ->sum('quantity');

        $totalAssignedPieces = \App\Models\ProductionOutflowInventory::where('type', 'rework')
            ->where('status', 'assigned')
            ->sum('quantity');

        $totalActiveLots = \App\Models\ProductionOutflowInventory::where('type', 'rework')
            ->distinct('lot_no')
            ->count('lot_no');

        return view('admin.packing.rework_list', compact('storerooms', 'stages', 'totalStoredPieces', 'totalAssignedPieces', 'totalActiveLots'));
    }

    public function reworkListData(Request $request)
    {
        $query = \App\Models\ProductionOutflowInventory::with([
            'orderMain.customer',
            'product',
            'color',
            'size.orderProductSet',
            'rack.storeroom',
            'responsibleStage',
            'responsibleUnit',
            'assignedStage',
            'assignedUnit',
            'assignedUser',
            'slip'
        ])->where('type', 'rework');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rack_id')) {
            $query->where('rack_id', $request->rack_id);
        }

        if ($request->filled('search_term')) {
            $term = trim($request->search_term);
            $query->where(function($q) use ($term) {
                $q->where('lot_no', 'like', "%{$term}%")
                  ->orWhere('remarks', 'like', "%{$term}%")
                  ->orWhereHas('orderMain', function($oq) use ($term) {
                      $oq->where('order_no', 'like', "%{$term}%")
                         ->orWhereHas('customer', function($cq) use ($term) {
                             $cq->where('name', 'like', "%{$term}%");
                         });
                  })
                  ->orWhereHas('product', function($pq) use ($term) {
                      $pq->where('design_number', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $records = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $records
        ]);
    }

    public function assignReworkFromRack(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|numeric',
            'to_stage_id' => 'required',
            'to_unit_id' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->assignReworkFromRack($data);
        return response()->json($result);
    }

    public function reassignReworkUnit(Request $request)
    {
        $data = $request->validate([
            'id' => 'required',
            'to_stage_id' => 'required',
            'to_unit_id' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $result = $this->service->reassignReworkUnit($data);
        return response()->json($result);
    }

    public function downloadReworkSlip(Request $request, $id)
    {
        $outflow = \App\Models\ProductionOutflowInventory::with([
            'orderMain.customer',
            'product.series',
            'color',
            'size',
            'rack.storeroom',
            'responsibleStage',
            'responsibleUnit',
            'assignedStage',
            'assignedUnit',
            'assignedUser',
            'slip'
        ])->findOrFail($id);

        if ($outflow->status !== 'assigned') {
            return redirect()->back()->with('error', 'Slip can only be downloaded after the item is assigned for rework.');
        }

        $items = collect([$outflow]);
        $general_setting = \App\Models\GeneralSettings::first();
        $isBulk = false;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing.rework_slip', compact('items', 'general_setting', 'isBulk'))
            ->setPaper('A4', 'portrait');

        $safeLot = str_replace(['/', '\\', ' '], '_', $outflow->lot_no ?? 'Lot');
        $fileName = 'Rework_Slip_' . $safeLot . '_#' . $outflow->id . '.pdf';

        if ($request->get('view') == 1) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    public function downloadBulkReworkSlip(Request $request)
    {
        $rawIds = $request->query('ids') ?? $request->input('ids');
        if (is_string($rawIds)) {
            $ids = array_filter(explode(',', $rawIds));
        } elseif (is_array($rawIds)) {
            $ids = array_filter($rawIds);
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Please select at least one assigned rework record to download slips.');
        }

        $items = \App\Models\ProductionOutflowInventory::with([
            'orderMain.customer',
            'product.series',
            'color',
            'size',
            'rack.storeroom',
            'responsibleStage',
            'responsibleUnit',
            'assignedStage',
            'assignedUnit',
            'assignedUser',
            'slip'
        ])->whereIn('id', $ids)
          ->where('status', 'assigned')
          ->orderBy('id', 'desc')
          ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Slip can only be downloaded after items are assigned for rework. None of the selected records are assigned yet.');
        }

        $general_setting = \App\Models\GeneralSettings::first();
        $isBulk = true;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing.rework_slip', compact('items', 'general_setting', 'isBulk'))
            ->setPaper('A4', 'portrait');

        $fileName = 'Rework_Slips_Bulk_' . date('Ymd_His') . '.pdf';

        if ($request->get('view') == 1) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
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

    /**
     * Delete several rework records at once (pack screen + Rework List). All or nothing.
     */
    public function bulkDeleteRework(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No records selected.']);
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $res = $this->service->deleteRework($id);
                if (($res['status'] ?? 'error') !== 'success') {
                    throw new \Exception($res['message'] ?? 'Failed to delete record ID: ' . $id);
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Selected rework records deleted and stock restored.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Nothing was deleted: ' . $e->getMessage()]);
        }
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

    /**
     * Pick $need pieces of one size from the selected lots, using only what THIS packing unit holds of that size.
     * $ctx = ['stock' => unitLotStock(...), 'lotSets' => [lot => set_id], 'details' => [set_id => [SIZE => detail]]] (stock is reduced in place).
     * @return array [[lot_no, detail, qty], ...]
     */
    private function allocateSize(array &$ctx, array $selectedLots, string $size, int $need): array
    {
        $sz = \App\Services\Admin\PackingService::sizeKey($size);
        $picks = [];
        foreach ($selectedLots as $lot) {
            if ($need <= 0) break;
            $lot = (string) $lot;
            $detail = $ctx['details'][$ctx['lotSets'][$lot] ?? 0][$sz] ?? null;
            if (!$detail || !isset($ctx['stock'][$lot])) continue;
            $s = &$ctx['stock'][$lot];
            $have = empty($s['sizes']) ? $s['pending'] : min($s['pending'], $s['sizes'][$sz] ?? 0);
            $take = min($have, $need);
            if ($take <= 0) { unset($s); continue; }
            $s['pending'] -= $take;
            if (!empty($s['sizes'])) $s['sizes'][$sz] -= $take;
            unset($s);
            $picks[] = [$lot, $detail, $take];
            $need -= $take;
        }
        if ($need > 0) {
            throw new \Exception("Not enough stock at this packing unit for size '{$size}' in the selected lots. Missing {$need} pcs.");
        }
        return $picks;
    }

    private function allocationContext(array $selectedLots, $unitId): array
    {
        $lotSets = \App\Models\OrderLot::whereIn('lot_no', $selectedLots)->pluck('order_products_set_id', 'lot_no')
            ->mapWithKeys(fn($set, $lot) => [(string) $lot => $set])->all();
        $details = [];
        foreach (\App\Models\OrderProductSetDetail::whereIn('order_products_set_id', array_values($lotSets))->get() as $d) {
            $details[$d->order_products_set_id][\App\Services\Admin\PackingService::sizeKey($d->size)] ??= $d;
        }
        $stock = [];
        foreach ($this->service->unitLotStock($selectedLots, $unitId) as $lot => $s) {
            $stock[(string) $lot] = $s;
        }
        return ['stock' => $stock, 'lotSets' => $lotSets, 'details' => $details];
    }

    public function saveDomesticBulk(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'slip_id' => 'required',
            'boxes' => 'required|array|min:1',
            'boxes.*.rack_id' => 'required',
        ], [
            'boxes.*.rack_id.required' => 'Storage Rack is required for domestic diversion.'
        ]);

        $slip_id = $request->slip_id;
        $order_id = $request->order_id;
        $boxesData = $request->boxes;

        DB::beginTransaction();
        try {
            $main = $this->service->getOrCreatePackingMain($slip_id, $order_id);
            if ($main->status == 1) {
                throw new \Exception('This packing session is finalized.');
            }
            $slip_details = \App\Models\ProductionSlipDigitization::findOrFail($slip_id);
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->map(fn($l) => (string) $l)->toArray();
            $ctx = $this->allocationContext($selected_lots, $slip_details->stage_master_unit_id);

            // Check the whole request first, so a shortage shows the real number of missing pieces
            $dry = $ctx;
            foreach ($boxesData as $data) {
                $sm = \App\Models\MasterSizeMeasurement::find($data['size_set_id'] ?? 0);
                if (!$sm || empty($sm->size_group)) continue;
                foreach (array_count_values(array_map('trim', explode(',', $sm->size_group))) as $sizeName => $pcsPerSet) {
                    $targetSize = ($data['size_mapping'] ?? [])[$sizeName] ?? $sizeName;
                    $this->allocateSize($dry, $selected_lots, (string) $targetSize, (int) $pcsPerSet * max(0, (int) ($data['quantity'] ?? 0)));
                }
            }

            $totalBoxesProcessed = 0;

            foreach ($boxesData as $data) {
                if (empty($data['rack_id'])) {
                    throw new \Exception('Storage Rack is required for domestic diversion.');
                }

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
                    ->where('packing_main_id', $main->id)
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
                        'status' => 3
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
                        $sizeMapping = $data['size_mapping'] ?? [];

                        foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                            $targetSize = $sizeMapping[$sizeName] ?? $sizeName;

                            // Take the pieces of this size from the selected lots - only what THIS unit holds of that size
                            foreach ($this->allocateSize($ctx, $selected_lots, (string) $targetSize, (int) $pcsPerSet) as [$lotNo, $detail, $take]) {
                                $this->service->deductUnitBalance($lotNo, $slip_details->stage_master_unit_id, $take);

                                // Deduct from matching size detail balance
                                $od = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($detail->id);
                                $fallbackPrice = 0;
                                if ($od) {
                                    $od->remaining_quantity = max(0, $od->remaining_quantity - $take);
                                    $od->save();
                                    if ($od->orderProductSet && $od->orderProductSet->total_quantity > 0) {
                                        $fallbackPrice = $od->orderProductSet->basic_amount / $od->orderProductSet->total_quantity;
                                    }
                                }

                                \App\Models\PackingItem::create([
                                    'packing_main_id' => $main->id,
                                    'packing_carton_id' => $carton->id,
                                    'size_id' => $detail->id,
                                    'lot_no' => $lotNo,
                                    'quantity' => $take,
                                    'selling_price' => $fallbackPrice,
                                    'mrp' => 0
                                ]);
                                $actualPiecesInBox += $take;
                            }
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
            $this->service->assertSessionOpen($slip_id);
            $slip_details = \App\Models\ProductionSlipDigitization::findOrFail($slip_id);
            $selected_lots = \App\Models\PackingSelectedLot::where('slip_id', $slip_id)->pluck('lot_no')->map(fn($l) => (string) $l)->toArray();
            $ctx = $this->allocationContext($selected_lots, $slip_details->stage_master_unit_id);

            $totalItemsProcessed = 0;

            foreach ($boxesData as $data) {
                $sizeSetMaster = \App\Models\MasterSizeMeasurement::find($data['size_set_id']);
                if (!$sizeSetMaster)
                    continue;

                $total_sets = $data['quantity'] ?? 0;
                if (!is_numeric($total_sets) || (int) $total_sets != $total_sets || (int) $total_sets < 1) {
                    throw new \Exception('Sampling quantity must be a whole number of 1 or more.');
                }
                $total_sets = (int) $total_sets;

                if (!empty($sizeSetMaster->size_group)) {
                    $sizesInSet = array_map('trim', explode(',', $sizeSetMaster->size_group));
                    $sizeCounts = array_count_values($sizesInSet);

                    foreach ($sizeCounts as $sizeName => $pcsPerSet) {
                        // Take the pieces from the selected lots - only what THIS unit holds of that size (else stop with a message)
                        foreach ($this->allocateSize($ctx, $selected_lots, (string) $sizeName, (int) ($pcsPerSet * $total_sets)) as [$lotNo, $detail, $take]) {
                            $this->service->deductUnitBalance($lotNo, $slip_details->stage_master_unit_id, $take);

                            $od = \App\Models\OrderProductSetDetail::find($detail->id);
                            if ($od) {
                                $od->remaining_quantity = max(0, $od->remaining_quantity - $take);
                                $od->save();
                            }

                            \App\Models\ProductionOutflowInventory::create([
                                'type' => 'sampling',
                                'order_main_id' => $order_id,
                                'slip_id' => $slip_id,
                                'lot_no' => $lotNo,
                                'rack_id' => $data['rack_id'] ?? null,
                                'product_id' => $data['product_id'],
                                'color_id' => $data['color_id'],
                                'size_id' => $detail->id,
                                'quantity' => $take,
                                'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                'remarks' => "Sampling Outflow"
                            ]);
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