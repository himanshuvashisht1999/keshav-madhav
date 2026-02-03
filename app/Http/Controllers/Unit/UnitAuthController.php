<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\StageMasterUnit;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use Illuminate\Support\Facades\File;

class UnitAuthController extends Controller
{
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

        return view('unit.upload', $response);
    }

    public function submitSlip(Request $request)
    {
        if (!session()->has('unit_auth')) {
            return redirect()->route('unit.login');
        }

        $stageMasterUnitId = Crypt::decryptString($request->stage_master_unit_id);
        $data = StageMasterUnit::findOrFail($stageMasterUnitId);

        $request->validate([
            'photo_data' => 'required'
        ]);

        $slip_file = null;

        if ($request->photo_data) {
            $image = $request->photo_data;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);
            $slip_file = 'production-slip-' . rand(1000, 9999) . '_' . time() . '.jpg';
            $destinationPath = public_path('assets/production_slips');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true);
            }

            file_put_contents($destinationPath . '/' . $slip_file, $imageData);
        }

        if ($request->type == 1) {
            $save_data = new FabricRollAssigning;
            $save_data->stage_master_unit_id = $data->id;
            $save_data->slip_file = $slip_file;
            $save_data->status = 0;
            $save_data->save();
        } else {
            $save_data = new ProductionSlipDigitization;
            $save_data->from_stage_id = $data->master_stage_id;
            $save_data->stage_master_unit_id = $data->id;
            $save_data->slip_file = $slip_file;
            $save_data->status = 0;
            $save_data->save();
        }

        return redirect()->back()->withSuccess('Production slip uploaded successfully.');
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
            ->orderBy('created_at', 'desc')
            ->get();

        $productionSlips = ProductionSlipDigitization::where('stage_master_unit_id', $unitId)
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

        // Only allow viewing production slips with full details
        $slip = ProductionSlipDigitization::where('id', $id)
            ->where('stage_master_unit_id', $unitId)
            ->with(['fromStage', 'getUnitMaster.masterFabricWarehouse'])
            ->firstOrFail();

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
}
