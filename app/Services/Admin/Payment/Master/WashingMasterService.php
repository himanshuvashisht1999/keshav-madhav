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
        $data = $request->validated();
        $balance = $data['balance'] ?? 0;
        if (($data['balance_type'] ?? '') == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $item = WashingMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);

        \App\Models\MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'washing_master',
                'master_id' => $item->id,
                'financial_year' => \App\Models\MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($data['balance'] ?? 0),
                'balance_type' => $data['balance_type'] ?? 'Credit',
            ]
        );

        return $item;
    }

    public function edit(Request $request)
    {
        return WashingMaster::with('currentOpeningBalance')->find($request->id);
    }

    public function update($request)
    {
        $data = $request->validated();
        $balance = $data['balance'] ?? 0;
        if (($data['balance_type'] ?? '') == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $washingMaster = WashingMaster::find($request->id);
        if ($washingMaster) {
            $washingMaster->update([
                'name' => $data['name'],
                'balance' => $balance,
                'status' => $request->status ?? 1,
            ]);

            \App\Models\MasterOpeningBalance::updateOrCreate(
                [
                    'master_type' => 'washing_master',
                    'master_id' => $washingMaster->id,
                    'financial_year' => \App\Models\MasterOpeningBalance::getCurrentFinancialYear(),
                ],
                [
                    'amount' => abs($data['balance'] ?? 0),
                    'balance_type' => $data['balance_type'] ?? 'Credit',
                ]
            );

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
