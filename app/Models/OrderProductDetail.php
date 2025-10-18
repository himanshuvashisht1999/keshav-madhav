<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductDetail extends Model
{
    use HasFactory;
    protected $table= 'order_product_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'product_sku',
        'fabric_sku',
        'meter',
        'order_quantity',
        'total_meter',
        'status',
        'created_at',
        'updated_at'
    ];
    public function product_detail_stocks(){
        return $this->hasMany('App\Models\OrderProductDetailStock','order_product_detail_id','id');
    }
    public function fabric_stocks(){
        return $this->hasMany('App\Models\Stock','sku','fabric_sku');
    }
    
}
