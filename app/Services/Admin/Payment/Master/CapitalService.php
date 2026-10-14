<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\CapitalMaster;
use App\Models\MasterOpeningBalance;
use Illuminate\Support\Facades\DB;

class CapitalService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = new CapitalMaster(['name' => $data['name'], 'status' => 1]);
            $item->balance = 0;
            $item->save();

            // Save the opening like every other party, so the capital ledger starts from it
            MasterOpeningBalance::applyOpeningChange($item, 'capital', $data['opening_balance'] ?? 0, $data['balance_type'] ?? 'Credit');
            $item->save();

            return $item;
        });
    }

    public function find($id)
    {
        return CapitalMaster::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $item = CapitalMaster::findOrFail($id);

        // Editing the opening balance moves the running balance by the difference only,
        // so transactions already applied to this account are never wiped out.
        return DB::transaction(function () use ($item, $data) {
            $item->name = $data['name'];
            MasterOpeningBalance::applyOpeningChange($item, 'capital', array_key_exists('opening_balance', $data) ? ($data['opening_balance'] ?? 0) : null, $data['balance_type'] ?? 'Credit');
            return $item->save();
        });
    }

    public function delete($id)
    {
        $item = CapitalMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
