<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\HulayatiMaster;
use App\Models\MasterOpeningBalance;

class HulayatiService
{
    public function store(array $data)
    {
        $balance = $data['balance'] ?? 0;
        if ($data['balance_type'] == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $item = HulayatiMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'hulayati',
                'master_id' => $item->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($data['balance'] ?? 0),
                'balance_type' => $data['balance_type'] ?? 'Credit',
            ]
        );

        return $item;
    }

    public function find($id)
    {
        return HulayatiMaster::with('currentOpeningBalance')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = HulayatiMaster::findOrFail($id);

        $balance = $data['balance'] ?? 0;
        if ($data['balance_type'] == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $item->update([
            'name' => $data['name'],
            'balance' => $balance,
        ]);

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'hulayati',
                'master_id' => $item->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($data['balance'] ?? 0),
                'balance_type' => $data['balance_type'] ?? 'Credit',
            ]
        );

        return true;
    }

    public function delete($id)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\HulayatiMaster')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$id)->exists();

        if (!$hasAdjustments) {
            \App\Models\MasterOpeningBalance::where('master_type', 'hulayati')
                ->where('master_id', $id)
                ->delete();
                
            $item = \App\Models\HulayatiMaster::findOrFail($id);
            return $item->delete();
        }

        $item = \App\Models\HulayatiMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
