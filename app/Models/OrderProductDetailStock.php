<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductDetailStock extends Model
{
    use HasFactory;
    protected $table= 'order_product_detail_stocks';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'order_product_detail_id',
        'fabric_stock_id',
        'meter',
        'status',
        'created_at',
        'updated_at'
    ];

    public function stock(){
        return $this->hasOne('App\Models\Stock','id','fabric_stock_id');
    }
    
}
