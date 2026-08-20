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
            $authData = [
                'id' => $unit->id,
                'employee_id' => $unit->employee_id,
                'name' => $unit->name,
                'stage_id' => $unit->master_stage_id
            ];
            
            session(['unit_auth' => $authData]);
            
            // Set remember me cookie for 1 year (365 days * 24 hours * 60 mins)
            \Illuminate\Support\Facades\Cookie::queue('unit_remember', encrypt(json_encode($authData)), 525600);
            
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

            // Update Transaction Image if IDs provided
            if ($request->transaction_ids) {
                // If transaction_ids is provided as JSON array string
                $txArray = is_string($request->transaction_ids) ? json_decode($request->transaction_ids, true) : $request->transaction_ids;
                
                if (is_array($txArray) && count($txArray) > 0) {
                    foreach ($txArray as $txInfo) {
                        $txId = $txInfo['id'] ?? null;
                        $txType = $txInfo['type'] ?? null;
                        
                        if (!$txId || !$txType) continue;

                        $tx = null;
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
                }
            } elseif ($request->transaction_id && $request->transaction_type) {
                // Legacy fallback for single transaction
                $txId = $request->transaction_id;
                $txType = $request->transaction_type;
                
                $tx = null;
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
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('unit_remember'));
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
        $page = $request->get('page', 1);
        $perPage = 15;

        $lotNo = $request->input('lot_no');
        $customerSearch = $request->input('customer');
        $status = $request->input('status');

        $unit = StageMasterUnit::find($unitId);

        if ($viewType === 'tasks') {
            $isCutting = $unit->master_stage_id == self::STAGE_CUTTING;
            $isPacking = $unit->master_stage_id == self::STAGE_PACKING;
            $activityType = $request->get('activity_type');
            
            $rawItems = collect();

            // 1. RECEIVED TASKS (Where unit is the destination)
            if (!$activityType || $activityType === 'received') {
                if ($isCutting) {
                    $qAssign = \App\Models\OrderCuttingStage::where('to_assign_id', $unitId)->with(['productSet.orderMain']);
                    if ($lotNo) $qAssign->whereHas('productSet', function ($qs) use ($lotNo) { $qs->where('design_number', 'like', '%' . $lotNo . '%'); });
                    if ($customerSearch) $qAssign->whereHas('productSet.orderMain', function ($qs) use ($customerSearch) { $qs->where('sku', 'like', '%' . $customerSearch . '%'); });
                    
                    $qAssign->get()->each(function($item) use ($rawItems) {
                        $item->_event_type = 'received';
                        $item->_type = 'cutting';
                        $item->_source_type = 'assign';
                        $rawItems->push($item);
                    });
                }

                $q1 = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);
                $q2 = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);
                $q3 = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);

                foreach ([$q1, $q2, $q3] as $idx => $q) {
                    if ($lotNo) $q->where('lot_no', 'like', '%' . $lotNo . '%');
                    if ($customerSearch) {
                        $q->where(function ($sq) use ($customerSearch) {
                            $sq->where('name', 'like', '%' . $customerSearch . '%')
                               ->orWhereHas('orderProduct.orderMain', function ($ssq) use ($customerSearch) { $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                        });
                    }

                    $txTypes = ['stage', 'printing', 'printing_to_stitching'];
                    $q->get()->each(function($item) use ($rawItems, $txTypes, $idx) {
                        $item->_event_type = 'received';
                        $item->_type = $txTypes[$idx];
                        $item->_source_type = 'tx';
                        $rawItems->push($item);
                    });
                }
            }

            // 2. SENT TASKS (Where unit is the source)
            if (!$activityType || $activityType === 'sent') {
                $qRolls = \App\Models\FabricRollAssigning::where('stage_master_unit_id', $unitId)->with(['orderProductSet.orderMain', 'fabricRollAssigningsDetail']);
                if ($lotNo) $qRolls->where('lot_no', 'like', '%' . $lotNo . '%');
                if ($customerSearch) {
                    $qRolls->where(function ($sq) use ($customerSearch) {
                        $sq->where('order_no', 'like', '%' . $customerSearch . '%')
                           ->orWhereHas('orderProductSet.orderMain', function ($ssq) use ($customerSearch) { $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                    });
                }

                $qRolls->get()->each(function($item) use ($rawItems) {
                    $item->_event_type = 'sent';
                    $item->_type = 'fabric';
                    $item->_source_type = 'roll';
                    $rawItems->push($item);
                });

                $s1 = \App\Models\OrderStageTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);
                $s2 = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);
                $s3 = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id', $unitId)->with(['to_stage', 'orderProduct.orderMain', 'orderProduct.orderProductSet.orderMain']);

                foreach ([$s1, $s2, $s3] as $idx => $q) {
                    if ($lotNo) $q->where('lot_no', 'like', '%' . $lotNo . '%');
                    if ($customerSearch) {
                        $q->where(function ($sq) use ($customerSearch) {
                            $sq->where('name', 'like', '%' . $customerSearch . '%')
                               ->orWhereHas('orderProduct.orderMain', function ($ssq) use ($customerSearch) { $ssq->where('sku', 'like', '%' . $customerSearch . '%'); });
                        });
                    }

                    $txTypes = ['stage', 'printing', 'printing_to_stitching'];
                    $q->get()->each(function($item) use ($rawItems, $txTypes, $idx) {
                        $item->_event_type = 'sent';
                        $item->_type = $txTypes[$idx];
                        $item->_source_type = 'sent_tx';
                        $rawItems->push($item);
                    });
                }
            }

            // Sort and Paginate Raw Items
            $rawItems = $rawItems->sortByDesc('created_at');
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $rawItems->forPage($page, $perPage),
                $rawItems->count(),
                $perPage,
                $page,
                ['path' => route('unit.history')]
            );

            $tasks = collect();
            foreach ($paginator->items() as $item) {
                // Map the items
                if ($item->_source_type === 'assign') {
                    $timing = \App\Models\OrderLotStageTiming::where('lot_no', $item->productSet->design_number)->where('master_stage_id', $unit->master_stage_id)->first();
                    $tasks->push([
                        'id' => $item->id,
                        'slip_id' => $item->production_slip_digitization_id ?? null,
                        'event_type' => 'received',
                        'type' => 'cutting',
                        'lot_no' => $item->lot_no ?? '-',
                        'design_no' => $item->productSet->design_number ?? '-',
                        'customer' => $item->productSet->orderMain->customer->name ?? '-',
                        'size_sets' => $item->productSet->size_set_name ?? '-',
                        'from_stage' => 'Admin Assignment',
                        'quantity' => $item->quantity,
                        'created_at' => $item->created_at,
                        'start_date' => $timing->start_date ?? $item->start_date ?? null,
                        'end_date' => $timing->end_date ?? $item->end_date ?? null,
                        'complete_date' => $timing->complete_date ?? $item->complete_date ?? null,
                        'status' => ($item->status == 1 || $item->image) ? 1 : 0
                    ]);
                } elseif ($item->_source_type === 'tx' || $item->_source_type === 'sent_tx') {
                    $sku = $item->orderProduct->orderMain->sku ?? $item->sku ?? '-';
                    if ($sku === '-' || empty($sku)) $sku = $item->orderProduct?->orderProductSet?->orderMain?->sku ?? '-';
                    if ($sku === '-' && !empty($item->lot_no)) {
                        $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderMain')->first();
                        $sku = $lRef->orderMain->sku ?? $lRef->order_no ?? '-';
                    }

                    $lotNoForTiming = $item->lot_no ?? $item->orderProduct?->orderProductSet?->design_number;
                    $timing = \App\Models\OrderLotStageTiming::where('lot_no', $lotNoForTiming)->where('master_stage_id', $unit->master_stage_id)->first();

                    $designNo = $item->orderProduct?->orderProductSet?->design_number ?? '-';
                    $customer = $item->orderProduct?->orderMain?->customer?->name ?? '-';
                    $sizeSets = $item->orderProduct?->orderProductSet?->size_set_name ?? '-';

                    if (($designNo === '-' || $customer === '-') && !empty($item->lot_no)) {
                        $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with(['orderProductSet', 'orderMain.customer'])->first();
                        if ($lRef) {
                            if ($designNo === '-') $designNo = $lRef->orderProductSet->design_number ?? '-';
                            if ($customer === '-') $customer = $lRef->orderMain?->customer?->name ?? '-';
                            if ($sizeSets === '-') $sizeSets = $lRef->orderProductSet?->size_set_name ?? '-';
                        }
                    }

                    $stageName = $item->_source_type === 'tx' ? ($item->from_stage->name ?? '-') : ($item->to_stage->name ?? 'Next Stage');

                    $tasks->push([
                        'id' => $item->id,
                        'slip_id' => $item->production_slip_digitization_id ?? null,
                        'event_type' => $item->_event_type,
                        'type' => $item->_type,
                        'lot_no' => $item->lot_no ?? '-',
                        'design_no' => $designNo,
                        'customer' => $customer,
                        'size_sets' => $sizeSets,
                        'from_stage' => $stageName,
                        'quantity' => $item->quantity,
                        'created_at' => $item->created_at,
                        'start_date' => $timing->start_date ?? $item->start_date ?? null,
                        'end_date' => $timing->end_date ?? $item->end_date ?? null,
                        'complete_date' => $timing->complete_date ?? $item->complete_date ?? null,
                        'status' => ($item->image || $item->_event_type === 'sent') ? 1 : 0
                    ]);
                } elseif ($item->_source_type === 'roll') {
                    $customerName = $item->orderProductSet?->orderMain?->customer?->name ?? '-';
                    $tasks->push([
                        'id' => $item->id,
                        'slip_id' => $item->production_slip_digitization_id ?? $item->id,
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
            }

            if ($status === 'done') $tasks = $tasks->where('status', 1);
            elseif ($status === 'pending') $tasks = $tasks->where('status', 0);

            if ($request->ajax()) {
                return view('unit.partials.history_tasks_list', compact('tasks', 'unit'))->render();
            }

            return view('unit.history', [
                'tasks' => $tasks,
                'paginator' => $paginator,
                'unit' => $unit,
                'viewType' => 'tasks'
            ]);
        }

        // --- Original Slips Logic ---
        $fabricQuery = FabricRollAssigning::where('stage_master_unit_id', $unitId)
            ->whereNotNull('slip_file')
            ->with(['fabricRollAssigningsDetail', 'orderProductSet.orderMain.customer']);

        if ($lotNo) $fabricQuery->where('lot_no', 'like', '%' . $lotNo . '%');
        if ($customerSearch) $fabricQuery->where('order_no', 'like', '%' . $customerSearch . '%');
        if ($status !== null && $status !== '') $fabricQuery->where('status', $status === 'done' ? 1 : 0);

        $fabricSlips = $fabricQuery->get()->map(function($item) { $item->_source_type = 'fabric'; return $item; });

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
                'orderGodamStageTransaction.orderProduct.orderProductSet.size_measurement',
                'orderGodamStageTransaction.orderProduct.orderProductSet.orderMain',
                'orderGodamStageTransaction.godamDetails',
                'fabricRollAssignings.fabricRollAssigningsDetail',
                'parts',
                'orderProductSet.orderMain.customer'
            ]);

        if ($lotNo) {
            $productionQuery->where(function ($q) use ($lotNo) {
                $q->where('lot_no', 'like', '%' . $lotNo . '%')
                  ->orWhereHas('orderLots', function ($sq) use ($lotNo) { $sq->where('lot_no', 'like', '%' . $lotNo . '%'); })
                  ->orWhereHas('orderPrintingStageTransaction', function ($pq) use ($lotNo) { $pq->where('lot_no', 'like', '%' . $lotNo . '%'); })
                  ->orWhereHas('orderStageTransaction', function ($tq) use ($lotNo) { $tq->where('lot_no', 'like', '%' . $lotNo . '%'); });
            });
        }

        if ($customerSearch) {
            $productionQuery->where(function ($q) use ($customerSearch) {
                $q->whereHas('orderProductSet.orderMain', function ($sq) use ($customerSearch) { $sq->where('name', 'like', '%' . $customerSearch . '%'); })
                  ->orWhereHas('orderLots.orderMain', function ($sq) use ($customerSearch) { $sq->where('name', 'like', '%' . $customerSearch . '%'); });
            });
        }

        if ($status !== null && $status !== '') $productionQuery->where('status', $status === 'done' ? 1 : 0);

        $productionSlips = $productionQuery->get()->map(function($item) { $item->_source_type = 'production'; return $item; });

        $mergedSlips = $fabricSlips->concat($productionSlips)->sortByDesc('created_at');
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $mergedSlips->forPage($page, $perPage),
            $mergedSlips->count(),
            $perPage,
            $page,
            ['path' => route('unit.history')]
        );

        $allSlips = collect();

        foreach ($paginator->items() as $slip) {
            if ($slip->_source_type === 'fabric') {
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
            } else {
                $sessions = collect();
                // 1. Cutting
                foreach ($slip->orderLots as $lot) {
                    $rolls = $slip->fabricRollAssignings->where('order_lot_id', $lot->id);
                    $totalPieces = 0;
                    $sizes = collect();
                    foreach ($rolls as $roll) { $totalPieces += $roll->fabricRollAssigningsDetail->sum('quantity'); $sizes = $sizes->merge($roll->fabricRollAssigningsDetail->pluck('size')); }
                    $sessions->push([ 'type' => 'Cutting', 'lot_no' => $lot->lot_no, 'pieces' => $totalPieces, 'size_sets' => $lot->orderProductSet?->size_set_name ?? '-', 'design_no' => $lot->orderProductSet->design_number ?? '-', 'customer' => $lot->orderMain?->customer?->name ?? '-' ]);
                }
                // 2. Printing
                foreach ($slip->orderPrintingStageTransaction as $opt) {
                    $sessions->push([ 'type' => 'Printing', 'lot_no' => $opt->lot_no, 'pieces' => $opt->details->sum('quantity'), 'size_sets' => $opt->orderProduct?->orderProductSet?->size_set_name ?? '-', 'design_no' => $opt->orderProduct?->orderProductSet?->design_number ?? '-', 'customer' => $opt->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-' ]);
                }
                // 3. Printing to Stitching
                foreach ($slip->orderPrintingToStichingTransaction as $optst) {
                    $sessions->push([ 'type' => 'Printing to Stitching', 'lot_no' => $optst->lot_no, 'pieces' => $optst->details->sum('quantity'), 'size_sets' => $optst->orderProduct?->orderProductSet?->size_set_name ?? '-', 'design_no' => $optst->orderProduct?->orderProductSet?->design_number ?? '-', 'customer' => $optst->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-' ]);
                }
                // 4. Transfer
                foreach ($slip->orderStageTransaction as $ost) {
                    $sessions->push([ 'type' => 'Transfer', 'lot_no' => $ost->lot_no, 'pieces' => $ost->details->sum('quantity'), 'size_sets' => $ost->orderProduct?->orderProductSet?->size_set_name ?? '-', 'design_no' => $ost->orderProduct?->orderProductSet?->design_number ?? '-', 'customer' => $ost->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-' ]);
                }
                // 4.5 Godam
                foreach ($slip->orderGodamStageTransaction as $ogst) {
                    $sessions->push([ 'type' => 'Godam Transfer', 'lot_no' => $ogst->lot_no, 'pieces' => $ogst->godamDetails->sum('quantity'), 'size_sets' => $ogst->orderProduct?->orderProductSet?->size_set_name ?? '-', 'design_no' => $ogst->orderProduct?->orderProductSet?->design_number ?? '-', 'customer' => $ogst->orderProduct?->orderProductSet?->orderMain?->customer?->name ?? '-' ]);
                }
                // 5. Parts
                foreach ($slip->parts as $part) {
                    $partDetails = \App\Models\ProductionDigitizationSetsDetails::where('production_slip_digitization_parts_id', $part->id)->get();
                    $sizes = collect(); $pieces = 0;
                    if ($partDetails->isNotEmpty()) { $pieces = $partDetails->sum('qauntity'); $sizes = $partDetails->pluck('size'); } else { $pieces = $part->single_quantity ?? 0; if ($part->single_size) $sizes->push($part->single_size); }
                    $sessions->push([ 'type' => 'Part', 'lot_no' => $part->lot_no, 'pieces' => $pieces, 'size_sets' => \App\Models\MasterSizeMeasurement::find($part->set_size)?->name ?? '-', 'design_no' => $part->design_number ?? '-', 'customer' => '-' ]);
                }

                $allLots = $sessions->pluck('lot_no')->unique()->filter()->values();
                if ($allLots->isEmpty() && $slip->lot_no) $allLots->push($slip->lot_no);
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
                    'pieces' => $sessions->sum('pieces'),
                    'size_sets' => $sessions->pluck('size_sets')->unique()->filter()->values()->join(', ') ?: '-',
                    'sessions' => $sessions,
                    'stage' => $slip->fromStage->name ?? '-',
                ]);
            }
        }

        if ($request->ajax()) {
            return view('unit.partials.history_slips_list', ['slips' => $allSlips])->render();
        }

        return view('unit.history', [
            'slips' => $allSlips,
            'paginator' => $paginator,
            'unit' => StageMasterUnit::find($unitId),
            'viewType' => 'slips'
        ]);
    }

    public function deleteSlip(Request $request, $type, $id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];

        if ($type === 'fabric') {
            $slip = \App\Models\FabricRollAssigning::where('id', $id)->where('stage_master_unit_id', $unitId)->first();
            if ($slip && $slip->status == 0) {
                if ($slip->slip_file && \Illuminate\Support\Facades\File::exists(public_path('assets/production_slips/' . $slip->slip_file))) {
                    \Illuminate\Support\Facades\File::delete(public_path('assets/production_slips/' . $slip->slip_file));
                }
                $slip->delete();
                return redirect()->back()->withSuccess('Slip deleted successfully.');
            }
        } elseif ($type === 'production') {
            $slip = \App\Models\ProductionSlipDigitization::where('id', $id)->where('stage_master_unit_id', $unitId)->first();
            if ($slip && $slip->status == 0) {
                // Check if any sessions exist
                $hasSessions = $slip->orderLots()->exists() ||
                               $slip->orderPrintingStageTransaction()->exists() ||
                               $slip->orderStageTransaction()->exists() ||
                               $slip->orderPrintingToStichingTransaction()->exists() ||
                               $slip->orderGodamStageTransaction()->exists() ||
                               $slip->parts()->exists();
                
                if (!$hasSessions) {
                    if ($slip->slip_file && \Illuminate\Support\Facades\File::exists(public_path('assets/production_slips/' . $slip->slip_file))) {
                        \Illuminate\Support\Facades\File::delete(public_path('assets/production_slips/' . $slip->slip_file));
                    }
                    $slip->delete();
                    return redirect()->back()->withSuccess('Slip deleted successfully.');
                } else {
                    return redirect()->back()->withError('Cannot delete slip because tasks have already been digitized for it.');
                }
            }
        }

        return redirect()->back()->withError('Slip not found or cannot be deleted.');
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
        $isCreator = $slip->stage_master_unit_id == $unitId;

        if (!$isCreator) {
            $isCreator = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists() ||
                \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists() ||
                \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $id)->where('sub_stage_id', $unitId)->exists();
        }

        $isReceiver = false;

        if (!$isCreator) {
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
        }

        if (!$isCreator && !$isReceiver) {
            if (request()->headers->get('referer') && str_contains(request()->headers->get('referer'), 'unit/history')) {
                // Allow view if redirected from history
            } else {
                abort(403, 'Unauthorized access to this slip.');
            }
        }

        // --- Fetch ALL digitized sessions linked to this slip ---

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

        $godam_tx = \App\Models\OrderGodamStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'godamDetails'
            ])->get();
            
        $godam_tx->each(function($tx) {
            $tx->details = $tx->godamDetails;
        });
        
        $stage_transactions = $stage_transactions->concat($godam_tx);

        $packing_details = null;
        if ($slip->from_stage_id == 11) {
            $packing_details = \App\Models\PackingMain::where('slip_id', $slip->id)
                ->with(['cartons.boxes.items.detail', 'cartons.items.detail'])
                ->first();
        }

        $consolidated = [
            'lot_nos' => collect(),
            'order_nos' => collect(),
            'design_nos' => collect(),
            'fabrics' => collect(),
            'colors' => collect(),
            'customers' => collect()
        ];

        foreach ($lots as $lot) {
            if ($lot->lot_no) $consolidated['lot_nos']->push($lot->lot_no);
            if ($lot->orderMain) $consolidated['order_nos']->push($lot->orderMain->sku);
            if ($lot->orderProductSet) {
                $ops = $lot->orderProductSet;
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric) $consolidated['fabrics']->push($ops->fabric_names);
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        foreach ($printings as $printing) {
            if ($printing->lot_no) $consolidated['lot_nos']->push($printing->lot_no);
            if ($printing->orderProduct?->orderProductSet) {
                $ops = $printing->orderProduct->orderProductSet;
                if ($ops->orderMain) $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric) $consolidated['fabrics']->push($ops->fabric_names);
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        foreach ($stage_transactions as $tx) {
            if ($tx->lot_no) $consolidated['lot_nos']->push($tx->lot_no);
            $ops = $tx->orderProduct?->orderProductSet;
            
            // Fallback to OrderLot if direct relationship is missing
            if (!$ops && !empty($tx->lot_no)) {
                $lRef = \App\Models\OrderLot::where('lot_no', $tx->lot_no)->with(['orderProductSet', 'orderMain.customer'])->first();
                if ($lRef) $ops = $lRef->orderProductSet;
            }

            if ($ops) {
                if ($ops->orderMain) $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric) $consolidated['fabrics']->push($ops->fabric_names);
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        $summary = [
            'lot_no' => $consolidated['lot_nos']->unique()->filter()->implode(', '),
            'order_no' => $consolidated['order_nos']->unique()->filter()->implode(', '),
            'customer' => $consolidated['customers']->unique()->filter()->implode(', '),
            'design' => $consolidated['design_nos']->unique()->filter()->implode(', ')
        ];

        $orderSet = $slip->orderProductSet;
        if (!$orderSet && $lots->isNotEmpty()) $orderSet = $lots->first()->orderProductSet;
        if (!$orderSet && $printings->isNotEmpty()) $orderSet = $printings->first()->orderProduct?->orderProductSet;
        if (!$orderSet && $stage_transactions->isNotEmpty()) {
            $orderSet = $stage_transactions->first()->orderProduct?->orderProductSet;
            if (!$orderSet && !empty($stage_transactions->first()->lot_no)) {
                $lRef = \App\Models\OrderLot::where('lot_no', $stage_transactions->first()->lot_no)->with('orderProductSet')->first();
                if ($lRef) $orderSet = $lRef->orderProductSet;
            }
        }

        $pcs_in_set = '-';
        if ($orderSet) {
            $orderSet->loadMissing('size_measurement');
            if ($orderSet->size_measurement) {
                $pcs_in_set = $orderSet->size_measurement->no_of_pcs ?? '-';
            }
        }
        $summary['size_group'] = $orderSet ? $orderSet->size_set_name : '-';
        $summary['pcs_in_set'] = $pcs_in_set;

        $all_sizes = [];
        foreach ($rolls as $r) { foreach ($r->fabricRollAssigningsDetail as $sd) { $all_sizes[] = $sd->size; } }
        foreach ($printings as $p) { foreach ($p->details as $rs) { $all_sizes[] = $rs->size; } }
        foreach ($stage_transactions as $st) { foreach ($st->details as $rs) { $all_sizes[] = $rs->size; } }
        $all_sizes = array_unique(array_filter($all_sizes));
        $actual_range = count($all_sizes) > 0 ? min($all_sizes) . '-' . max($all_sizes) : '-';

        $total_pcs = 0;
        foreach ($rolls as $r) { foreach ($r->fabricRollAssigningsDetail as $sd) { $total_pcs += $sd->quantity; } }
        foreach ($printings as $p) { $total_pcs += $p->details->sum('quantity'); }
        foreach ($stage_transactions as $st) { $total_pcs += $st->details->sum('quantity'); }

        return view('unit.view_slip', [
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
        ]);
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
        $isCutting = $unit->master_stage_id == self::STAGE_CUTTING;
        $isPacking = $unit->master_stage_id == self::STAGE_PACKING;
        $canCloseTasks = $isCutting || $isPacking;

        $view = $request->get('view', 'open') === 'closed' ? 'closed' : 'open';

        $lotNo = $request->get('lot_no');
        $orderNo = $request->get('order_no');
        $customerSearch = $orderNo; 

        if ($isCutting) {
            $type = 'cutting';
            $query = \App\Models\OrderCuttingStage::where('to_assign_id', $unitId)
                ->with(['productSet.orderMain.customer', 'productSet.fabric', 'productSet.colors', 'productSet.master_design_pattern'])
                ->orderBy('created_at', 'desc');

            if ($lotNo) $query->whereHas('productSet', function ($q) use ($lotNo) { $q->where('design_number', 'like', '%' . $lotNo . '%'); });
            if ($customerSearch) $query->whereHas('productSet.orderMain', function ($q) use ($customerSearch) { $q->where('sku', 'like', '%' . $orderNo . '%'); });

            $assignments = ($view === 'closed') ? $query->where('is_closed_for_unit', 1)->get() : $query->where(function ($q) { $q->whereNull('is_closed_for_unit')->orWhere('is_closed_for_unit', 0); })->get();
        } else {
            $type = 'other';
            $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);

            if ($lotNo) {
                $ass1Query->where('lot_no', 'like', '%' . $lotNo . '%');
                $ass2Query->where('lot_no', 'like', '%' . $lotNo . '%');
                $ass3Query->where('lot_no', 'like', '%' . $lotNo . '%');
            }

            if ($customerSearch) {
                $ass1Query->where(function($sq) use ($orderNo) { $sq->where('sku', 'like', '%' . $orderNo . '%')->orWhereHas('orderProduct.orderMain', function ($q) use ($orderNo) { $q->where('sku', 'like', '%' . $orderNo . '%'); }); });
                $ass2Query->where(function($sq) use ($orderNo) { $sq->where('sku', 'like', '%' . $orderNo . '%')->orWhereHas('orderProduct.orderMain', function ($q) use ($orderNo) { $q->where('sku', 'like', '%' . $orderNo . '%'); }); });
                $ass3Query->where(function($sq) use ($orderNo) { $sq->where('sku', 'like', '%' . $orderNo . '%')->orWhereHas('orderProduct.orderMain', function ($q) use ($orderNo) { $q->where('sku', 'like', '%' . $orderNo . '%'); }); });
            }

            if ($isPacking) {
                if ($view === 'closed') {
                    $ass1Query->where('is_closed_for_unit', 1); $ass2Query->where('is_closed_for_unit', 1); $ass3Query->where('is_closed_for_unit', 1);
                    $ass1 = $ass1Query->get()->map(function ($item) { $item->transaction_type = 'stage'; return $item; });
                    $ass2 = $ass2Query->get()->map(function ($item) { $item->transaction_type = 'printing'; return $item; });
                    $ass3 = $ass3Query->get()->map(function ($item) { $item->transaction_type = 'printing_to_stitching'; return $item; });
                } else {
                    $ass1Query->where(function ($q) { $q->whereNull('is_closed_for_unit')->orWhere('is_closed_for_unit', 0); });
                    $ass2Query->where(function ($q) { $q->whereNull('is_closed_for_unit')->orWhere('is_closed_for_unit', 0); });
                    $ass3Query->where(function ($q) { $q->whereNull('is_closed_for_unit')->orWhere('is_closed_for_unit', 0); });
                    $ass1 = $ass1Query->get()->map(function ($item) { $item->transaction_type = 'stage'; return $item; });
                    $ass2 = $ass2Query->get()->map(function ($item) { $item->transaction_type = 'printing'; return $item; });
                    $ass3 = $ass3Query->get()->map(function ($item) { $item->transaction_type = 'printing_to_stitching'; return $item; });
                }
            } else {
                // Non-cutting, non-packing stages (e.g., Washing, Stitching)
                if ($view === 'closed') {
                    $ass1Query->whereNotNull('image');
                    $ass2Query->whereNotNull('image');
                    $ass3Query->whereNotNull('image');
                    $ass1 = $ass1Query->get()->map(function ($item) { $item->transaction_type = 'stage'; return $item; });
                    $ass2 = $ass2Query->get()->map(function ($item) { $item->transaction_type = 'printing'; return $item; });
                    $ass3 = $ass3Query->get()->map(function ($item) { $item->transaction_type = 'printing_to_stitching'; return $item; });
                } else {
                    // Fetch pending transactions first
                    $pending1 = (clone $ass1Query)->where('remaining_quantity', '>', 0)->whereNull('image')->get();
                    $pending2 = (clone $ass2Query)->where('remaining_quantity', '>', 0)->whereNull('image')->get();
                    $pending3 = (clone $ass3Query)->where('remaining_quantity', '>', 0)->whereNull('image')->get();
                    
                    $slipIds = $pending1->pluck('production_slip_digitization_id')->merge($pending2->pluck('production_slip_digitization_id'))->merge($pending3->pluck('production_slip_digitization_id'))->unique()->filter();
                    
                    $directIds1 = $pending1->whereNull('production_slip_digitization_id')->pluck('id');
                    $directIds2 = $pending2->whereNull('production_slip_digitization_id')->pluck('id');
                    $directIds3 = $pending3->whereNull('production_slip_digitization_id')->pluck('id');
                    
                    if ($slipIds->isEmpty() && $directIds1->isEmpty()) {
                        $ass1Query->whereRaw('1 = 0');
                    } else {
                        $ass1Query->where(function($q) use ($slipIds, $directIds1) {
                            if ($slipIds->isNotEmpty()) $q->whereIn('production_slip_digitization_id', $slipIds);
                            if ($directIds1->isNotEmpty()) $q->orWhereIn('id', $directIds1);
                        });
                    }
                    
                    if ($slipIds->isEmpty() && $directIds2->isEmpty()) {
                        $ass2Query->whereRaw('1 = 0');
                    } else {
                        $ass2Query->where(function($q) use ($slipIds, $directIds2) {
                            if ($slipIds->isNotEmpty()) $q->whereIn('production_slip_digitization_id', $slipIds);
                            if ($directIds2->isNotEmpty()) $q->orWhereIn('id', $directIds2);
                        });
                    }
                    
                    if ($slipIds->isEmpty() && $directIds3->isEmpty()) {
                        $ass3Query->whereRaw('1 = 0');
                    } else {
                        $ass3Query->where(function($q) use ($slipIds, $directIds3) {
                            if ($slipIds->isNotEmpty()) $q->whereIn('production_slip_digitization_id', $slipIds);
                            if ($directIds3->isNotEmpty()) $q->orWhereIn('id', $directIds3);
                        });
                    }
                    
                    $ass1 = $ass1Query->get()->map(function ($item) { $item->transaction_type = 'stage'; return $item; });
                    $ass2 = $ass2Query->get()->map(function ($item) { $item->transaction_type = 'printing'; return $item; });
                    $ass3 = $ass3Query->get()->map(function ($item) { $item->transaction_type = 'printing_to_stitching'; return $item; });
                }
            }

            $assignments = $ass1->merge($ass2)->merge($ass3)->sortByDesc('created_at');
        }

        $assignments->each(function($item) use ($unit) {
            if (isset($item->productSet)) {
                $item->order_sku = $item->productSet->orderMain->sku ?? '-';
                $item->customer_name = $item->productSet->orderMain?->customer->name ?? '-';
                $item->lot_number = $item->lot_no ?? ($item->productSet->design_number ?? null);
                $item->design_number = $item->productSet->design_number ?? null;
            } else {
                $sku = $item->orderProduct->orderMain->sku ?? $item->sku ?? '-';
                if ($sku === '-' || empty($sku)) $sku = $item->orderProduct?->orderProductSet?->orderMain?->sku ?? '-';
                if ($sku === '-' && !empty($item->lot_no)) {
                    $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderMain')->first();
                    $sku = $lRef->orderMain->sku ?? $lRef->order_no ?? '-';
                }
                $item->order_sku = $sku;
                $item->customer_name = $item->orderProduct?->orderMain?->customer?->name ?? '-';
                $item->lot_number = $item->lot_no;
                
                // Get design number
                $designNo = $item->orderProduct?->orderProductSet?->design_number ?? null;
                if (!$designNo && !empty($item->lot_no)) {
                    $lRef = \App\Models\OrderLot::where('lot_no', $item->lot_no)->with('orderProductSet')->first();
                    $designNo = $lRef->orderProductSet->design_number ?? null;
                }
                $item->design_number = $designNo;
            }

            // ✅ Fetch Timing Information
            $item->timing = \App\Models\OrderLotStageTiming::where('lot_no', $item->lot_number)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
        });

        if ($isCutting) {
            $grouped = $assignments->groupBy('order_sku');
            $groupLabel = 'Order';
        } else {
            // For non-cutting, we group by incoming Slip No
            $groupedAssignments = $assignments->groupBy(function($item) {
                return $item->production_slip_digitization_id ?? ('T' . $item->id);
            });
            return view('unit.assignments_simple', compact('groupedAssignments', 'unit', 'type', 'view', 'canCloseTasks', 'isCutting'));
        }
        
        $orderSku = $request->get('order_sku');

        if ($orderSku) {
            $assignments = $grouped->get($orderSku, collect());
            
            if ($isCutting) {
                $allDetails = [];
                foreach ($assignments as $assignment) {
                    $stageAssignment = \App\Models\OrderCuttingStage::with(['productSet.stage_master_unit.masterFabricWarehouse', 'productSet.fabric', 'productSet.master_design_pattern', 'productSet.orderMain.customer', 'productSet.colors', 'productSet.size_measurement', 'productSet.master_product_fitting', 'cutting_master.masterFabricWarehouse', 'productSet.printing_unit'])->find($assignment->id);
                    if (!$stageAssignment) continue;

                    $data = $stageAssignment->productSet;
                    $sizes = [];
                    if (!empty($data->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
                    elseif (!empty($data->set_size)) $sizes = [$data->set_size];
                    $sizeSetRange = $data->size_measurement?->name ?? (count($sizes) > 0 ? min($sizes) . '*' . max($sizes) : '-');
                    $totalPcsInSet = $data->size_measurement->no_of_pcs ?? count($sizes);
                    
                    $header = [ 'id' => $stageAssignment->id, 'order_no' => $data->orderMain->sku ?? '-', 'date' => $data->created_at->format('d-m-Y'), 'customer' => $data->orderMain->customer->name ?? '-', 'design_no' => $data->design_number ?? '-', 'fabric' => $data->fabric_names ?? ($stageAssignment->fabric_names ?? '-'), 'color' => $data->colors->name ?? '-', 'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'), 'fitting' => $data->master_product_fitting?->name ?? ($stageAssignment->master_fitting?->name ?? '-'), 'warehouse' => $unit->masterFabricWarehouse->cutting_master_name ?? '-', 'unit_name' => $unit->name ?? '-', 'remark' => $stageAssignment->remarks ?? $data->remark ?? '-', 'belt' => $stageAssignment->belt ?? '-', 'total_pcs' => $stageAssignment->quantity ?? 0, 'lot_no' => 'Pending', 'size_set' => $sizeSetRange, 'pcs_in_set' => $totalPcsInSet, 'printing_required' => $data->is_printing ? 'Yes' : 'No', 'printing_unit' => $data->printing_unit ? $data->printing_unit->name : '-' ];
                    
                    $timing = \App\Models\OrderLotStageTiming::where('lot_no', $data->design_number)
                        ->where('master_stage_id', $unit->master_stage_id)
                        ->first();
                    
                    $header['start_date'] = $timing->start_date ?? $stageAssignment->start_date ?? null;
                    $header['end_date'] = $timing->end_date ?? $stageAssignment->end_date ?? null;
                    $header['complete_date'] = $timing->complete_date ?? $stageAssignment->complete_date ?? null;
                    
                    $totalInRatio = count($sizes);
                    $sizeCounts = array_count_values($sizes);
                    $sizeData = [];
                    foreach ($sizeCounts as $size => $count) { $sizeData[] = [ 'size' => $size, 'color' => $data->colors->name, 'pcs' => $totalInRatio > 0 ? ($count * $stageAssignment->quantity) / $totalInRatio : 0 ]; }
                    
                    $allDetails[] = [
                        'header' => $header,
                        'sizeData' => $sizeData,
                        'transaction' => $stageAssignment,
                        'isRework' => false
                    ];
                }
                
                return view('unit.order_assignment_tasks_details', compact('allDetails', 'unit', 'type', 'view', 'canCloseTasks', 'orderSku', 'groupLabel'));
            }

            return view('unit.order_assignment_tasks', compact('assignments', 'unit', 'type', 'view', 'canCloseTasks', 'orderSku', 'groupLabel'));
        }

        $orders = $grouped->map(function($items, $sku) use ($groupLabel) {
            $isDelayed = $items->contains(function($item) {
                return isset($item->timing) && !$item->timing->complete_date && now() > $item->timing->end_date;
            });

            // Find min start and max end
            $startDate = null;
            $endDate = null;
            foreach($items as $item) {
                if(isset($item->timing)) {
                    if(!$startDate || ($item->timing->start_date && $item->timing->start_date < $startDate)) $startDate = $item->timing->start_date;
                    if(!$endDate || ($item->timing->end_date && $item->timing->end_date > $endDate)) $endDate = $item->timing->end_date;
                }
            }

            $taskCount = $items->count();
            $directUrl = null;
            if ($taskCount === 1 && $groupLabel === 'Lot') {
                $single = $items->first();
                $directUrl = route('unit.assignments.details', [
                    'type' => $single->transaction_type ?? 'cutting',
                    'id' => $single->id
                ]);
            }

            $first = $items->first();
            $designNo = '-';
            $color = '-';
            if (isset($first->productSet)) {
                $designNo = $first->productSet->design_number ?? '-';
                $color = $first->productSet->colors->name ?? '-';
            } else {
                $designNo = $first->orderProduct?->orderProductSet?->design_number ?? '-';
                $color = $first->orderProduct?->orderProductSet?->colors->name ?? '-';
                if ($designNo === '-' && !empty($first->lot_no)) {
                    $lRef = \App\Models\OrderLot::where('lot_no', $first->lot_no)->with('orderProductSet.colors')->first();
                    $designNo = $lRef->orderProductSet->design_number ?? '-';
                    $color = $lRef->orderProductSet->colors->name ?? '-';
                }
            }

            return [
                'sku' => $sku,
                'customer' => $first->customer_name ?? '-',
                'task_count' => $taskCount,
                'latest_task' => $first->created_at,
                'is_delayed' => $isDelayed,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_label' => $groupLabel,
                'direct_url' => $directUrl,
                'design_no' => $designNo,
                'color' => $color,
                'total_quantity' => $items->sum(function($i) { return $i->quantity ?? $i->remaining_quantity ?? 0; })
            ];
        })->sortByDesc('latest_task');

        return view('unit.assignments', compact('orders', 'unit', 'type', 'view', 'canCloseTasks', 'groupLabel'));
    }

    public function orderSummary($sku, \App\Services\Admin\Report\OrderSummaryReportService $service)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $order = \App\Models\OrderMain::where('sku', $sku)->first();
        if (!$order) {
            return redirect()->back()->withError('Order not found');
        }

        $data = $service->view($order->id);
        if (!$data) {
            return redirect()->back()->withError('Order not found');
        }
        $data['lotsData'] = $service->lots($order->id);
        $data['is_unit'] = true;

        return view('admin.report.order_summary.view', $data);
    }

    public function closeAssignment($type, $id)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);

        if (!in_array($unit->master_stage_id, [self::STAGE_CUTTING, self::STAGE_PACKING])) return redirect()->back()->withError('Close task option is only available for Cutting and Packing units.');
        $record = $this->findAssignmentRecordForUnit($type, $id, $unitId);
        if (!$record) return redirect()->back()->withError('Assignment not found or access denied.');
        $record->is_closed_for_unit = 1;
        $record->complete_date = now();
        
        // Ensure end_date is set based on assignment time if not already present
        if (!$record->end_date && $record->start_date) {
            $days = $unit->lot_time_in_days ?? 0;
            if ($days > 0) {
                $record->end_date = \Carbon\Carbon::parse($record->start_date)->addDays($days);
            }
        }
        $record->save();

        // ✅ NEW: Update Unified Timing Table
        $lotNo = $record->lot_no;
        if (!$lotNo) {
             if ($type === 'cutting' && $record->productSet) {
                 // Try to find if a lot has been created for this product set already
                 $lot = \App\Models\OrderLot::where('order_products_set_id', $record->set_product_id)->first();
                 $lotNo = $lot->lot_no ?? $record->productSet->design_number ?? null;
             } elseif (method_exists($record, 'orderProduct') && $record->orderProduct) {
                 $lotNo = $record->orderProduct->orderProductSet->design_number ?? null;
             }
        }
        
        if ($lotNo && $unit->master_stage_id != 3) {
            \App\Models\OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lotNo, 'master_stage_id' => $unit->master_stage_id],
                [
                    'complete_date' => $record->complete_date,
                    'end_date' => $record->end_date, // Use the calculated/expected end date
                    'status' => 2
                ]
            );
        }
        return redirect()->route('unit.assignments')->withSuccess('Task closed successfully.');
    }

    public function reopenAssignment($type, $id)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);

        if (!in_array($unit->master_stage_id, [self::STAGE_CUTTING, self::STAGE_PACKING])) return redirect()->back()->withError('Re-open task option is only available for Cutting and Packing units.');
        $record = $this->findAssignmentRecordForUnit($type, $id, $unitId);
        if (!$record) return redirect()->back()->withError('Assignment not found or access denied.');
        $record->is_closed_for_unit = 0;
        $record->save();
        return redirect()->route('unit.assignments', ['view' => 'open'])->withSuccess('Task re-opened successfully.');
    }

    protected function findAssignmentRecordForUnit(string $type, int $id, int $unitId)
    {
        switch ($type) {
            case 'cutting': return \App\Models\OrderCuttingStage::where('id', $id)->where('to_assign_id', $unitId)->first();
            case 'stage': return \App\Models\OrderStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->first();
            case 'printing': return \App\Models\OrderPrintingStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->first();
            case 'printing_to_stitching': return \App\Models\OrderPrintingToStichingTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->first();
            case 'production': return \App\Models\ProductionSlipDigitization::where('id', $id)->where('stage_master_unit_id', $unitId)->first();
            case 'fabric': return \App\Models\FabricRollAssigning::where('id', $id)->where('stage_master_unit_id', $unitId)->first();
            default: return null;
        }
    }
    public function showSlipDetails($slip_id)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::find($unitId);
        
        $isDirectTx = str_starts_with($slip_id, 'T');
        
        $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'details']);
        $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'details']);
        $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'details']);
        
        if ($isDirectTx) {
            $txId = str_replace('T', '', $slip_id);
            $ass1Query->where('id', $txId)->whereNull('production_slip_digitization_id');
            $ass2Query->where('id', $txId)->whereNull('production_slip_digitization_id');
            $ass3Query->where('id', $txId)->whereNull('production_slip_digitization_id');
        } else {
            $ass1Query->where('production_slip_digitization_id', $slip_id);
            $ass2Query->where('production_slip_digitization_id', $slip_id);
            $ass3Query->where('production_slip_digitization_id', $slip_id);
        }
        
        $ass1 = $ass1Query->get()->map(function ($item) { $item->transaction_type = 'stage'; return $item; });
        $ass2 = $ass2Query->get()->map(function ($item) { $item->transaction_type = 'printing'; return $item; });
        $ass3 = $ass3Query->get()->map(function ($item) { $item->transaction_type = 'printing_to_stitching'; return $item; });
        
        $transactions = $ass1->merge($ass2)->merge($ass3);
        
        if ($transactions->isEmpty()) {
            abort(404, 'Slip assignments not found.');
        }

        $transactions->each(function($item) use ($unit) {
            $item->lot_number = $item->lot_no;
            $item->timing = \App\Models\OrderLotStageTiming::where('lot_no', $item->lot_number)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
        });

        $previousSlip = null;
        if (!$isDirectTx) {
            $previousSlip = \App\Models\ProductionSlipDigitization::find($slip_id);
        }

        return view('unit.slip_details', compact('unit', 'transactions', 'slip_id', 'previousSlip'));
    }


    public function showAssignmentDetails($type, $id)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = StageMasterUnit::with('masterFabricWarehouse')->find($unitId);

        $header = [];
        $sizeData = [];
        $transaction = null;
        $orderProductSet = null;
        $orderLot = null;
        $isRework = false;

        if ($type === 'cutting') {
            $stageAssignment = \App\Models\OrderCuttingStage::with(['productSet.stage_master_unit.masterFabricWarehouse', 'productSet.fabric', 'productSet.master_design_pattern', 'productSet.orderMain.customer', 'productSet.colors', 'productSet.size_measurement', 'productSet.master_product_fitting', 'cutting_master.masterFabricWarehouse'])->findOrFail($id);
            $data = $stageAssignment->productSet;
            $orderProductSet = $data;
            $sizes = [];
            if (!empty($data->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
            elseif (!empty($data->set_size)) $sizes = [$data->set_size];
            $sizeSetRange = $data->size_measurement?->name ?? (count($sizes) > 0 ? min($sizes) . '*' . max($sizes) : '-');
            $totalPcsInSet = $data->size_measurement->no_of_pcs ?? count($sizes);
            $header = [ 'id' => $stageAssignment->id, 'order_no' => $data->orderMain->sku ?? '-', 'date' => $data->created_at->format('d-m-Y'), 'customer' => $data->orderMain->customer->name ?? '-', 'design_no' => $data->design_number ?? '-', 'fabric' => $data->fabric_names ?? ($stageAssignment->fabric_names ?? '-'), 'color' => $data->colors->name ?? '-', 'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'), 'fitting' => $data->master_product_fitting?->name ?? ($stageAssignment->master_fitting?->name ?? '-'), 'warehouse' => $unit->masterFabricWarehouse->cutting_master_name ?? '-', 'unit_name' => $unit->name ?? '-', 'remark' => $stageAssignment->remarks ?? $data->remark ?? '-', 'belt' => $stageAssignment->belt ?? '-', 'total_pcs' => $stageAssignment->quantity ?? 0, 'lot_no' => 'Pending', 'size_set' => $sizeSetRange, 'pcs_in_set' => $totalPcsInSet ];
            
            // ✅ NEW: Fetch Timing Information
            $timing = \App\Models\OrderLotStageTiming::where('lot_no', $data->design_number)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
            
            $header['start_date'] = $timing->start_date ?? $stageAssignment->start_date ?? null;
            $header['end_date'] = $timing->end_date ?? $stageAssignment->end_date ?? null;
            $header['complete_date'] = $timing->complete_date ?? $stageAssignment->complete_date ?? null;
            $totalInRatio = count($sizes);
            $sizeCounts = array_count_values($sizes);
            foreach ($sizeCounts as $size => $count) { $sizeData[] = [ 'size' => $size, 'color' => $data->colors->name, 'pcs' => $totalInRatio > 0 ? ($count * $stageAssignment->quantity) / $totalInRatio : 0 ]; }
        } else {
            switch ($type) {
                case 'stage': $transaction = \App\Models\OrderStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                case 'printing': $transaction = \App\Models\OrderPrintingStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                case 'printing_to_stitching': $transaction = \App\Models\OrderPrintingToStichingTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                case 'production': return $this->viewSlip('production', $id);
                case 'fabric': return $this->viewSlip('fabric', $id);
                default: abort(404, 'Invalid assignment type');
            }
            if ($transaction && $transaction->orderProduct && $transaction->orderProduct->orderProductSet) $orderProductSet = $transaction->orderProduct->orderProductSet;
            if (!$orderProductSet && $transaction && $transaction->lot_no) {
                $orderLot = \App\Models\OrderLot::where('lot_no', $transaction->lot_no)->with(['orderProductSet.fabric', 'orderProductSet.colors', 'orderProductSet.master_design_pattern', 'orderProductSet.master_product_fitting', 'orderProductSet.orderMain.customer', 'orderMain.customer'])->first();
                if ($orderLot && $orderLot->orderProductSet) $orderProductSet = $orderLot->orderProductSet;
            }
            $hOrderMain = $transaction->orderProduct->orderMain ?? $orderProductSet->orderMain ?? ($orderLot->orderMain ?? null);
            $sizes = [];
            if ($orderProductSet) {
                if (!empty($orderProductSet->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $orderProductSet->size_measurement->size_group));
                elseif (!empty($orderProductSet->set_size)) $sizes = [$orderProductSet->set_size];
            }
            $isRework = ($transaction && isset($transaction->type) && $transaction->type === 'rework');
            $sizeSetRange = $orderProductSet->size_measurement?->name ?? (count($sizes) > 0 ? min($sizes) . '*' . max($sizes) : '-');
            $totalPcsInSet = $orderProductSet->size_measurement->no_of_pcs ?? count($sizes);
            $header = [
                'id' => $id,
                'order_no' => $hOrderMain->sku ?? '-',
                'date' => $transaction->created_at->format('d-m-Y'),
                'customer' => $hOrderMain->customer->name ?? '-',
                'design_no' => $orderProductSet->design_number ?? '-',
                'fabric' => $orderProductSet->fabric_names ?? ($orderProductSet->order_cutting_stage->fabric_names ?? '-'),
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
                'belt' => $orderProductSet->order_cutting_stage->belt ?? '-'
            ];

            // ✅ NEW: Fetch Timing Information
            $timing = \App\Models\OrderLotStageTiming::where('lot_no', $transaction->lot_no)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
            $header['start_date'] = $timing->start_date ?? $transaction->start_date ?? null;
            $header['end_date'] = $timing->end_date ?? $transaction->end_date ?? null;
            $header['complete_date'] = $timing->complete_date ?? $transaction->complete_date ?? null;
            if ($type === 'printing' && method_exists($transaction, 'printingDetails')) {
                foreach ($transaction->printingDetails as $det) $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
            } elseif ($type === 'stage' || $type === 'printing_to_stitching') {
                $details = ($type === 'stage') ? \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $id)->get() : \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $id)->get();
                foreach ($details as $det) $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
            }
            if (!empty($sizeData)) $header['total_pcs'] = array_sum(array_column($sizeData, 'pcs'));
            elseif ($header['total_pcs'] == 0 && isset($transaction->quantity)) $header['total_pcs'] = $transaction->quantity;
        }
        $nextStages = ($unit->master_stage_id == self::STAGE_CUTTING) ? \App\Models\MasterProductStage::whereIn('id', [self::STAGE_PRINTING, self::STAGE_STITCHING])->get() : [];
        return view('unit.assignment_details', [ 'header' => $header, 'sizeData' => $sizeData, 'transaction' => $transaction, 'type' => $type, 'unit' => $unit, 'nextStages' => $nextStages, 'isRework' => $isRework, 'encrypted_unit_id' => \Illuminate\Support\Facades\Crypt::encryptString($unitId) ]);
    }
    
    public function downloadAssignmentDetailsPdf($type, $id)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = \App\Models\StageMasterUnit::with('masterFabricWarehouse')->find($unitId);
        $transaction = null;
        $orderProductSet = null;
        $orderLot = null;
        $sizeData = [];

        if ($type === 'cutting') {
            $stageAssignment = \App\Models\OrderCuttingStage::with(['productSet.stage_master_unit.masterFabricWarehouse', 'productSet.fabric', 'productSet.master_design_pattern', 'productSet.orderMain.customer', 'productSet.colors', 'productSet.size_measurement', 'productSet.master_product_fitting', 'cutting_master.masterFabricWarehouse'])->findOrFail($id);
            $data = $stageAssignment->productSet;
            $orderProductSet = $data;
            $sizes = [];
            if (!empty($data->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
            elseif (!empty($data->set_size)) $sizes = [$data->set_size];
            $sizeSetRange = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
            $totalPcsInSet = $data->size_measurement->no_of_pcs ?? count($sizes);
            $header = [ 'id' => $stageAssignment->id, 'order_no' => $data->orderMain->sku ?? '-', 'date' => $data->created_at->format('d-m-Y'), 'customer' => $data->orderMain->customer->name ?? '-', 'design_no' => $data->design_number ?? '-', 'fabric' => $data->fabric_names ?? ($stageAssignment->fabric_names ?? '-'), 'color' => $data->colors->name ?? '-', 'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'), 'fitting' => $data->master_product_fitting?->name ?? ($stageAssignment->master_fitting?->name ?? '-'), 'warehouse' => $unit->masterFabricWarehouse->cutting_master_name ?? '-', 'unit_name' => $unit->name ?? '-', 'remark' => $stageAssignment->remarks ?? $data->remark ?? '-', 'belt' => $stageAssignment->belt ?? '-', 'total_pcs' => $stageAssignment->quantity ?? 0, 'lot_no' => 'Pending', 'size_set' => $sizeSetRange, 'pcs_in_set' => $totalPcsInSet ];
            
            $timing = \App\Models\OrderLotStageTiming::where('lot_no', $data->design_number)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
            
            $header['start_date'] = $timing->start_date ?? $stageAssignment->start_date ?? null;
            $header['end_date'] = $timing->end_date ?? $stageAssignment->end_date ?? null;
            $header['complete_date'] = $timing->complete_date ?? $stageAssignment->complete_date ?? null;
        } else {
            switch ($type) {
                case 'stage': $transaction = \App\Models\OrderStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                case 'printing': $transaction = \App\Models\OrderPrintingStageTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                case 'printing_to_stitching': $transaction = \App\Models\OrderPrintingToStichingTransaction::where('id', $id)->where('sub_stage_id_to', $unitId)->with(['from_stage', 'productionSlipDigitization', 'getFromUnitMaster', 'orderProduct.orderMain.customer', 'orderProduct.orderProductSet.fabric', 'orderProduct.orderProductSet.colors', 'orderProduct.orderProductSet.master_design_pattern', 'orderProduct.orderProductSet.master_product_fitting', 'orderProduct.orderProductSet.order_cutting_stage.fabric', 'orderProduct.orderProductSet.order_cutting_stage.pattern', 'orderProduct.orderProductSet.order_cutting_stage.master_fitting'])->firstOrFail(); break;
                default: abort(404, 'Invalid assignment type');
            }
            if ($transaction && $transaction->orderProduct && $transaction->orderProduct->orderProductSet) $orderProductSet = $transaction->orderProduct->orderProductSet;
            if (!$orderProductSet && $transaction && $transaction->lot_no) {
                $orderLot = \App\Models\OrderLot::where('lot_no', $transaction->lot_no)->with(['orderProductSet.fabric', 'orderProductSet.colors', 'orderProductSet.master_design_pattern', 'orderProductSet.master_product_fitting', 'orderProductSet.orderMain.customer', 'orderMain.customer'])->first();
                if ($orderLot && $orderLot->orderProductSet) $orderProductSet = $orderLot->orderProductSet;
            }
            $hOrderMain = $transaction->orderProduct->orderMain ?? $orderProductSet->orderMain ?? ($orderLot->orderMain ?? null);
            $sizes = [];
            if ($orderProductSet) {
                if (!empty($orderProductSet->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $orderProductSet->size_measurement->size_group));
                elseif (!empty($orderProductSet->set_size)) $sizes = [$orderProductSet->set_size];
            }
            $sizeSetRange = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
            $totalPcsInSet = $orderProductSet->size_measurement->no_of_pcs ?? count($sizes);
            $header = [
                'id' => $id,
                'order_no' => $hOrderMain->sku ?? '-',
                'date' => $transaction->created_at->format('d-m-Y'),
                'customer' => $hOrderMain->customer->name ?? '-',
                'design_no' => $orderProductSet->design_number ?? '-',
                'fabric' => $orderProductSet->fabric_names ?? ($orderProductSet->order_cutting_stage->fabric_names ?? '-'),
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
                'belt' => $orderProductSet->order_cutting_stage->belt ?? '-'
            ];

            $timing = \App\Models\OrderLotStageTiming::where('lot_no', $transaction->lot_no)
                ->where('master_stage_id', $unit->master_stage_id)
                ->first();
            $header['start_date'] = $timing->start_date ?? $transaction->start_date ?? null;
            $header['end_date'] = $timing->end_date ?? $transaction->end_date ?? null;
            $header['complete_date'] = $timing->complete_date ?? $transaction->complete_date ?? null;
            
            if ($type === 'printing' && method_exists($transaction, 'printingDetails')) {
                foreach ($transaction->printingDetails as $det) $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
            } elseif ($type === 'stage' || $type === 'printing_to_stitching') {
                $details = ($type === 'stage') ? \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $id)->get() : \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $id)->get();
                foreach ($details as $det) $sizeData[] = ['size' => $det->size, 'color' => $header['color'], 'pcs' => $det->quantity];
            }
            if (!empty($sizeData)) $header['total_pcs'] = array_sum(array_column($sizeData, 'pcs'));
            elseif ($header['total_pcs'] == 0 && isset($transaction->quantity)) $header['total_pcs'] = $transaction->quantity;
        }

        $pdf = Pdf::loadView('unit.pdf.assignment_details_pdf', compact('header', 'transaction', 'type', 'unit'))->setPaper('A4', 'portrait');
        return $pdf->download('Assignment_Details_CMPO_' . $header['id'] . '.pdf');
    }

    public function downloadSlip($slipId)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $slip = ProductionSlipDigitization::with(['fromStage', 'getUnitMaster.masterFabricWarehouse'])->findOrFail($slipId);
        $general_setting = GeneralSettings::where('status', 1)->first();
        $data = [ 'slip' => $slip, 'general_setting' => $general_setting, 'lot' => null, 'lots' => collect(), 'rolls' => collect(), 'printing' => null, 'printings' => collect(), 'printing_sizes' => collect(), 'stage_transactions' => collect(), 'packing_details' => collect(), 'size_set' => '-', 'pcs_in_set' => 0, ];

        switch ($slip->save_type) {
            case 1:
                $lots = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)->with(['orderMain', 'orderProductSet.fabric', 'orderProductSet.colors', 'orderProductSet.master_design_pattern', 'orderProductSet.master_product_fitting'])->get();
                if ($lots->isNotEmpty()) { $lot = $lots->first(); $rolls = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)->with(['fabricRollAssigningsDetail', 'stageMasterUnit.masterFabricWarehouse'])->get(); $data['lot'] = $lot; $data['lots'] = $lots; $data['rolls'] = $rolls; }
                break;
            case 2:
                $printings = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])->get();
                if ($printings->isNotEmpty()) { $printing = $printings->first(); $printingSizes = \App\Models\OrderPrintingStageTransactionDetail::where('order_printing_stage_transaction_id', $printing->id)->get(); $data['printing'] = $printing; $data['printings'] = $printings; $data['printing_sizes'] = $printingSizes; }
                break;
            case 3:
                if ($slip->from_stage_id == 1) {
                    $stage_transactions = \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])->get();
                    if ($stage_transactions->isNotEmpty()) { $stageTransaction = $stage_transactions->first(); $stageSizes = \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $stageTransaction->id)->get(); $data['stage_transaction'] = $stageTransaction; $data['stage_transactions'] = $stage_transactions; $data['stage_sizes'] = $stageSizes; }
                } else {
                    $stage_transactions = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)->with(['from_stage', 'to_stage', 'details', 'orderProduct.orderProductSet.orderMain'])->get();
                    if ($stage_transactions->isNotEmpty()) { $stageTransaction = $stage_transactions->first(); $stageSizes = \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $stageTransaction->id)->get(); $data['stage_transaction'] = $stageTransaction; $data['stage_transactions'] = $stage_transactions; $data['stage_sizes'] = $stageSizes; }
                }
                if ($slip->from_stage_id == 11) { $packingDetails = \App\Models\PackingMain::where('slip_id', $slip->id)->with(['cartons.boxes.items.detail', 'cartons.items.detail'])->get(); $data['packing_details'] = $packingDetails; }
                break;
        }

        $orderSet = $slip->orderProductSet;
        if (!$orderSet && $data['lots']->isNotEmpty()) $orderSet = $data['lots']->first()->orderProductSet;
        if (!$orderSet && $data['printings']->isNotEmpty()) $orderSet = $data['printings']->first()->orderProduct?->orderProductSet;
        if (!$orderSet && $data['stage_transactions']->isNotEmpty()) $orderSet = $data['stage_transactions']->first()->orderProduct?->orderProductSet;

        if (!$orderSet) {
            $fallbackLot = null;
            if ($data['lots']->isNotEmpty()) $fallbackLot = $data['lots']->first()->lot_no;
            elseif ($data['printings']->isNotEmpty()) $fallbackLot = $data['printings']->first()->lot_no;
            elseif ($data['stage_transactions']->isNotEmpty()) $fallbackLot = $data['stage_transactions']->first()->lot_no;
            if ($fallbackLot) { $orderLot = \App\Models\OrderLot::where('lot_no', $fallbackLot)->with('orderProductSet')->first(); if ($orderLot && $orderLot->orderProductSet) $orderSet = $orderLot->orderProductSet; }
        }
        $data['orderProductSet'] = $orderSet;
        $sizes = [];
        if ($orderSet) { $orderSet->loadMissing('size_measurement'); if (!empty($orderSet->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $orderSet->size_measurement->size_group)); elseif (!empty($orderSet->set_size)) $sizes = [$orderSet->set_size]; }
        $data['size_set'] = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
        $grandTotal = 0;
        foreach ($data['lots'] as $l) $grandTotal += ($l->quantity ?? 0);
        foreach ($data['printings'] as $p) $grandTotal += ($p->quantity ?? 0);
        foreach ($data['stage_transactions'] as $st) $grandTotal += ($st->quantity ?? 0);
        $data['pcs_in_set'] = ($grandTotal == 0) ? (($orderSet && $orderSet->size_measurement) ? ($orderSet->size_measurement->no_of_pcs ?? count($sizes)) : count($sizes)) : $grandTotal;

        $pdf = Pdf::loadView('admin.uploaded_slips.pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->download('Production_Slip_' . $slipId . '.pdf');
    }

    public function downloadCmpo($id)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        $stageAssignment = \App\Models\OrderCuttingStage::with(['productSet.size_measurement', 'productSet.colors', 'productSet.fabric', 'productSet.master_design_pattern', 'productSet.orderMain.customer', 'master_fitting', 'fabric', 'pattern', 'cutting_master.masterFabricWarehouse'])->findOrFail($id);
        $data = $stageAssignment->productSet;
        $cmpoHeader = [ 'cmpo_id' => $stageAssignment->id, 'date' => $stageAssignment->created_at->format('d-m-Y'), 'order_no' => $data->orderMain->sku ?? '-', 'customer' => $data->orderMain->customer->name ?? '-', 'design_no' => $data->design_number ?? '-', 'color' => $data->colors->name ?? '-', 'fabric' => $data->fabric->name ?? ($stageAssignment->fabric->name ?? '-'), 'pattern' => $data->master_design_pattern->name ?? ($stageAssignment->pattern->name ?? '-'), 'fitting' => $stageAssignment->master_fitting->name ?? '-', 'warehouse_name' => $stageAssignment->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-', 'cuttingMaster' => $stageAssignment->cutting_master->name ?? '-', 'remark' => $stageAssignment->remarks ?? '-', 'belt' => $stageAssignment->belt ?? '-', 'total_pcs' => $stageAssignment->quantity ?? 0, ];
        $sizes = [];
        if (!empty($data->size_measurement?->size_group)) $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
        elseif (!empty($data->set_size)) $sizes = [$data->set_size];
        $cmpoHeader['size_set'] = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';
        $cmpoHeader['pcs_in_set'] = $data->size_measurement->no_of_pcs ?? count($sizes);
        $sizeData = [];
        $total_pcs = $cmpoHeader['total_pcs'];
        $totalInRatio = count($sizes);
        $sizeCounts = array_count_values($sizes);
        foreach ($sizeCounts as $size => $count) { $sizeData[$size] = [ 'design_no' => $data->design_number, 'color' => $data->colors->name, 'size' => $size, 'pcs' => $totalInRatio > 0 ? ($count * $total_pcs) / $totalInRatio : 0, ]; }
        $pdf = Pdf::loadView('admin.product_order.cmpo_slip', [ 'header' => $cmpoHeader, 'sizeData' => $sizeData, ])->setPaper('a4', 'portrait');
        return $pdf->download('CMPO-' . $id . '.pdf');
    }

    public function lotSearch(Request $request)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        return view('unit.lot_search');
    }

    public function lotDetails(Request $request)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');

        if (!$request->filled('lot_no')) {
            return redirect()->route('unit.lot.search')->with('error', 'Please enter a Lot Number.');
        }

        $service = app(\App\Services\Admin\ReportService::class);
        $response['data'] = $service->lotDetails($request->lot_no);

        if (empty($response['data'])) {
            return redirect()->route('unit.lot.search')->with('error', 'Lot not found or has been completely deleted.');
        }

        $response['master_stages'] = $service->master_stages();
        return view('unit.lot_details', $response);
    }

    public function pendingTasks(Request $request)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $unitAuth = session('unit_auth');
        $unitId = $unitAuth['id'];
        $unit = \App\Models\StageMasterUnit::find($unitId);
        
        $lotNo = $request->get('lot_no');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $isDelayed = $request->get('is_delayed');

        $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster']);
        $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster']);
        $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster']);
        $ass4Query = \App\Models\OrderGodamStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster']);

        if ($lotNo) {
            $ass1Query->where('lot_no', $lotNo);
            $ass2Query->where('lot_no', $lotNo);
            $ass3Query->where('lot_no', $lotNo);
            $ass4Query->where('lot_no', $lotNo);
        }

        if ($startDate) {
            $ass1Query->whereDate('created_at', '>=', $startDate);
            $ass2Query->whereDate('created_at', '>=', $startDate);
            $ass3Query->whereDate('created_at', '>=', $startDate);
            $ass4Query->whereDate('created_at', '>=', $startDate);
        }

        $ass1 = $ass1Query->get()->map(function($i) { $i->source = 'cutting'; return $i; });
        $ass2 = $ass2Query->get()->map(function($i) { $i->source = 'printing'; return $i; });
        $ass3 = $ass3Query->get()->map(function($i) { $i->source = 'printing_to_stitching'; return $i; });
        $ass4 = $ass4Query->get()->map(function($i) { $i->source = 'godam'; return $i; });

        $all = collect()->concat($ass1)->concat($ass2)->concat($ass3)->concat($ass4);
        
        $lotNos = $all->pluck('lot_no')->unique()->toArray();
        $masterStageId = $unit->master_stage_id ?? 0;
        $timings = \App\Models\OrderLotStageTiming::whereIn('lot_no', $lotNos)->where('master_stage_id', $masterStageId)->get();

        $grouped = $all->groupBy('lot_no')->map(function ($items, $lot_no) use ($timings) {
            $firstItem = $items->first();
            $timing = $timings->where('lot_no', $lot_no)->first();
            
            $assignedDate = $firstItem->created_at;
            $estimatedDate = $timing?->end_date ? \Carbon\Carbon::parse($timing->end_date) : ($firstItem->end_date ? \Carbon\Carbon::parse($firstItem->end_date) : null);
            
            $isTaskDelayed = false;
            if ($estimatedDate && now()->gt($estimatedDate)) {
                $isTaskDelayed = true;
            }
            
            // Fix double counting by only considering 'cutting' source if it exists, as requested by user.
            $cuttingItems = $items->where('source', 'cutting');
            if ($cuttingItems->count() > 0) {
                $itemsToSum = $cuttingItems;
            } else {
                $itemsToSum = $items;
            }
            
            $sentBy = '-';
            $stageName = $firstItem->from_stage->name ?? ($firstItem->source == 'cutting' ? 'Cutting' : '-');
            $unitName = $firstItem->getFromUnitMaster->name ?? null;
            
            if ($stageName !== '-' && $unitName) {
                $sentBy = $stageName . ' (' . $unitName . ')';
            } elseif ($unitName) {
                $sentBy = $unitName;
            } elseif ($stageName !== '-') {
                $sentBy = $stageName;
            }

            // Fetch extra info via OrderLot
            $orderLot = \App\Models\OrderLot::with(['orderProductSet.orderCuttingStages', 'orderProductSet.size_measurement'])->where('lot_no', $lot_no)->first();
            $designNo = $orderLot?->orderProductSet?->design_number ?? '-';
            $sizeSet = $orderLot?->orderProductSet?->size_measurement?->name ?? '-';
            
            // Total cutting pieces
            $totalCuttingPieces = 0;
            $rolls = \App\Models\FabricRollAssigning::with('fabricRollAssigningsDetail')->where('lot_no', $lot_no)->get();
            if ($rolls->isNotEmpty()) {
                $totalCuttingPieces = $rolls->flatMap(function($roll) {
                    return $roll->fabricRollAssigningsDetail;
                })->sum('quantity');
            }

            return [
                'lot_no' => $lot_no,
                'design_no' => $designNo,
                'size_set' => $sizeSet,
                'total_cutting_pieces' => $totalCuttingPieces,
                'sent_by' => $sentBy,
                'total_assigned' => $itemsToSum->sum('quantity'),
                'total_pending' => $itemsToSum->sum('remaining_quantity'),
                'assigned_date' => $assignedDate,
                'estimated_date' => $estimatedDate,
                'is_delayed' => $isTaskDelayed
            ];
        })->filter(function($item) use ($isDelayed, $endDate) {
            if ($item['total_pending'] <= 0) return false;
            if ($isDelayed && !$item['is_delayed']) return false;
            if ($endDate && $item['estimated_date']) {
                if (\Carbon\Carbon::parse($item['estimated_date'])->format('Y-m-d') > \Carbon\Carbon::parse($endDate)->format('Y-m-d')) {
                    return false;
                }
            }
            return true;
        })->values();

        return view('unit.pending_tasks', compact('grouped'));
    }
}