<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\HulayatiMaster;

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

        return HulayatiMaster::create([
            'name' => $data['name'],
            'balance' => $balance,
            'status' => 1,
        ]);
    }

    public function find($id)
    {
        return HulayatiMaster::findOrFail($id);
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

        return $item->update([
            'name' => $data['name'],
            'balance' => $balance,
        ]);
    }

    public function delete($id)
    {
        $item = HulayatiMaster::findOrFail($id);
        return $item->update(['status' => 0]); // Soft deactivate
    }
}
