<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Tax;
use App\Http\DataTable\Admin\Payment\Master\TaxDataTable as DataTable;

class TaxService
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
        $tax = new Tax();
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->status = 1;
        $tax->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Tax::find($request->id);
    }

    public function update(Request $request)
    {
        $tax = Tax::find($request->id);
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->save();
        return true;
    }

    public function delete(Request $request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\Tax')->pluck('id');
        
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)
            ->where('ref_id', (string)$request->id)
            ->exists();

        if (!$hasAdjustments) {
            return Tax::where('id', $request->id)->delete();
        }

        return Tax::where('id', $request->id)->update(['status' => 0]);
    }
}
