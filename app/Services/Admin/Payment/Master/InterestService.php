<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Interest;
use App\Http\DataTable\Admin\Payment\Master\InterestDataTable as DataTable;

class InterestService
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
        $interest = new Interest();
        $interest->name = $request->name;
        $interest->percentage = $request->percentage;
        $interest->status = 1;
        $interest->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Interest::find($request->id);
    }

    public function update(Request $request)
    {
        $interest = Interest::find($request->id);
        $interest->name = $request->name;
        $interest->percentage = $request->percentage;
        $interest->save();
        return true;
    }

    public function delete(Request $request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\Interest')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            return \App\Models\Interest::where('id', $request->id)->delete();
        }

        return \App\Models\Interest::where('id', $request->id)->update(['status' => 0]);
    }
}
