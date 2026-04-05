<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\HulayatiMaster;

class HulayatiService
{
    public function store(array $data)
    {
        return HulayatiMaster::create([
            'name' => $data['name'],
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
        return $item->update([
            'name' => $data['name'],
        ]);
    }

    public function delete($id)
    {
        $item = HulayatiMaster::findOrFail($id);
        return $item->update(['status' => 0]); // Soft deactivate
    }
}
