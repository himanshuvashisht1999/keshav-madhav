<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\LoanMaster;

class LoanService
{
    public function store(array $data)
    {
        $balance = $data['balance'] ?? 0;
        if ($data['balance_type'] == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }

        return LoanMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);
    }

    public function find($id)
    {
        return LoanMaster::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = LoanMaster::findOrFail($id);

        $balance = $data['balance'] ?? 0;
        if ($data['balance_type'] == 'Debit') {
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
        $item = LoanMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
