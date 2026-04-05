<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\Commission as Model;

class CommissionService
{
    public function store($data)
    {
        return Model::create($data);
    }

    public function update($data, $id)
    {
        $model = Model::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }

    public function delete($id)
    {
        $model = Model::find($id);
        if ($model) {
            $model->status = 0;
            $model->save();
            return true;
        }
        return false;
    }

    public function find($id)
    {
        return Model::find($id);
    }
}
