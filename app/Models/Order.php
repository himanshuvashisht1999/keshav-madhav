<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
        'expected_delivery_date',
        'master_customer_id',
        'status',
        'created_at',
        'updated_at'
    ];
    public function products(){
        return $this->hasMany('App\Models\OrderProduct','order_id','id');
    }
    public function customer(){
        return $this->hasOne('App\Models\MasterCustomer','id','master_customer_id');
    }
    
}
