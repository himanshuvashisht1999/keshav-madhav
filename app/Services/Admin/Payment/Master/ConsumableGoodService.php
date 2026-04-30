<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\ConsumableGood;
use App\Http\DataTable\Admin\Payment\Master\ConsumableGoodDataTable as DataTable;

class ConsumableGoodService
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
        $consumableGood = new ConsumableGood();
        $consumableGood->name = $request->name;
        $consumableGood->status = 1;
        $consumableGood->save();
        return true;
    }

    public function edit(Request $request)
    {
        return ConsumableGood::find($request->id);
    }

    public function update(Request $request)
    {
        $consumableGood = ConsumableGood::find($request->id);
        $consumableGood->name = $request->name;
        $consumableGood->save();
        return true;
    }

    public function delete(Request $request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\ConsumableGood')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            return \App\Models\ConsumableGood::where('id', $request->id)->delete();
        }

        return \App\Models\ConsumableGood::where('id', $request->id)->update(['status' => 0]);
    }
}
