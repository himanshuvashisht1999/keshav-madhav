<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\CapitalMaster;

class CapitalService
{
    public function store(array $data)
    {
        return CapitalMaster::create([
            'name' => $data['name'],
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
        return $item->update([
            'name' => $data['name'],
        ]);
    }

    public function delete($id)
    {
        $item = CapitalMaster::findOrFail($id);
        return $item->update(['status' => 0]);
    }
}
