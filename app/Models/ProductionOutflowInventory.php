<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOutflowInventory extends Model
{
    use HasFactory;

    protected $table = 'production_outflow_inventories';

    protected $fillable = [
        'type',
        'order_main_id',
        'slip_id',
        'lot_no',
        'rack_id',
        'product_id',
        'color_id',

        'size_id',
        'quantity',
        'per_piece_amount',
        'total_amount',
        'discount',
        'responsible_stage_id',
        'responsible_unit_id',
        'barcode',
        'remarks',
        'status'
    ];

    public function disposeHistory()
    {
        return $this->hasOne(ProductionOutflowDispose::class, 'production_outflow_inventory_id');
    }

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

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function responsibleStage()
    {
        return $this->belongsTo(MasterProductStage::class, 'responsible_stage_id');
    }

    public function responsibleUnit()
    {
        return $this->belongsTo(StageMasterUnit::class, 'responsible_unit_id');
    }

    public function slip()
    {
        return $this->belongsTo(ProductionSlipDigitization::class, 'slip_id');
    }
}
