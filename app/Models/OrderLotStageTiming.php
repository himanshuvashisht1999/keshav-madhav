<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLotStageTiming extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_lot_id',
        'lot_no',
        'master_stage_id',
        'unit_id',
        'days_allocated',
        'start_date',
        'end_date',
        'complete_date',
        'remarks',
        'status'
    ];

    public function orderLot()
    {
        return $this->belongsTo(OrderLot::class, 'order_lot_id');
    }

    public function stage()
    {
        return $this->belongsTo(MasterProductStage::class, 'master_stage_id');
    }

    public function unit()
    {
        return $this->belongsTo(StageMasterUnit::class, 'unit_id');
    }
}
