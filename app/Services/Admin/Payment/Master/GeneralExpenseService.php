<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\GeneralExpense as Model;

class GeneralExpenseService
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
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\GeneralExpense')->pluck('id');
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)->where('ref_id', (string)$id)->exists();

        if (!$hasAdjustments) {
            $model = Model::find($id);
            if ($model) {
                $model->delete();
                return true;
            }
            return false;
        }

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
