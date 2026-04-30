<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\CuttingPaymentMaster;
use Illuminate\Http\Request;
use App\Http\DataTable\Admin\Payment\Master\CuttingPaymentMasterDataTable as DataTable;

class CuttingPaymentMasterService
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
        return CuttingPaymentMaster::create($input);
    }

    public function edit(Request $request)
    {
        return CuttingPaymentMaster::find($request->id);
    }

    public function update($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = $request->status ?? 1;
        $cuttingPayment = CuttingPaymentMaster::find($request->id);
        if ($cuttingPayment) {
            $cuttingPayment->update($input);
            return $cuttingPayment;
        }
        return false;
    }

    public function delete($request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\CuttingPaymentMaster')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            $cuttingPayment = \App\Models\CuttingPaymentMaster::find($request->id);
            if ($cuttingPayment) {
                $cuttingPayment->delete();
                return true;
            }
            return false;
        }

        return \App\Models\CuttingPaymentMaster::where('id', $request->id)->update(['status' => 0]);
    }
}
