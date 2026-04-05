<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitDebit extends Model
{
    use HasFactory;

    protected $table = 'unit_debits';

    protected $fillable = [
        'order_main_id',
        'slip_id',
        'stage_id',
        'unit_id',
        'product_id',
        'color_id',
        'size_id',
        'quantity',
        'amount',
        'remarks'
    ];

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(OrderProductSetDetail::class, 'size_id');
    }

    public function responsibleStage()
    {
        return $this->belongsTo(MasterProductStage::class, 'stage_id');
    }

    public function responsibleUnit()
    {
        return $this->belongsTo(StageMasterUnit::class, 'unit_id');
    }
}
