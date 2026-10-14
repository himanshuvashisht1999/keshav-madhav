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
                'financial_year' => \App\Models\MasterOpeningBalance::openingKey(),
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

        $washingMaster = WashingMaster::find($request->id);
        if ($washingMaster) {
            // Editing the opening balance moves the running balance by the difference only,
            // so transactions already applied to this account are never wiped out.
            \Illuminate\Support\Facades\DB::transaction(function () use ($washingMaster, $request, $data) {
                $washingMaster->name = $data['name'];
                $washingMaster->status = $request->status ?? 1;
                \App\Models\MasterOpeningBalance::applyOpeningChange($washingMaster, 'washing_master', array_key_exists('balance', $data) ? ($data['balance'] ?? 0) : null, $data['balance_type'] ?? 'Credit');
                $washingMaster->save();
            });

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
