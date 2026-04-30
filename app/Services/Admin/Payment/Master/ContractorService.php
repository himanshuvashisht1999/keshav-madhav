<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Contractor;
use App\Http\DataTable\Admin\Payment\Master\ContractorDataTable as DataTable;

class ContractorService
{
    protected $datatable;

    public function __construct(DataTable $datatable)
    {
        $this->datatable = $datatable;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {
        $contractor = new Contractor();
        $contractor->name = $request->name;
        $contractor->phone = $request->phone;
        $contractor->address = $request->address;
        $contractor->status = 1;
        $contractor->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Contractor::find($request->id);
    }

    public function update(Request $request)
    {
        $contractor = Contractor::find($request->id);
        $contractor->name = $request->name;
        $contractor->phone = $request->phone;
        $contractor->address = $request->address;
        $contractor->save();
        return true;
    }

    public function delete(Request $request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\Contractor')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            return \App\Models\Contractor::where('id', $request->id)->delete();
        }

        return \App\Models\Contractor::where('id', $request->id)->update(['status' => 0]);
    }
}
