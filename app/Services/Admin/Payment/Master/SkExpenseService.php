<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\SkExpense;
use Illuminate\Http\Request;
use App\Http\DataTable\Admin\Payment\Master\SkExpenseDataTable as DataTable;

class SkExpenseService
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
        return SkExpense::create($input);
    }

    public function edit(Request $request)
    {
        return SkExpense::find($request->id);
    }

    public function update($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = $request->status ?? 1;
        $skExpense = SkExpense::find($request->id);
        if ($skExpense) {
            $skExpense->update($input);
            return $skExpense;
        }
        return false;
    }

    public function delete($request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\SkExpense')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$request->id)->exists();

        if (!$hasAdjustments) {
            $skExpense = \App\Models\SkExpense::find($request->id);
            if ($skExpense) {
                $skExpense->delete();
                return true;
            }
            return false;
        }

        return \App\Models\SkExpense::where('id', $request->id)->update(['status' => 0]);
    }
}
