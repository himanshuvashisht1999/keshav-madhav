<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\StageMasterUnit;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use App\Models\OrderProductSet;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderStageTransaction;
use App\Models\OrderCuttingStage;
use App\Models\GeneralSettings;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
                    Log::error('Failed to save production slip file', [
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

            // Set save_type and type from request
            if ($request->has('save_type'))
                $save_data->save_type = $request->save_type;
            if ($request->has('type'))
                $save_data->type = $request->type;

            if ($request->has('to_stage_id'))
                $save_data->to_stage_id = $request->to_stage_id;
            if ($request->has('order_product_set_id'))
                $save_data->order_product_set_id = $request->order_product_set_id;
            if ($request->has('lot_no'))
                $save_data->lot_no = $request->lot_no;
            $save_data->save();

            // Update Transaction Image if ID provided
            if ($request->transaction_id && $request->transaction_type) {
                $tx = null;
                $txId = $request->transaction_id;
                $txType = $request->transaction_type;

                if ($txType === 'printing_to_stitching') {
                    $tx = OrderPrintingToStichingTransaction::find($txId);
                } elseif ($txType === 'printing') {
                    $tx = OrderPrintingStageTransaction::find($txId);
                } elseif ($txType === 'cutting') {
                    $tx = OrderCuttingStage::find($txId);
                } else {
                    $tx = OrderStageTransaction::find($txId);
                }

                if ($tx) {
                    $tx->update([
                        'image' => $slip_file,
                        'production_slip_digitization_id' => $save_data->id // Link them
                    ]);
                }
            }

            Log::info('ProductionSlipDigitization saved', ['id' => $save_data->id, 'slip_file' => $slip_file]);
            //}

            return redirect()->back()->with('success', 'Production slip uploaded successfully.');

        } catch (\Exception $e) {
            dd('error' . $e->getMessage());
            Log::error('Error in submitSlip', [
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

    public function history(Request $request)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $viewType = $request->get('view_type', 'slips'); // 'slips' or 'tasks'

        $lotNo = $request->input('lot_no');
        $customerSearch = $request->input('customer');
        $status = $request->input('status');

        if ($viewType === 'tasks') {
            // --- SHARED DATA FETCHING ---
            $unit = StageMasterUnit::find($unitId);
            $isCutting = $unit->master_stage_id == self::STAGE_CUTTING;
            $activityType = $request->get('activity_type');
            $tasks = collect();

            // 1. RECEIVED TASKS (Where unit is the destination)
            if (!$activityType || $activityType === 'received') {
                // Assignments (Cutting Only)
                if ($isCutting) {
                    $qAssign = \App\Models\OrderCuttingStage::where('to_assign_id', $unitId)->with(['productSet.orderMain']);
                    if ($lotNo)
                        $qAssign->whereHas('productSet', function ($qs) use ($lotNo) {
                            $qs->where('design_number', 'like', '%' . $lotNo . '%'); });
                    if ($customerSearch)
                        $qAssign->whereHas('productSet.orderMain', function ($qs) use ($customerSearch) {
                            $qs->where('sku', 'like', '%' . $customerSearch . '%'); });

                    foreach ($qAssign->get() as $item) {
                        $tasks->push([
                            'id' => $item->id,
                            'event_type' => 'received',
                            'type' => 'cutting',
                            'lot_no' => $item->lot_no ?? '-',
                            'design_no' => $item->productSet->design_number ?? '-',
                            'customer' => $item->productSet->orderMain->customer->name ?? '-',
                            'size_sets' => $item->productSet->size_set_name ?? '-',
                            'from_stage' => 'Admin Assignment',
                            'quantity' => $item->quantity,
                            'created_at' => $item->created_at,
                            'status' => ($item->status == 1 || $item->image) ? 1 : 0
                        ]);
                    }
                }

                // Transactions (All Units)
                $q1 = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'orderProduct.orderMain']);
                $q2 = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'orderProduct.orderMain']);
                $q3 = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'orderProduct.orderMain']);

                foreach ([$q1, $q2, $q3] as $idx => $q) {
                    if ($lotNo)
                        $q->where('lot_no', 'like', '%' . $lotNo . '%');
                    if ($customerSearch)
                        $q->where(function ($sq) use ($customerSearch) {
                            $sq->where('name', 'like', '%' . $customerSearch . '%')
                                ->orWhereHas('orderProduct.orderMain', function ($ssq) use ($customerSearch) {
                                    $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                        });

                    $txTypes = ['stage', 'printing', 'printing_to_stitching'];
                    foreach ($q->get() as $item) {
                        // ROBUST SKU FETCHING
                        $sku = $item->orderProduct->orderMain->sku ?? $item->sku ?? '-';
                        if ($sku === '-' || empty($sku)) {
                            $sku = $item->orderProduct?->orderProductSet?->orderMain?->sku ?? '-';
                        }
                        if ($sku === '-' && !empty($item->lot_no)) {
                            $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderMain')->first();
                            $sku = $lRef->orderMain->sku ?? $lRef->order_no ?? '-';
                        }

                        $tasks->push([
                            'id' => $item->id,
                            'event_type' => 'received',
                            'type' => $txTypes[$idx],
                            'lot_no' => $item->lot_no ?? '-',
                            'design_no' => $item->orderProduct?->orderProductSet?->design_number ?? '-',
                            'customer' => $item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'size_sets' => $item->orderProduct?->orderProductSet?->size_set_name ?? '-',
                            'from_stage' => $item->from_stage->name ?? '-',
                            'quantity' => $item->quantity,
                            'created_at' => $item->created_at,
                            'status' => ($item->image) ? 1 : 0
                        ]);
                    }
                }
            }

            // 2. SENT TASKS (Where unit is the source)
            if (!$activityType || $activityType === 'sent') {
                // Fabric Rolls Alotted (Mainly for Cutting)
                $qRolls = \App\Models\FabricRollAssigning::where('stage_master_unit_id', $unitId)->with(['orderProductSet.orderMain', 'fabricRollAssigningsDetail']);
                if ($lotNo)
                    $qRolls->where('lot_no', 'like', '%' . $lotNo . '%');
                if ($customerSearch) {
                    $qRolls->where(function ($sq) use ($customerSearch) {
                        $sq->where('order_no', 'like', '%' . $customerSearch . '%')
                            ->orWhereHas('orderProductSet.orderMain', function ($ssq) use ($customerSearch) {
                                $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                    });
                }

                foreach ($qRolls->get() as $item) {
                    $customerName = $item->orderProductSet?->orderMain?->customer?->name ?? '-';
                    $sku = $item->order_no ?? $item->orderProductSet->orderMain->sku ?? '-';
                    if ($sku === '-' && !empty($item->lot_no)) {
                        $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderMain')->first();
                        $sku = $lRef->orderMain->sku ?? $lRef->order_no ?? '-';
                    }

                    $tasks->push([
                        'id' => $item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => $item->lot_no ?? '-',
                        'design_no' => $item->orderProductSet->design_number ?? '-',
                        'customer' => $customerName,
                        'size_sets' => $item->orderProductSet->size_set_name ?? '-',
                        'from_stage' => 'Rolls Alotted',
                        'quantity' => $item->fabricRollAssigningsDetail->sum('quantity') ?: 0,
                        'created_at' => $item->created_at,
                        'status' => 1
                    ]);
                }

                // Sent Transactions
                $s1 = \App\Models\OrderStageTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain']);
                $s2 = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain']);
                $s3 = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain']);

                foreach ([$s1, $s2, $s3] as $idx => $q) {
                    if ($lotNo)
                        $q->where('lot_no', 'like', '%' . $lotNo . '%');
                    if ($customerSearch)
                        $q->where(function ($sq) use ($customerSearch) {
                            $sq->where('name', 'like', '%' . $customerSearch . '%')
                                ->orWhereHas('orderProduct.orderMain', function ($ssq) use ($customerSearch) {
                                    $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                        });

                    $txTypes = ['stage', 'printing', 'printing_to_stitching'];
                    foreach ($q->get() as $item) {
                        $sku = $item->orderProduct->orderMain->sku ?? $item->sku ?? '-';
                        if ($sku === '-' || empty($sku)) {
                            $sku = $item->orderProduct?->orderProductSet?->orderMain?->sku ?? '-';
                        }
                        if ($sku === '-' && !empty($item->lot_no)) {
                            $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderMain')->first();
                            $sku = $lRef->orderMain->sku ?? $lRef->order_no ?? '-';
                        }

                        $tasks->push([
                            'id' => $item->id,
                            'event_type' => 'sent',
                            'type' => $txTypes[$idx],
                            'lot_no' => $item->lot_no ?? '-',
                            'design_no' => $item->orderProduct?->orderProductSet?->design_number ?? '-',
                            'customer' => $item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'size_sets' => $item->orderProduct?->orderProductSet?->size_set_name ?? '-',
                            'from_stage' => $item->to_stage->name ?? 'Next Stage',
                            'quantity' => $item->quantity,
                            'created_at' => $item->created_at,
                            'status' => 1
                        ]);
                    }
                }
            }

            // Filtering by status on merged collection if needed
            if ($status === 'done')
                $tasks = $tasks->where('status', 1);
            elseif ($status === 'pending')
                $tasks = $tasks->where('status', 0);

            return view('unit.history', [
                'tasks' => $tasks->sortByDesc('created_at'),
                'unit' => $unit,
                'viewType' => 'tasks'
            ]);
        }

        // --- Original Slips Logic ---
        $fabricQuery = FabricRollAssigning::where('stage_master_unit_id', $unitId)
            ->whereNotNull('slip_file')
            ->with(['fabricRollAssigningsDetail', 'orderProductSet']);

        if ($lotNo) {
            $fabricQuery->where('lot_no', 'like', '%' . $lotNo . '%');
        }
        if ($customerSearch) {
            $fabricQuery->where('order_no', 'like', '%' . $customerSearch . '%');
        }
        if ($status !== null && $status !== '') {
            $statusVal = $status === 'done' ? 1 : 0;
            $fabricQuery->where('status', $statusVal);
        }

        $fabricSlips = $fabricQuery->orderBy('created_at', 'desc')->get();

        $productionQuery = ProductionSlipDigitization::where('stage_master_unit_id', $unitId)
            ->whereNotNull('slip_file')
            ->with([
                'fromStage',
                'orderLots.orderProductSet.size_measurement',
                'orderPrintingStageTransaction.orderProduct.orderProductSet.size_measurement',
                'orderPrintingStageTransaction.orderProduct.orderProductSet.orderMain',
                'orderPrintingStageTransaction.details',
                'orderStageTransaction.orderProduct.orderProductSet.size_measurement',
                'orderStageTransaction.orderProduct.orderProductSet.orderMain',
                'orderStageTransaction.details',
                'orderPrintingToStichingTransaction.orderProduct.orderProductSet.size_measurement',
                'orderPrintingToStichingTransaction.orderProduct.orderProductSet.orderMain',
                'orderPrintingToStichingTransaction.details',
                'fabricRollAssignings.fabricRollAssigningsDetail',
                'parts',
                'fabricRollAssignings.fabricRollAssigningsDetail',
                'parts',
                'orderProductSet.orderMain'
            ]);

        if ($lotNo) {
            $productionQuery->where(function ($q) use ($lotNo) {
                $q->where('lot_no', 'like', '%' . $lotNo . '%')
                    ->orWhereHas('orderLots', function ($sq) use ($lotNo) {
                        $sq->where('lot_no', 'like', '%' . $lotNo . '%');
                    })
                    ->orWhereHas('orderPrintingStageTransaction', function ($pq) use ($lotNo) {
                        $pq->where('lot_no', 'like', '%' . $lotNo . '%');
                    })
                    ->orWhereHas('orderStageTransaction', function ($tq) use ($lotNo) {
                        $tq->where('lot_no', 'like', '%' . $lotNo . '%');
                    });
            });
        }

        if ($customerSearch) {
            $productionQuery->where(function ($q) use ($customerSearch) {
                $q->whereHas('orderProductSet.orderMain', function ($sq) use ($customerSearch) {
                    $sq->where('name', 'like', '%' . $customerSearch . '%');
                })
                    ->orWhereHas('orderLots.orderMain', function ($sq) use ($customerSearch) {
                        $sq->where('name', 'like', '%' . $customerSearch . '%');
                    })
                    ->orWhereHas('orderPrintingStageTransaction.orderProduct.orderProductSet.size_measurement',
                'orderPrintingStageTransaction.orderProduct.orderProductSet.orderMain', function ($sq) use ($customerSearch) {
                        $sq->where('name', 'like', '%' . $customerSearch . '%');
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $statusVal = $status === 'done' ? 1 : 0;
            $productionQuery->where('status', $statusVal);
        }

        $productionSlips = $productionQuery->orderBy('created_at', 'desc')->get();

        // Merge and sort by date
        $allSlips = collect();

        foreach ($fabricSlips as $slip) {
            $totalPieces = $slip->fabricRollAssigningsDetail->sum('quantity');
            $sizes = $slip->fabricRollAssigningsDetail->pluck('size')->unique()->filter()->values();
            
            $allSlips->push([
                'id' => $slip->id,
                'type' => 'fabric',
                'slip_file' => $slip->slip_file,
                'created_at' => $slip->created_at,
                'status' => $slip->status,
                'lot_no' => $slip->lot_no ?? '-',
                'customer' => $slip->orderProductSet?->orderMain?->customer?->name ?? '-',
                'design_no' => $slip->orderProductSet->design_number ?? '-',
                'pieces' => $totalPieces,
                'size_sets' => $sizes->isNotEmpty() ? $sizes->join(', ') : '-',
            ]);

                }

        foreach ($productionSlips as $slip) {
            $sessions = collect();

            // 1. Cutting Sessions (OrderLot)
            foreach ($slip->orderLots as $lot) {
                $rolls = $slip->fabricRollAssignings->where('order_lot_id', $lot->id);
                $totalPieces = 0;
                $sizes = collect();
                foreach ($rolls as $roll) {
                    $totalPieces += $roll->fabricRollAssigningsDetail->sum('quantity');
                    $sizes = $sizes->merge($roll->fabricRollAssigningsDetail->pluck('size'));
                }
                
                $sessions->push([
                    'type' => 'Cutting',
                    'lot_no' => $lot->lot_no,
                    'pieces' => $totalPieces,
                    'size_sets' => $lot->orderProductSet?->size_set_name ?? '-',
                    'design_no' => $lot->orderProductSet->design_number ?? '-',
                    'customer' => $lot->orderMain?->customer?->name ?? '-',
                ]);
            }

            // 2. Printing Sessions
            foreach ($slip->orderPrintingStageTransaction as $opt) {
                $sessions->push([
                    'type' => 'Printing',
                    'lot_no' => $opt->lot_no,
                    'pieces' => $opt->details->sum('quantity'),
                    'size_sets' => $opt->orderProduct?->orderProductSet?->size_set_name ?? '-',
                    'design_no' => $opt->orderProduct?->orderProductSet?->design_number ?? '-',
                    'customer' => $opt->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-',
                ]);
            }

            // 3. Printing to Stitching Sessions
            foreach ($slip->orderPrintingToStichingTransaction as $optst) {
                $sessions->push([
                    'type' => 'Printing to Stitching',
                    'lot_no' => $optst->lot_no,
                    'pieces' => $optst->details->sum('quantity'),
                    'size_sets' => $optst->orderProduct?->orderProductSet?->size_set_name ?? '-',
                    'design_no' => $optst->orderProduct?->orderProductSet?->design_number ?? '-',
                    'customer' => $optst->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-',
                ]);
            }

            // 4. Transfer/Stage Sessions
            foreach ($slip->orderStageTransaction as $ost) {
                $sessions->push([
                    'type' => 'Transfer',
                    'lot_no' => $ost->lot_no,
                    'pieces' => $ost->details->sum('quantity'),
                    'size_sets' => $ost->orderProduct?->orderProductSet?->size_set_name ?? '-',
                    'design_no' => $ost->orderProduct?->orderProductSet?->design_number ?? '-',
                    'customer' => $ost->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-',
                ]);
            }

            // 5. Parts Sessions
            foreach ($slip->parts as $part) {
                $partDetails = \App\Models\ProductionDigitizationSetsDetails::where('production_slip_digitization_parts_id', $part->id)->get();
                $sizes = collect();
                $pieces = 0;
                if ($partDetails->isNotEmpty()) {
                    $pieces = $partDetails->sum('qauntity');
                    $sizes = $partDetails->pluck('size');
                } else {
                    $pieces = $part->single_quantity ?? 0;
                    if ($part->single_size) $sizes->push($part->single_size);
                }

                $sessions->push([
                    'type' => 'Part',
                    'lot_no' => $part->lot_no,
                    'pieces' => $pieces,
                    'size_sets' => \App\Models\MasterSizeMeasurement::find($part->set_size)?->name ?? '-',
                    'design_no' => $part->design_number ?? '-',
                    'customer' => '-',
                ]);
            }

            // Gather aggregate data for the card header
            $allLots = $sessions->pluck('lot_no')->unique()->filter()->values();
            if ($allLots->isEmpty() && $slip->lot_no) $allLots->push($slip->lot_no);
            
            $totalPieces = $sessions->sum('pieces');
            
            $designNos = $sessions->pluck('design_no')->unique()->filter()->values();
            if ($designNos->isEmpty() && $slip->orderProductSet?->design_number) $designNos->push($slip->orderProductSet->design_number);

            $allSlips->push([
                'id' => $slip->id,
                'type' => 'production',
                'slip_file' => $slip->slip_file,
                'created_at' => $slip->created_at,
                'status' => $slip->status,
                'lot_no' => $allLots->join(', ') ?: '-',
                'customer' => $slip->orderProductSet?->orderMain?->customer?->name ?? '-',
                'design_no' => $designNos->join(', ') ?: '-',
                'pieces' => $totalPieces,
                'size_sets' => $sessions->pluck('size_sets')->unique()->filter()->values()->join(', ') ?: '-',
                'sessions' => $sessions,
                'stage' => $slip->fromStage->name ?? '-',
            ]);
        }

        $allSlips = $allSlips->sortByDesc('created_at');

        return view('unit.history', [
            'slips' => $allSlips,
            'unit' => StageMasterUnit::find($unitId),
            'viewType' => 'slips'
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
            ->with([
                'fromStage',
                'getUnitMaster.masterFabricWarehouse',
                'orderProductSet.orderMain.customer',
                'orderProductSet.fabric',
                'orderProductSet.colors',
                'orderProductSet.master_design_pattern',
                'orderProductSet.master_product_fitting'
            ])
            ->first();

        if (!$slip && $type == 'fabric') {
            $slip = FabricRollAssigning::where('id', $id)
                ->with(['stageMasterUnit.masterFabricWarehouse'])
                ->first();
        }

        if (!$slip) {
            // Fallback for ID lookup across tables
            $slip = FabricRollAssigning::where('id', $id)
                ->with(['stageMasterUnit.masterFabricWarehouse'])
                ->first();
        }

        if (!$slip) {
            abort(404, 'Slip not found.');
        }

        // Check if user is the creator (sender) OR the receiver (via transaction)
        // 1. Is direct creator?
        $isCreator = $slip->stage_master_unit_id == $unitId;

        // 2. Is sender in any linked transaction?
        if (!$isCreator) {
            $isCreator = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists() ||
                \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists() ||
                \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists();
        }

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
            // Final fallback: If you are arriving from history, we should allow you to see what you DID.
            // Since we already filtered the history list to only show your interactions, 
            // if this was in your history list, you should be allowed to view it.
            if (request()->headers->get('referer') && str_contains(request()->headers->get('referer'), 'unit/history')) {
                // Allow view if redirected from history
            } else {
                abort(403, 'Unauthorized access to this slip.');
            }
        }

        // --- Fetch ALL digitized sessions linked to this slip (Multiple are now supported) ---

        // 1. Lots & Rolls (Cutting)
        $lots = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)
            ->with([
                'orderMain',
                'orderProductSet.fabric',
                'orderProductSet.colors',
                'orderProductSet.master_design_pattern',
                'orderProductSet.master_product_fitting',
            ])->get();

        $rolls = \App\Models\FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
            ->with([
                'fabricRollAssigningsDetail',
                'stageMasterUnit.masterFabricWarehouse'
            ])->get();

        // 2. Printing
        $printings = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        // 3. Other Stage Transfers
        $stage_transactions = collect();
        if ($slip->from_stage_id == 1) {
            $stage_transactions = \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)
                ->with([
                    'from_stage',
                    'to_stage',
                    'orderProduct.orderProductSet.fabric',
                    'orderProduct.orderProductSet.colors',
                    'orderProduct.orderProductSet.master_design_pattern',
                    'orderProduct.orderProductSet.master_product_fitting',
                    'orderProduct.orderProductSet.orderMain.customer',
                    'details'
                ])->get();
        } else {
            $stage_transactions = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
                ->with([
                    'from_stage',
                    'to_stage',
                    'orderProduct.orderProductSet.fabric',
                    'orderProduct.orderProductSet.colors',
                    'orderProduct.orderProductSet.master_design_pattern',
                    'orderProduct.orderProductSet.master_product_fitting',
                    'orderProduct.orderProductSet.orderMain.customer',
                    'details'
                ])->get();
        }

        // 4. Packing Details
        $packing_details = null;
        if ($slip->from_stage_id == 11) {
            $packing_details = \App\Models\PackingMain::where('slip_id', $slip->id)
                ->with(['cartons.boxes.items.detail', 'cartons.items.detail'])
                ->first();
        }

        // Consolidate ALL Order Details from all digitized sessions for Summary Header
        $consolidated = [
            'lot_nos' => collect(),
            'order_nos' => collect(),
            'design_nos' => collect(),
            'fabrics' => collect(),
            'colors' => collect(),
            'customers' => collect()
        ];

        // 1. From Lots
        foreach ($lots as $lot) {
            if ($lot->lot_no)
                $consolidated['lot_nos']->push($lot->lot_no);
            if ($lot->orderMain)
                $consolidated['order_nos']->push($lot->orderMain->sku);
            if ($lot->orderProductSet) {
                $ops = $lot->orderProductSet;
                if ($ops->design_number)
                    $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric)
                    $consolidated['fabrics']->push($ops->fabric->name);
                if ($ops->colors)
                    $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer)
                    $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        // 2. From Printings
        foreach ($printings as $printing) {
            if ($printing->lot_no)
                $consolidated['lot_nos']->push($printing->lot_no);
            if ($printing->orderProduct?->orderProductSet) {
                $ops = $printing->orderProduct->orderProductSet;
                if ($ops->orderMain)
                    $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number)
                    $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric)
                    $consolidated['fabrics']->push($ops->fabric->name);
                if ($ops->colors)
                    $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer)
                    $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        // 3. From Stage Transactions
        foreach ($stage_transactions as $tx) {
            if ($tx->lot_no)
                $consolidated['lot_nos']->push($tx->lot_no);
            if ($tx->orderProduct?->orderProductSet) {
                $ops = $tx->orderProduct->orderProductSet;
                if ($ops->orderMain)
                    $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number)
                    $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric)
                    $consolidated['fabrics']->push($ops->fabric->name);
                if ($ops->colors)
                    $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer)
                    $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        $summary = [
            'lot_no' => $consolidated['lot_nos']->unique()->filter()->implode(', '),
            'order_no' => $consolidated['order_nos']->unique()->filter()->implode(', '),
            'customer' => $consolidated['customers']->unique()->filter()->implode(', '),
            'design' => $consolidated['design_nos']->unique()->filter()->implode(', ')
        ];

        // Derive Size Group from first available session
        $orderSet = $slip->orderProductSet;
        if (!$orderSet && $lots->isNotEmpty())
            $orderSet = $lots->first()->orderProductSet;
        if (!$orderSet && $printings->isNotEmpty())
            $orderSet = $printings->first()->orderProduct?->orderProductSet;
        if (!$orderSet && $stage_transactions->isNotEmpty())
            $orderSet = $stage_transactions->first()->orderProduct?->orderProductSet;

        $pcs_in_set = '-';
        if ($orderSet) {
            $orderSet->loadMissing('size_measurement');
            if ($orderSet->size_measurement) {
                $pcs_in_set = $orderSet->size_measurement->no_of_pcs ?? '-';
            }
        }
        $summary['size_group'] = $orderSet ? $orderSet->size_set_name : '-';
        $summary['pcs_in_set'] = $pcs_in_set;

        // Calculation of slip range and total pcs from ACTUAL digitized data
        $all_sizes = [];
        foreach ($rolls as $r) {
            foreach ($r->fabricRollAssigningsDetail as $sd) {
                $all_sizes[] = $sd->size;
            }
        }
        foreach ($printings as $p) {
            foreach ($p->details as $rs) {
                $all_sizes[] = $rs->size;
            }
        }
        foreach ($stage_transactions as $st) {
            foreach ($st->details as $rs) {
                $all_sizes[] = $rs->size;
            }
        }
        $all_sizes = array_unique(array_filter($all_sizes));
        $actual_range = count($all_sizes) > 0 ? min($all_sizes) . '-' . max($all_sizes) : '-';

        $total_pcs = 0;
        foreach ($rolls as $r) {
            foreach ($r->fabricRollAssigningsDetail as $sd) {
                $total_pcs += $sd->quantity;
            }
        }
        foreach ($printings as $p) {
            $total_pcs += $p->details->sum('quantity');
        }
        foreach ($stage_transactions as $st) {
            $total_pcs += $st->details->sum('quantity');
        }

        $data = [
            'slip' => $slip,
            'summary' => $summary,
            'lots' => $lots,
            'rolls' => $rolls,
            'printings' => $printings,
            'stage_transactions' => $stage_transactions,
            'packing_details' => $packing_details,
            'unit' => StageMasterUnit::find($unitId),
            'actual_range' => $actual_range,
            'total_pcs' => $total_pcs
        ];

        return view('unit.view_slip', $data);
    }
    public function assignments(Request $request)
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
        $view = $request->get('view', 'open') === 'closed' ? 'closed' : 'open';
        if (!$canCloseTasks) {
            $view = 'open';
        }

        $lotNo = $request->get('lot_no');
        $orderNo = $request->get('order_no');

        if ($isCutting) {
            $type = 'cutting';

            $query = \App\Models\OrderCuttingStage::where('to_assign_id', $unitId)
                ->with([
                    'productSet.orderMain.customer',
                    'productSet.fabric',
                    'productSet.colors',
                    'productSet.master_design_pattern'
                ])
                ->orderBy('created_at', 'desc');

            if ($lotNo) {
                $query->whereHas('productSet', function ($q) use ($lotNo) {
                    $q->where('design_number', 'like', '%' . $lotNo . '%');
                });
            }

            if ($customerSearch) {
                $query->whereHas('productSet.orderMain', function ($q) use ($customerSearch) {
                    $q->where('sku', 'like', '%' . $orderNo . '%');
                });
            }

            if ($view === 'closed') {
                $assignments = $query->where('is_closed_for_unit', 1)->get();
            } else {
                $assignments = $query->where(function ($q) {
                    $q->whereNull('is_closed_for_unit')
                        ->orWhere('is_closed_for_unit', 0);
                })->get();
            }
        } else {
            $type = 'other';
            // Other Stages see Slips sent to them (ProductionSlipDigitization)
            // Filter by sub_stage_id_to = current unit's ID in Transactions

            $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);

            $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);

            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)
                ->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);

            if ($lotNo) {
                $ass1Query->where('lot_no', 'like', '%' . $lotNo . '%');
                $ass2Query->where('lot_no', 'like', '%' . $lotNo . '%');
                $ass3Query->where('lot_no', 'like', '%' . $lotNo . '%');
            }

            if ($customerSearch) {
                // If they need to search by orderNo, the transactions might have `sku` or we need to join.
                // Assuming $transaction models have an 'sku' field as seen in the fillable properties, 
                // or we join the orderProduct/orderMain. Let's try `sku` first based on common pattern.
                $ass1Query->where('sku', 'like', '%' . $orderNo . '%')
                    ->orWhereHas('orderProduct.orderMain', function ($q) use ($customerSearch) {
                        $q->where('sku', 'like', '%' . $orderNo . '%');
                    });

                $ass2Query->where('sku', 'like', '%' . $orderNo . '%')
                    ->orWhereHas('orderProduct.orderMain', function ($q) use ($customerSearch) {
                        $q->where('sku', 'like', '%' . $orderNo . '%');
                    });

                $ass3Query->where('sku', 'like', '%' . $orderNo . '%')
                    ->orWhereHas('orderProduct.orderMain', function ($q) use ($customerSearch) {
                        $q->where('sku', 'like', '%' . $orderNo . '%');
                    });
            }

            if ($isPacking && $canCloseTasks) {
                // Packing unit: supports open/closed state like cutting
                if ($view === 'closed') {
                    $ass1Query->where('is_closed_for_unit', 1);
                    $ass2Query->where('is_closed_for_unit', 1);
                    $ass3Query->where('is_closed_for_unit', 1);
                } else {
                    $ass1Query->where(function ($q) {
                        $q->whereNull('is_closed_for_unit')
                            ->orWhere('is_closed_for_unit', 0);
                    });
                    $ass2Query->where(function ($q) {
                        $q->whereNull('is_closed_for_unit')
                            ->orWhere('is_closed_for_unit', 0);
                    });
                    $ass3Query->where(function ($q) {
                        $q->whereNull('is_closed_for_unit')
                            ->orWhere('is_closed_for_unit', 0);
                    });
                }
            } else {
                // Other stages (printing, stitching, etc.) can only see open work; no close option
                $ass1Query->where('remaining_quantity', '>', 0)->whereNull('image');
                $ass2Query->where('remaining_quantity', '>', 0)->whereNull('image');
                $ass3Query->where('remaining_quantity', '>', 0)->whereNull('image');
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
                return \App\Models\OrderCuttingStage::where('id', $id)
                    ->where('to_assign_id', $unitId)
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

            case 'production':
                return \App\Models\ProductionSlipDigitization::where('id', $id)
                    ->where('stage_master_unit_id', $unitId)
                    ->first();

            case 'fabric':
                return \App\Models\FabricRollAssigning::where('id', $id)
                    ->where('stage_master_unit_id', $unitId)
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
            $stageAssignment = \App\Models\OrderCuttingStage::with([
                'productSet.stage_master_unit.masterFabricWarehouse',
                'productSet.fabric',
                'productSet.master_design_pattern',
                'productSet.orderMain.customer',
                'productSet.colors',
                'productSet.size_measurement',
                'productSet.master_product_fitting',
                'cutting_master.masterFabricWarehouse',
            ])->findOrFail($id);

            $data = $stageAssignment->productSet;
            $orderProductSet = $data;

            // Simplified Size Set Info
            $sizes = [];
            if (!empty($data->size_measurement?->size_group)) {
                $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
            } elseif (!empty($data->set_size)) {
                $sizes = [$data->set_size];
            }

            $sizeSetRange = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
            $totalPcsInSet = $data->size_measurement->no_of_pcs ?? count($sizes);

            $header = [
                'id' => $stageAssignment->id,
                'order_no' => $data->orderMain->sku ?? '-',
                'date' => $data->created_at->format('d-m-Y'),
                'customer' => $data->orderMain->customer->name ?? '-',
                'design_no' => $data->design_number ?? '-',
                'fabric' => $data->fabric->name ?? ($stageAssignment->fabric->name ?? '-'),
                'color' => $data->colors->name ?? '-',
                'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'),
                'fitting' => $data->master_product_fitting?->name ?? ($stageAssignment->master_fitting?->name ?? '-'),
                'warehouse' => $unit->masterFabricWarehouse->cutting_master_name ?? '-',
                'unit_name' => $unit->name ?? '-',
                'remark' => $stageAssignment->remarks ?? $data->remark ?? '-',
                'belt' => $stageAssignment->belt ?? '-',
                'total_pcs' => $stageAssignment->quantity ?? 0,
                'lot_no' => 'Pending',
                'size_set' => $sizeSetRange,
                'pcs_in_set' => $totalPcsInSet,
            ];

            // Size breakdown for the table
            $totalInRatio = count($sizes);
            $sizeCounts = array_count_values($sizes);
            foreach ($sizeCounts as $size => $count) {
                $sizeData[] = [
                    'size' => $size,
                    'color' => $data->colors->name,
                    'pcs' => $totalInRatio > 0 ? ($count * $stageAssignment->quantity) / $totalInRatio : 0,
                ];
            }
        } else {
            switch ($type) {
                case 'stage':
                    $transaction = \App\Models\OrderStageTransaction::where('id', $id)
                        ->where('sub_stage_id_to', $unitId)
                        ->with([
                            'from_stage',
                            'productionSlipDigitization',
                            'getFromUnitMaster',
                            'orderProduct.orderMain.customer',
                            'orderProduct.orderProductSet.fabric',
                            'orderProduct.orderProductSet.colors',
                            'orderProduct.orderProductSet.master_design_pattern',
                            'orderProduct.orderProductSet.master_product_fitting',
                            'orderProduct.orderProductSet.order_cutting_stage.fabric',
                            'orderProduct.orderProductSet.order_cutting_stage.pattern',
                            'orderProduct.orderProductSet.order_cutting_stage.master_fitting'
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
                            'orderProduct.orderProductSet.master_product_fitting',
                            'orderProduct.orderProductSet.order_cutting_stage.fabric',
                            'orderProduct.orderProductSet.order_cutting_stage.pattern',
                            'orderProduct.orderProductSet.order_cutting_stage.master_fitting'
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
                            'orderProduct.orderProductSet.master_product_fitting',
                            'orderProduct.orderProductSet.order_cutting_stage.fabric',
                            'orderProduct.orderProductSet.order_cutting_stage.pattern',
                            'orderProduct.orderProductSet.order_cutting_stage.master_fitting'
                        ])
                        ->firstOrFail();
                    break;
                case 'production':
                    // If it's a direct slip view from assignments
                    return $this->viewSlip('production', $id);
                case 'fabric':
                    return $this->viewSlip('fabric', $id);
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

            $sizes = [];
            if ($orderProductSet) {
                if (!empty($orderProductSet->size_measurement?->size_group)) {
                    $sizes = array_map('trim', explode(',', $orderProductSet->size_measurement->size_group));
                } elseif (!empty($orderProductSet->set_size)) {
                    $sizes = [$orderProductSet->set_size];
                }
            }

            $isRework = ($transaction && isset($transaction->type) && $transaction->type === 'rework');

            $sizeSetRange = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
            $totalPcsInSet = $orderProductSet->size_measurement->no_of_pcs ?? count($sizes);

            $header = [
                'id' => $id,
                'order_no' => $hOrderMain->sku ?? '-',
                'date' => $transaction->created_at->format('d-m-Y'),
                'customer' => $hOrderMain->customer->name ?? '-',
                'design_no' => $orderProductSet->design_number ?? '-',
                'fabric' => $orderProductSet->fabric->name ?? ($orderProductSet->order_cutting_stage->fabric->name ?? '-'),
                'color' => $orderProductSet->colors->name ?? '-',
                'pattern' => $orderProductSet->master_design_pattern->name ?? ($orderProductSet->order_cutting_stage->pattern->name ?? '-'),
                'fitting' => $orderProductSet->master_product_fitting?->name ?? ($orderProductSet->order_cutting_stage->master_fitting?->name ?? '-'),
                'lot_no' => $transaction->lot_no,
                'from_stage' => $transaction->from_stage->name ?? '-',
                'sent_by' => $transaction->getFromUnitMaster->name ?? '-',
                'total_pcs' => $transaction->remaining_quantity,
                'remark' => $transaction->remarks ?? '-',
                'size_set' => $sizeSetRange,
                'pcs_in_set' => $totalPcsInSet,
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

            // --- RE-CALCULATE TOTALS BASED ON DETAILED Breakdown (if available) ---
            if (!empty($sizeData)) {
                $totalFromBreakdown = array_sum(array_column($sizeData, 'pcs'));
                $header['total_pcs'] = $totalFromBreakdown;
            } else {
                // If no breakdown available, try to use transaction quantity
                if ($header['total_pcs'] == 0 && isset($transaction->quantity)) {
                    $header['total_pcs'] = $transaction->quantity;
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
            'isRework' => $isRework ?? false,
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

        $general_setting = GeneralSettings::where('status', 1)->first();

        $data = [
            'slip' => $slip,
            'general_setting' => $general_setting,
            'lot' => null,
            'lots' => collect(),
            'rolls' => collect(),
            'printing' => null,
            'printings' => collect(),
            'printing_sizes' => collect(),
            'stage_transactions' => collect(),
            'packing_details' => collect(),
            'size_set' => '-',
            'pcs_in_set' => 0,
        ];

        switch ($slip->save_type) {
            case 1:
                $lots = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)
                    ->with([
                        'orderMain',
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.master_design_pattern',
                        'orderProductSet.master_product_fitting',
                    ])
                    ->get();

                if ($lots->isNotEmpty()) {
                    $lot = $lots->first();
                    $rolls = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
                        ->with([
                            'fabricRollAssigningsDetail',
                            'stageMasterUnit.masterFabricWarehouse'
                        ])
                        ->get();

                    $data['lot'] = $lot;
                    $data['lots'] = $lots;
                    $data['rolls'] = $rolls;
                }
                break;

            case 2:
                $printings = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
                    ->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])
                    ->get();

                if ($printings->isNotEmpty()) {
                    $printing = $printings->first();
                    $printingSizes = \App\Models\OrderPrintingStageTransactionDetail::where(
                        'order_printing_stage_transaction_id',
                        $printing->id
                    )->get();

                    $data['printing'] = $printing;
                    $data['printings'] = $printings;
                    $data['printing_sizes'] = $printingSizes;
                }
                break;

            case 3:
                if ($slip->from_stage_id == 1) {
                    $stage_transactions = \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)
                        ->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])
                        ->get();

                    if ($stage_transactions->isNotEmpty()) {
                        $stageTransaction = $stage_transactions->first();
                        $stageSizes = \App\Models\OrderPrintingToStichingTransactionDetail::where(
                            'order_printing_to_stiching_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_transactions'] = $stage_transactions;
                        $data['stage_sizes'] = $stageSizes;
                    }
                } else {
                    $stage_transactions = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
                        ->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])
                        ->get();

                    if ($stage_transactions->isNotEmpty()) {
                        $stageTransaction = $stage_transactions->first();
                        $stageSizes = \App\Models\OrderStageTransactionDetail::where(
                            'order_stage_transaction_id',
                            $stageTransaction->id
                        )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_transactions'] = $stage_transactions;
                        $data['stage_sizes'] = $stageSizes;
                    }
                }

                if ($slip->from_stage_id == 11) {
                    $packingDetails = \App\Models\PackingMain::where('slip_id', $slip->id)
                        ->with(['cartons.boxes.items.detail', 'cartons.items.detail'])
                        ->get();
                    $data['packing_details'] = $packingDetails;
                }
                break;
        }

        // Consolidate Order Details
        $orderSet = $slip->orderProductSet;
        if (!$orderSet && $data['lots']->isNotEmpty()) {
            $orderSet = $data['lots']->first()->orderProductSet;
        }
        if (!$orderSet && $data['printings']->isNotEmpty()) {
            $orderSet = $data['printings']->first()->orderProduct?->orderProductSet;
        }
        if (!$orderSet && $data['stage_transactions']->isNotEmpty()) {
            $orderSet = $data['stage_transactions']->first()->orderProduct?->orderProductSet;
        }

        // --- FALLBACK: Try to find OrderProductSet via lot_no if still null ---
        if (!$orderSet) {
            $fallbackLot = null;
            if ($data['lots']->isNotEmpty())
                $fallbackLot = $data['lots']->first()->lot_no;
            elseif ($data['printings']->isNotEmpty())
                $fallbackLot = $data['printings']->first()->lot_no;
            elseif ($data['stage_transactions']->isNotEmpty())
                $fallbackLot = $data['stage_transactions']->first()->lot_no;

            if ($fallbackLot) {
                $orderLot = \App\Models\OrderLot::where('lot_no', $fallbackLot)->with('orderProductSet')->first();
                if ($orderLot && $orderLot->orderProductSet) {
                    $orderSet = $orderLot->orderProductSet;
                }
            }
        }
        $data['orderProductSet'] = $orderSet;

        // Simplified Size Set Info
        $sizes = [];
        if ($orderSet) {
            $orderSet->loadMissing('size_measurement');
            if (!empty($orderSet->size_measurement?->size_group)) {
                $sizes = array_map('trim', explode(',', $orderSet->size_measurement->size_group));
            } elseif (!empty($orderSet->set_size)) {
                $sizes = [$orderSet->set_size];
            }
        }

        $data['size_set'] = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';

        // --- Calculate Total Pieces by summing all sessions ---
        $grandTotal = 0;
        foreach ($data['lots'] as $l)
            $grandTotal += ($l->quantity ?? 0);
        foreach ($data['printings'] as $p)
            $grandTotal += ($p->quantity ?? 0);
        foreach ($data['stage_transactions'] as $st)
            $grandTotal += ($st->quantity ?? 0);

        // If summarized total is 0, check if we have sizes in data
        if ($grandTotal == 0) {
            // Fallback to pcs_in_set logic if needed, but grandTotal from sessions is usually what's wanted for summary
            $data['pcs_in_set'] = ($orderSet && $orderSet->size_measurement) ? ($orderSet->size_measurement->no_of_pcs ?? count($sizes)) : count($sizes);
        } else {
            $data['pcs_in_set'] = $grandTotal;
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

        $stageAssignment = \App\Models\OrderCuttingStage::with([
            'productSet.size_measurement',
            'productSet.colors',
            'productSet.fabric',
            'productSet.master_design_pattern',
            'productSet.orderMain.customer',
            'master_fitting',
            'fabric',
            'pattern',
            'cutting_master.masterFabricWarehouse'
        ])->findOrFail($id);

        $data = $stageAssignment->productSet;

        $cmpoHeader = [
            'cmpo_id' => $stageAssignment->id,
            'date' => $stageAssignment->created_at->format('d-m-Y'),
            'order_no' => $data->orderMain->sku ?? '-',
            'customer' => $data->orderMain->customer->name ?? '-',
            'design_no' => $data->design_number ?? '-',
            'color' => $data->colors->name ?? '-',
            'fabric' => $data->fabric->name ?? ($stageAssignment->fabric->name ?? '-'),
            'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'),
            'fitting' => $stageAssignment->master_fitting->name ?? '-',
            'warehouse_name' => $stageAssignment->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-',
            'cuttingMaster' => $stageAssignment->cutting_master->name ?? '-',
            'remark' => $stageAssignment->remarks ?? '-',
            'belt' => $stageAssignment->belt ?? '-',
            'total_pcs' => $stageAssignment->quantity ?? 0,
        ];

        // Size data
        $sizes = [];
        if (!empty($data->size_measurement?->size_group)) {
            $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
        } elseif (!empty($data->set_size)) {
            $sizes = [$data->set_size];
        }

        $sizeSetRange = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
        $totalPcsInSet = $data->size_measurement->no_of_pcs ?? count($sizes);
        $cmpoHeader['size_set'] = $sizeSetRange;
        $cmpoHeader['pcs_in_set'] = $totalPcsInSet;

        $sizeData = [];
        $total_pcs = $cmpoHeader['total_pcs'];
        $totalInRatio = count($sizes);
        $sizeCounts = array_count_values($sizes);
        foreach ($sizeCounts as $size => $count) {
            $sizeData[$size] = [
                'design_no' => $data->design_number,
                'color' => $data->colors->name,
                'size' => $size,
                'pcs' => $totalInRatio > 0 ? ($count * $total_pcs) / $totalInRatio : 0,
            ];
        }

        $pdf = Pdf::loadView('admin.product_order.cmpo_slip', [
            'header' => $cmpoHeader,
            'sizeData' => $sizeData,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('CMPO-' . $id . '.pdf');
    }
}
