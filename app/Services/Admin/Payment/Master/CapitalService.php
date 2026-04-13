<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\CapitalMaster;

class CapitalService
{
    public function store(array $data)
    {
        $balance = $data['opening_balance'] ?? 0;
        $balance_type = $data['balance_type'] ?? 'Credit';

        if ($balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        return CapitalMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);
    }

    public function find($id)
    {
        return CapitalMaster::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = CapitalMaster::findOrFail($id);

        $balance = $data['opening_balance'] ?? 0;
        $balance_type = $data['balance_type'] ?? 'Credit';

        if ($balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        return $item->update([
            'name' => $data['name'],
            'balance' => $balance,
        ]);
    }

    public function delete($id)
    {
        $item = CapitalMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
