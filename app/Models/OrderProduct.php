<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $table= 'order_products';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_id',
        'product_sku',
        'quantity',
        'status',
        'created_at',
        'updated_at'
    ];
    public function product_details(){
        return $this->hasMany('App\Models\OrderProductDetail','order_product_id','id');
    }
    public function order_stages(){
        return $this->hasMany('App\Models\OrderProductStage','order_product_id','id');
    }
    public function order_stage_trnsactions(){
        return $this->hasMany('App\Models\OrderStageTransaction','order_product_id','id');
    }

    
}
