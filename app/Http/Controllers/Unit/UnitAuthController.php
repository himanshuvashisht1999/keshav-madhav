<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\StageMasterUnit;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use App\Models\OrderProductSet;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class UnitAuthController extends Controller
{
    // Define Stage Constants
    const STAGE_PRINTING = 1;
    const STAGE_CUTTING = 3;
    const STAGE_STITCHING = 4;
    const STAGE_PACKING = 11;


    public function showLogin()
    {
        if (session()->has('unit_auth')) {
            return redirect()->route('unit.dashboard');
        }
        return view('unit.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'password' => 'required',
        ]);

        $unit = StageMasterUnit::where('employee_id', $request->employee_id)
            ->where('password', $request->password)
            ->where('status', 1)
            ->first();

        if ($unit) {
            session([
                'unit_auth' => [
                    'id' => $unit->id,
                    'employee_id' => $unit->employee_id,
                    'name' => $unit->name,
                    'stage_id' => $unit->master_stage_id
                ]
            ]);
            return redirect()->route('unit.dashboard')->withSuccess('Logged in successfully');
        } else {
            return redirect()->back()->withError('Invalid credentials or account inactive.');
        }
    }

    public function dashboard()
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unit = StageMasterUnit::findOrFail($unitAuth['id']);

        $response['data'] = $unit;
        $response['stage_master_unit_id'] = Crypt::encryptString($unit->id);

        // Pass next stages for Cutting Master
        $nextStages = [];
        if ($unit->master_stage_id == self::STAGE_CUTTING) {
            $nextStages = \App\Models\MasterProductStage::whereIn('id', [self::STAGE_PRINTING, self::STAGE_STITCHING])->get();
        }

        return view('unit.upload', $response + ['nextStages' => $nextStages]);
    }

    public function submitSlip(Request $request)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        try {
            $stageMasterUnitId = Crypt::decryptString($request->stage_master_unit_id);
            $data = StageMasterUnit::findOrFail($stageMasterUnitId);

            $request->validate([
                'photo_data' => 'required'
            ]);

            $slip_file = null;

            if ($request->photo_data) {
                $image = $request->photo_data;

                // Remove base64 prefix
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
                $image = str_replace(' ', '+', $image);

                // Decode base64
                $imageData = base64_decode($image);

                // Check if decode was successful
                if ($imageData === false) {
                    return redirect()->back()->withErrors(['photo_data' => 'Invalid image data. Please try capturing the photo again.']);
                }

                $slip_file = 'production-slip-' . rand(1000, 9999) . '_' . time() . '.jpg';
                $destinationPath = public_path('assets/production_slips');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true);
                }

                // Save the file
                $fileSaved = file_put_contents($destinationPath . '/' . $slip_file, $imageData);

                if ($fileSaved === false) {
                    \Log::error('Failed to save production slip file', [
                        'path' => $destinationPath . '/' . $slip_file,
                        'unit_id' => $stageMasterUnitId
                    ]);
                    return redirect()->back()->withErrors(['photo_data' => 'Failed to save image file. Please try again.']);
                }
            }

            // if ($request->type == 1) {
            //     $save_data = new FabricRollAssigning;
            //     $save_data->stage_master_unit_id = $data->id;
            //     $save_data->slip_file = $slip_file;
            //     $save_data->status = 0;
            //     if ($request->has('to_stage_id'))
            //         $save_data->to_stage_id = $request->to_stage_id;
            //     $save_data->save();

            //     \Log::info('FabricRollAssigning saved', ['id' => $save_data->id, 'slip_file' => $slip_file]);
            // } else {
            $save_data = new ProductionSlipDigitization;
            $save_data->from_stage_id = $data->master_stage_id;
            $save_data->stage_master_unit_id = $data->id;
            $save_data->slip_file = $slip_file;
            $save_data->status = 0;
            if ($request->has('to_stage_id'))
                $save_data->to_stage_id = $request->to_stage_id;
            if ($request->has('order_product_set_id'))
                $save_data->order_product_set_id = $request->order_product_set_id;
            $save_data->save();

            \Log::info('ProductionSlipDigitization saved', ['id' => $save_data->id, 'slip_file' => $slip_file]);
            //}

            return redirect()->back()->withSuccess('Production slip uploaded successfully.');

        } catch (\Exception $e) {
            dd('error' . $e->getMessage());
            \Log::error('Error in submitSlip', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        session()->forget('unit_auth');
        return redirect()->route('unit.login')->withSuccess('Logged out successfully');
    }

    public function history()
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];

        // Get all uploaded slips for this unit
        $fabricSlips = FabricRollAssigning::where('stage_master_unit_id', $unitId)
            ->whereNotNull('slip_file')
            ->orderBy('created_at', 'desc')
            ->get();

        $productionSlips = ProductionSlipDigitization::where('stage_master_unit_id', $unitId)
            ->whereNotNull('slip_file')
            ->with('fromStage')
            ->orderBy('created_at', 'desc')
            ->get();

        // Merge and sort by date
        $allSlips = collect();

        foreach ($fabricSlips as $slip) {
            $allSlips->push([
                'id' => $slip->id,
                'type' => 'fabric',
                'slip_file' => $slip->slip_file,
                'created_at' => $slip->created_at,
                'status' => $slip->status,
                'lot_no' => $slip->lot_no ?? '-',
                'order_no' => $slip->order_no ?? '-',
            ]);
        }

        foreach ($productionSlips as $slip) {
            $allSlips->push([
                'id' => $slip->id,
                'type' => 'production',
                'slip_file' => $slip->slip_file,
                'created_at' => $slip->created_at,
                'status' => $slip->status,
                'lot_no' => $slip->lot_no ?? '-',
                'stage' => $slip->fromStage->name ?? '-',
            ]);
        }

        $allSlips = $allSlips->sortByDesc('created_at');

        return view('unit.history', [
            'slips' => $allSlips,
            'unit' => StageMasterUnit::find($unitId)
        ]);
    }

    public function viewSlip($type, $id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];

        // Try fetching as Production Slip first
        $slip = ProductionSlipDigitization::where('id', $id)
            ->where('stage_master_unit_id', $unitId)
            ->with(['fromStage', 'getUnitMaster.masterFabricWarehouse'])
            ->first();

        // If not found, try Fabric Roll Assigning (legacy/fabric type)
        // Note: The history view currently forces 'production' type in URL for all, 
        // so we must handle the ID lookup across both tables or differentiate.
        // Ideally, we should respect the 'type' param, but the user issue was a 404 on "fabric" type.
        // Let's first support the explicit type if provided.

        if (!$slip && $type == 'fabric') {
            $slip = FabricRollAssigning::where('id', $id)
                ->where('stage_master_unit_id', $unitId)
                ->with(['stageMasterUnit.masterFabricWarehouse'])
                ->first();

            // FabricAssigning model structure is different, so we might need to normalize or handle view differently.
            // But for now, let's just ensure we don't 404 if it exists.
        }

        if (!$slip) {
            // Fallback: If type was passed as 'production' (due to blade change) but it's actually in fabric table
            $slip = FabricRollAssigning::where('id', $id)
                ->where('stage_master_unit_id', $unitId)
                ->with(['stageMasterUnit.masterFabricWarehouse'])
                ->first();
        }

        if (!$slip) {
            abort(404, 'Slip not found.');
        }

        // Check if user is the creator (sender) OR the receiver (via transaction)
        $isCreator = $slip->stage_master_unit_id == $unitId;
        $isReceiver = false;

        if (!$isCreator) {
            // Check if this unit is a receiver in any transaction linked to this slip
            $isReceiver = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $id)
                ->where('sub_stage_id_to', $unitId)
                ->exists();

            if (!$isReceiver) {
                $isReceiver = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $id)
                    ->where('sub_stage_id_to', $unitId)
                    ->exists();
            }

            if (!$isReceiver) {
                $isReceiver = \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $id)
                    ->where('sub_stage_id_to', $unitId)
                    ->exists();
            }

            if (!$isReceiver) {
                // Also check legacy/direct method just in case (to_stage_id matches, although less specific)
                // But better to be strict. For now, if not creator and not in transaction, deny.
                // UNLESS it's the "to_stage_id" match + maybe assumption? 
                // Let's stick to transaction check for now as it handles specific unit assignment.
            }
        }

        if (!$isCreator && !$isReceiver) {
            abort(403, 'Unauthorized access to this slip.');
        }

        $data = [
            'slip' => $slip,
            'lot' => null,
            'rolls' => collect(),
            'printing' => null,
            'printing_sizes' => collect(),
            'stage_transaction' => null,
            'stage_sizes' => collect(),
            'packing_details' => null,
            'unit' => StageMasterUnit::find($unitId)
        ];

        // Fetch detailed data based on save_type
        switch ($slip->save_type) {

            // TYPE 1: LOT / ROLLS
            case 1:
                $lot = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)
                    ->with([
                        'orderMain',
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.master_design_pattern',
                        'orderProductSet.master_product_fitting',
                    ])
                    ->first();

                if ($lot) {
                    $rolls = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
                        ->with([
                            'fabricRollAssigningsDetail',
                            'stageMasterUnit.masterFabricWarehouse'
                        ])
                        ->get();

                    $data['lot'] = $lot;
                    $data['rolls'] = $rolls;
                }
                break;

            // TYPE 2: PRINTING
            case 2:
                $printing = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
                    ->with('from_stage', 'to_stage')
                    ->first();

                if ($printing) {
                    $printingSizes = \App\Models\OrderPrintingStageTransactionDetail::where(
                        'order_printing_stage_transaction_id',
                        $printing->id
                    )->get();

                    $data['printing'] = $printing;
                    $data['printing_sizes'] = $printingSizes;
                }
                break;

            // TYPE 3: OTHER (STITCHING / STAGE MOVEMENT)
            case 3:
                if ($slip->from_stage_id == 1) {
                    $stageTransaction = \App\Models\OrderPrintingToStichingTransaction::where(
                        'production_slip_digitization_id',
                        $slip->id
                    )
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = \App\Models\OrderPrintingToStichingTransactionDetail::where(
                            'order_printing_to_stiching_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                } else {
                    $stageTransaction = \App\Models\OrderStageTransaction::where(
                        'production_slip_digitization_id',
                        $slip->id
                    )
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = \App\Models\OrderStageTransactionDetail::where(
                            'order_stage_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                }

                // Packing details if from packing stage
                if ($slip->from_stage_id == 11) {
                    $packingDetails = \App\Models\PackingMain::where('slip_id', $slip->id)
                        ->with(['cartons.boxes.items.detail', 'cartons.items.detail'])
                        ->first();
                    $data['packing_details'] = $packingDetails;
                }
                break;
        }

        return view('unit.view_slip', $data);
    }
    public function assignments()
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);
        $assignments = [];
        $type = '';
        // Only Cutting & Packing units can close/reopen tasks
        $isCutting = $unit->master_stage_id == self::STAGE_CUTTING;
        $isPacking = $unit->master_stage_id == self::STAGE_PACKING;
        $canCloseTasks = $isCutting || $isPacking;

        // view = open | closed (only meaningful for cutting/packing)
        $view = request()->get('view', 'open') === 'closed' ? 'closed' : 'open';
        if (!$canCloseTasks) {
            $view = 'open';
        }

        if ($isCutting) {
            $type = 'cutting';

            $query = OrderProductSet::where('stage_master_unit_id', $unitId)
                ->with(['orderMain.customer', 'fabric', 'colors', 'master_design_pattern'])
                ->orderBy('created_at', 'desc');

            if ($view === 'closed') {
                // Closed tasks for Cutting Master
                $assignments = $query
                    ->where('is_closed_for_unit', 1)
                    ->get();
            } else {
                // Open tasks (default) – exclude closed ones
                $assignments = $query
                    ->where(function ($q) {
                        $q->whereNull('is_closed_for_unit')
                            ->orWhere('is_closed_for_unit', 0);
                    })
                    ->get()
                    ->filter(function (OrderProductSet $set) {
                        // 1) If any quantity is still remaining, it is definitely pending
                        if (($set->remain_total_quantity ?? 0) > 0) {
                            return true;
                        }

                        // 2) If all qty is allocated (remain_total_quantity == 0), hide the set
                        //    only when both printing and stitching slips from cutting are completed.
                        $printingDone = ProductionSlipDigitization::where('order_product_set_id', $set->id)
                            ->where('from_stage_id', self::STAGE_CUTTING)
                            ->where('to_stage_id', self::STAGE_PRINTING)
                            ->where('status', 1)
                            ->exists();

                        $stitchingDone = ProductionSlipDigitization::where('order_product_set_id', $set->id)
                            ->where('from_stage_id', self::STAGE_CUTTING)
                            ->where('to_stage_id', self::STAGE_STITCHING)
                            ->where('status', 1)
                            ->exists();

                        // Keep in list unless both are done
                        return !($printingDone && $stitchingDone);
                    });
            }
        } else {
            $type = 'other';
            // Other Stages see Slips sent to them (ProductionSlipDigitization)
            // Filter by sub_stage_id_to = current unit's ID in Transactions

            $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster']);

            $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster']);

            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster']);

            if ($isPacking && $canCloseTasks) {
                // Packing unit: supports open/closed state like cutting
                if ($view === 'closed') {
                    $ass1Query->where('is_closed_for_unit', 1);
                    $ass2Query->where('is_closed_for_unit', 1);
                    $ass3Query->where('is_closed_for_unit', 1);
                } else {
                    $ass1Query->where('remaining_quantity', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('is_closed_for_unit')
                                ->orWhere('is_closed_for_unit', 0);
                        });
                    $ass2Query->where('remaining_quantity', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('is_closed_for_unit')
                                ->orWhere('is_closed_for_unit', 0);
                        });
                    $ass3Query->where('remaining_quantity', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('is_closed_for_unit')
                                ->orWhere('is_closed_for_unit', 0);
                        });
                }
            } else {
                // Other stages (printing, stitching, etc.) can only see open work; no close option
                $ass1Query->where('remaining_quantity', '>', 0);
                $ass2Query->where('remaining_quantity', '>', 0);
                $ass3Query->where('remaining_quantity', '>', 0);
            }

            $ass1 = $ass1Query->get()
                ->map(function ($item) {
                    $item->transaction_type = 'stage';
                    return $item;
                });

            $ass2 = $ass2Query->get()
                ->map(function ($item) {
                    $item->transaction_type = 'printing';
                    return $item;
                });

            $ass3 = $ass3Query->get()
                ->map(function ($item) {
                    $item->transaction_type = 'printing_to_stitching'; // or just 'stitching'? keeping specific
                    return $item;
                });

            // Merge
            $assignments = $ass1->merge($ass2)->merge($ass3)->sortByDesc('created_at');
        }

        return view('unit.assignments', compact('assignments', 'unit', 'type', 'view', 'canCloseTasks'));
    }

    /**
     * Mark a unit assignment as closed (so it is hidden from the open list).
     */
    public function closeAssignment($type, $id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);

        if (!in_array($unit->master_stage_id, [self::STAGE_CUTTING, self::STAGE_PACKING])) {
            return redirect()->back()->withError('Close task option is only available for Cutting and Packing units.');
        }

        $record = $this->findAssignmentRecordForUnit($type, $id, $unitId);

        if (!$record) {
            return redirect()->back()->withError('Assignment not found or access denied.');
        }

        $record->is_closed_for_unit = 1;
        $record->save();

        return redirect()->route('unit.assignments')->withSuccess('Task closed successfully.');
    }

    /**
     * Re-open a previously closed assignment.
     */
    public function reopenAssignment($type, $id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);

        if (!in_array($unit->master_stage_id, [self::STAGE_CUTTING, self::STAGE_PACKING])) {
            return redirect()->back()->withError('Re-open task option is only available for Cutting and Packing units.');
        }

        $record = $this->findAssignmentRecordForUnit($type, $id, $unitId);

        if (!$record) {
            return redirect()->back()->withError('Assignment not found or access denied.');
        }

        $record->is_closed_for_unit = 0;
        $record->save();

        return redirect()->route('unit.assignments', ['view' => 'open'])->withSuccess('Task re-opened successfully.');
    }

    /**
     * Resolve the underlying record for a unit assignment by type.
     *
     * @param string $type
     * @param int $id
     * @param int $unitId
     * @return mixed
     */
    protected function findAssignmentRecordForUnit(string $type, int $id, int $unitId)
    {
        switch ($type) {
            case 'cutting':
                return OrderProductSet::where('id', $id)
                    ->where('stage_master_unit_id', $unitId)
                    ->first();

            case 'stage':
                return \App\Models\OrderStageTransaction::where('id', $id)
                    ->where('sub_stage_id_to', $unitId)
                    ->first();

            case 'printing':
                return \App\Models\OrderPrintingStageTransaction::where('id', $id)
                    ->where('sub_stage_id_to', $unitId)
                    ->first();

            case 'printing_to_stitching':
                return \App\Models\OrderPrintingToStichingTransaction::where('id', $id)
                    ->where('sub_stage_id_to', $unitId)
                    ->first();

            default:
                return null;
        }
    }
    public function showAssignmentDetails($type, $id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::with('masterFabricWarehouse')->find($unitId);

        $header = [];
        $sizeData = [];
        $transaction = null;
        $orderProductSet = null;
        $orderLot = null;

        if ($type === 'cutting') {
            $data = \App\Models\OrderProductSet::with([
                'stage_master_unit.masterFabricWarehouse',
                'fabric',
                'master_design_pattern',
                'orderMain.customer',
                'colors',
                'size_measurement',
                'master_product_fitting',
            ])->findOrFail($id);

            $orderProductSet = $data;

            $header = [
                'id' => $data->id,
                'order_no' => $data->orderMain->sku ?? '-',
                'date' => $data->created_at->format('d-m-Y'),
                'customer' => $data->orderMain->customer->name ?? '-',
                'design_no' => $data->design_number ?? '-',
                'fabric' => $data->fabric->name ?? '-',
                'color' => $data->colors->name ?? '-',
                'pattern' => $data->master_design_pattern->name ?? '-',
                'fitting' => $data->master_product_fitting?->name ?? '-',
                'warehouse' => $data->stage_master_unit->masterFabricWarehouse->cutting_master_name ?? '-',
                'unit_name' => $data->stage_master_unit->name ?? '-',
                'remark' => $data->remark ?? '-',
                'total_pcs' => $data->total_quantity ?? 0,
                'lot_no' => 'Pending',
            ];

            // Size breakdown
            $sizes = [$data->set_size];
            if (!empty($data->size_measurement?->size_group)) {
                $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
            }
            $sizeCounts = array_count_values($sizes);
            foreach ($sizeCounts as $size => $count) {
                $sizeData[] = [
                    'size' => $size,
                    'color' => $data->colors->name,
                    'pcs' => $count * $data->set_quantity,
                ];
            }
        } else {
            switch ($type) {
                case 'stage':
                    $transaction = \App\Models\OrderStageTransaction::where('id', $id)
                        ->where('sub_stage_id_to', $unitId)
                        ->with([
                            'from_stage',
                            'getFromUnitMaster',
                            'orderProduct.orderMain.customer',
                            'orderProduct.orderProductSet.fabric',
                            'orderProduct.orderProductSet.colors',
                            'orderProduct.orderProductSet.master_design_pattern',
                            'orderProduct.orderProductSet.master_product_fitting'
                        ])
                        ->firstOrFail();
                    break;
                case 'printing':
                    $transaction = \App\Models\OrderPrintingStageTransaction::where('id', $id)
                        ->where('sub_stage_id_to', $unitId)
                        ->with([
                            'from_stage',
                            'getFromUnitMaster',
                            'orderProduct.orderMain.customer',
                            'orderProduct.orderProductSet.fabric',
                            'orderProduct.orderProductSet.colors',
                            'orderProduct.orderProductSet.master_design_pattern',
                            'orderProduct.orderProductSet.master_product_fitting'
                        ])
                        ->firstOrFail();
                    break;
                case 'printing_to_stitching':
                    $transaction = \App\Models\OrderPrintingToStichingTransaction::where('id', $id)
                        ->where('sub_stage_id_to', $unitId)
                        ->with([
                            'from_stage',
                            'getFromUnitMaster',
                            'orderProduct.orderMain.customer',
                            'orderProduct.orderProductSet.fabric',
                            'orderProduct.orderProductSet.colors',
                            'orderProduct.orderProductSet.master_design_pattern',
                            'orderProduct.orderProductSet.master_product_fitting'
                        ])
                        ->firstOrFail();
                    break;
                case 'production':
                    // If it's a direct slip view from assignments
                    return $this->viewSlip($type, $id);
                default:
                    abort(404, 'Invalid assignment type');
            }

            // Get associated OrderProductSet from the loaded transaction
            if ($transaction && $transaction->orderProduct && $transaction->orderProduct->orderProductSet) {
                $orderProductSet = $transaction->orderProduct->orderProductSet;
            }

            // Fallback: If orderProductSet is missing, try finding via lot_no
            if (!$orderProductSet && $transaction && $transaction->lot_no) {
                $orderLot = \App\Models\OrderLot::where('lot_no', $transaction->lot_no)
                    ->with([
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.master_design_pattern',
                        'orderProductSet.master_product_fitting',
                        'orderProductSet.orderMain.customer',
                        'orderMain.customer'
                    ])->first();
                if ($orderLot && $orderLot->orderProductSet) {
                    $orderProductSet = $orderLot->orderProductSet;
                }
            }

            $hOrderMain = $transaction->orderProduct->orderMain ?? $orderProductSet->orderMain ?? ($orderLot->orderMain ?? null);

            $header = [
                'id' => $id,
                'order_no' => $hOrderMain->sku ?? '-',
                'date' => $transaction->created_at->format('d-m-Y'),
                'customer' => $hOrderMain->customer->name ?? '-',
                'design_no' => $orderProductSet->design_number ?? '-',
                'fabric' => $orderProductSet->fabric->name ?? '-',
                'color' => $orderProductSet->colors->name ?? '-',
                'pattern' => $orderProductSet->master_design_pattern->name ?? '-',
                'fitting' => $orderProductSet->master_product_fitting?->name ?? '-',
                'lot_no' => $transaction->lot_no,
                'from_stage' => $transaction->from_stage->name ?? '-',
                'sent_by' => $transaction->getFromUnitMaster->name ?? '-',
                'total_pcs' => $transaction->remaining_quantity,
                'remark' => $transaction->remarks ?? '-',
            ];

            // Size breakdown for transaction
            if ($type === 'printing' && method_exists($transaction, 'printingDetails')) {
                foreach ($transaction->printingDetails as $det) {
                    $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
                }
            } elseif ($type === 'stage' || $type === 'printing_to_stitching') {
                // Determine details table
                $details = [];
                if ($type === 'stage') {
                    $details = \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $id)->get();
                } else {
                    $details = \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $id)->get();
                }
                foreach ($details as $det) {
                    $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
                }
            }
        }

        // Pass next stages for Cutting Master
        $nextStages = [];
        if ($unit->master_stage_id == self::STAGE_CUTTING) {
            $nextStages = \App\Models\MasterProductStage::whereIn('id', [self::STAGE_PRINTING, self::STAGE_STITCHING])->get();
        }

        return view('unit.assignment_details', [
            'header' => $header,
            'sizeData' => $sizeData,
            'transaction' => $transaction,
            'type' => $type,
            'unit' => $unit,
            'nextStages' => $nextStages,
            'encrypted_unit_id' => \Illuminate\Support\Facades\Crypt::encryptString($unitId)
        ]);
    }

    public function downloadSlip($slipId)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $slip = ProductionSlipDigitization::with([
            'fromStage',
            'getUnitMaster.masterFabricWarehouse'
        ])->findOrFail($slipId);

        $data = [
            'slip' => $slip,
            'lot' => null,
            'rolls' => collect(),
            'printing' => null,
            'printing_sizes' => collect(),
        ];

        switch ($slip->save_type) {
            case 1:
                $lot = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)
                    ->with([
                        'orderMain',
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.master_design_pattern',
                        'orderProductSet.master_product_fitting',
                    ])
                    ->first();

                if ($lot) {
                    $rolls = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
                        ->with([
                            'fabricRollAssigningsDetail',
                            'stageMasterUnit.masterFabricWarehouse'
                        ])
                        ->get();

                    $data['lot'] = $lot;
                    $data['rolls'] = $rolls;
                }
                break;

            case 2:
                $printing = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
                    ->with('from_stage', 'to_stage')
                    ->first();

                if ($printing) {
                    $printingSizes = \App\Models\OrderPrintingStageTransactionDetail::where(
                        'order_printing_stage_transaction_id',
                        $printing->id
                    )->get();

                    $data['printing'] = $printing;
                    $data['printing_sizes'] = $printingSizes;
                }
                break;

            case 3:
                if ($slip->from_stage_id == 1) {
                    $stageTransaction = \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = \App\Models\OrderPrintingToStichingTransactionDetail::where(
                            'order_printing_to_stiching_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                } else {
                    $stageTransaction = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = \App\Models\OrderStageTransactionDetail::where(
                            'order_stage_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                }

                if ($slip->from_stage_id == 11) {
                    $packingDetails = \App\Models\PackingMain::where('slip_id', $slip->id)
                        ->with(['cartons.boxes.items.detail', 'cartons.items.detail'])
                        ->first();
                    $data['packing_details'] = $packingDetails;
                }
                break;
        }

        $pdf = Pdf::loadView('admin.uploaded_slips.pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download('Production_Slip_' . $slipId . '.pdf');
    }

    public function downloadCmpo($id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $data = \App\Models\OrderProductSet::with([
            'stage_master_unit.masterFabricWarehouse',
            'fabric',
            'master_design_pattern',
            'orderMain.customer',
            'colors',
            'size_measurement',
            'master_product_fitting',
        ])->findOrFail($id);

        // Header info matching buildCmpoData logic
        $cmpoHeader = [
            'cmpo_id' => $data->id,
            'date' => $data->created_at->format('d-m-Y'),
            'order_no' => $data->orderMain->sku ?? '-',
            'customer' => $data->orderMain->customer->name ?? '-',
            'design_no' => $data->design_number ?? '-',
            'color' => $data->colors->name ?? '-',
            'fabric' => $data->fabric->name ?? '-',
            'pattern' => $data->master_design_pattern->name ?? '-',
            'warehouse_name' => $data->stage_master_unit->masterFabricWarehouse->cutting_master_name ?? '-',
            'cuttingMaster' => $data->stage_master_unit->name ?? '-',
            'cuttingMasterAddress' => $data->stage_master_unit->masterFabricWarehouse->address ?? '-',
            'fitting' => $data->master_product_fitting?->name ?? '-',
            'remark' => $data->remark ?? '-',
            'total_pcs' => $data->total_quantity ?? 0,
        ];

        $sizeData = [];
        $sizes = [$data->set_size];
        if (!empty($data->size_measurement?->size_group)) {
            $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
        }
        $sizeCounts = array_count_values($sizes);
        foreach ($sizeCounts as $size => $count) {
            $sizeData[$size] = [
                'design_no' => $data->design_number,
                'color' => $data->colors->name,
                'size' => $size,
                'pcs' => $count * $data->set_quantity,
            ];
        }

        $pdf = Pdf::loadView('admin.product_order.cmpo_slip', [
            'header' => $cmpoHeader,
            'sizeData' => $sizeData,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('CMPO-' . $id . '.pdf');
    }
}
