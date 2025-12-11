<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateOrderProduct extends Model
{
    use HasFactory;
    protected $table= 'orders';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_main_id',
        'design_id',
        'product_size',
        'color_id',
        'quantity',
        'status',
        'created_at',
        'updated_at'
    ];
    public function products(){
        return $this->hasMany('App\Models\OrderProduct','order_main_id','id');
    }
}
