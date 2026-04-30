<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\WashingMaster;
use Illuminate\Http\Request;
use App\Http\DataTable\Admin\Payment\Master\WashingMasterDataTable as DataTable;

class WashingMasterService
{
    public function index()
    {
        return [];
    }

    public function indexList(Request $request)
    {
        $dataTable = new DataTable();
        return $dataTable->indexList($request);
    }

    public function store($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = 1;
        return WashingMaster::create($input);
    }

    public function edit(Request $request)
    {
        return WashingMaster::find($request->id);
    }

    public function update($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = $request->status ?? 1;
        $washingMaster = WashingMaster::find($request->id);
        if ($washingMaster) {
            $washingMaster->update($input);
            return $washingMaster;
        }
        return false;
    }

    public function delete($request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\WashingMaster')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            $washingMaster = \App\Models\WashingMaster::find($request->id);
            if ($washingMaster) {
                $washingMaster->delete();
                return true;
            }
            return false;
        }

        return \App\Models\WashingMaster::where('id', $request->id)->update(['status' => 0]);
    }
}
