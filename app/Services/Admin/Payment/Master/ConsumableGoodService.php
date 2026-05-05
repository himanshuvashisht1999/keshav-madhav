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
        $data = $request->all();
        $balance = $data['balance'] ?? 0;
        if (($data['balance_type'] ?? '') == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $consumableGood = ConsumableGood::create([
            'name' => $request->name,
            'balance' => $balance,
            'status' => 1,
        ]);

        \App\Models\MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'consumable_good',
                'master_id' => $consumableGood->id,
                'financial_year' => \App\Models\MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($data['balance'] ?? 0),
                'balance_type' => $data['balance_type'] ?? 'Credit',
            ]
        );

        return true;
    }

    public function edit(Request $request)
    {
        return ConsumableGood::with('currentOpeningBalance')->find($request->id);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $balance = $data['balance'] ?? 0;
        if (($data['balance_type'] ?? '') == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $consumableGood = ConsumableGood::find($request->id);
        $consumableGood->update([
            'name' => $request->name,
            'balance' => $balance,
        ]);

        \App\Models\MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'consumable_good',
                'master_id' => $consumableGood->id,
                'financial_year' => \App\Models\MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($data['balance'] ?? 0),
                'balance_type' => $data['balance_type'] ?? 'Credit',
            ]
        );

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
