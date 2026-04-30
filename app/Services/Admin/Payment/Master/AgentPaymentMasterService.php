<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\AgentPaymentMaster;
use Illuminate\Http\Request;
use App\Http\DataTable\Admin\Payment\Master\AgentPaymentMasterDataTable as DataTable;

class AgentPaymentMasterService
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
        return AgentPaymentMaster::create($input);
    }

    public function edit(Request $request)
    {
        return AgentPaymentMaster::find($request->id);
    }

    public function update($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = $request->status ?? 1;
        $agentPayment = AgentPaymentMaster::find($request->id);
        if ($agentPayment) {
            $agentPayment->update($input);
            return $agentPayment;
        }
        return false;
    }

    public function delete($request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\AgentPaymentMaster')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            $agentPayment = \App\Models\AgentPaymentMaster::find($request->id);
            if ($agentPayment) {
                $agentPayment->delete();
                return true;
            }
            return false;
        }

        return \App\Models\AgentPaymentMaster::where('id', $request->id)->update(['status' => 0]);
    }
}
