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
        return FactoryHeadMaster::with('currentOpeningBalance')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = FactoryHeadMaster::findOrFail($id);

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
                'master_type' => 'factory_head',
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
        $item = FactoryHeadMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
