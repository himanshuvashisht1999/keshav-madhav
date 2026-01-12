<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductSetDetail extends Model
{
    use HasFactory;
    protected $table= 'order_products_set_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_products_set_id',
        'size',
        'total_quantity',
        'remaining_quantity',
        'remaining_lot_allocated',
        'status',
        'created_at',
        'updated_at'
    ];

    public function orderProductSet()
    {
        return $this->belongsTo(\App\Models\OrderProductSet::class, 'order_products_set_id');
    }
}
