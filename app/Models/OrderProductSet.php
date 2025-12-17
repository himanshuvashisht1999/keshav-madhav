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
        'design_number',
        'set_size',
        'color_id',
        'set_quantity',
        'no_of_pcs',
        'total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    public function cuttingStages()
    {
        return $this->hasMany(OrderCuttingStage::class, 'set_product_id');
    }

    public function colors()
    {
        return $this->hasOne(MasterColor::class, 'id', 'color_id');
    }
}
