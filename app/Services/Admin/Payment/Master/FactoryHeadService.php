<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\FactoryHeadMaster;
use App\Models\MasterOpeningBalance;

class FactoryHeadService
{
    public function store(array $data)
    {
        $balance = $data['balance'] ?? 0;
        if ($data['balance_type'] == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        $item = FactoryHeadMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'factory_head',
                'master_id' => $item->id,
                'financial_year' => MasterOpeningBalance::openingKey(),
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
        return FactoryHeadMaster::with('currentOpeningBalance')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = FactoryHeadMaster::findOrFail($id);

        // Editing the opening balance moves the running balance by the difference only,
        // so transactions already applied to this account are never wiped out.
        \Illuminate\Support\Facades\DB::transaction(function () use ($item, $data) {
            $item->name = $data['name'];
            MasterOpeningBalance::applyOpeningChange($item, 'factory_head', array_key_exists('balance', $data) ? ($data['balance'] ?? 0) : null, $data['balance_type'] ?? 'Credit');
            $item->save();
        });

        return true;
    }

    public function delete($id)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\FactoryHeadMaster')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$id)->exists();

        if (!$hasAdjustments) {
            \App\Models\MasterOpeningBalance::where('master_type', 'factory_head')
                ->where('master_id', $id)
                ->delete();
                
            $item = \App\Models\FactoryHeadMaster::findOrFail($id);
            return $item->delete();
        }

        $item = \App\Models\FactoryHeadMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
