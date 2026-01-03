<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductSet extends Model
{
    use HasFactory;
    protected $table= 'order_products_sets';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_id',
        'order_main_id',
        'product_sku',
        'bar_code',
        'design_number',
        'set_size',
        'color_id',
        'set_quantity',
        'no_of_pcs',
        'total_quantity',
        'remain_set_quantity',
        'remain_total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    public function cuttingStages()
    {
        return $this->hasMany(OrderCuttingStage::class, 'set_product_id');
    }
    public function order_cutting_stage()
    {
        return $this->hasOne(OrderCuttingStage::class, 'set_product_id');
    }

    public function colors()
    {
        return $this->hasOne(MasterColor::class, 'id', 'color_id');
    }

    public function sizeMeasurement()
    {
        return $this->hasOne(MasterSizeMeasurement::class, 'id', 'set_size')
            ->join(
                'order_products_sets',
                'order_products_sets.set_size',
                '=',
                'master_size_measurements.id'
            )
            ->whereColumn(
                'master_size_measurements.design_number',
                'order_products_sets.design_number'
            )
            ->select('master_size_measurements.*');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id', 'id');
    }
}
