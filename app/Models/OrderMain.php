<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMain extends Model
{
    use HasFactory;
    protected $table= 'order_main';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_type',
        'expected_delivery_date',
        'master_customer_id',
        'total_amount',
        'status',
        'created_at',
        'updated_at'
    ];

    public function customer(){
        return $this->hasOne('App\Models\MasterCustomer','id','master_customer_id');
    }
    public function orders(){
        return $this->hasMany('App\Models\Order','order_main_id','id');
    }
    public function order_products(){
        return $this->hasMany('App\Models\OrderProduct','order_main_id','id');
    }
    public function packages()
    {
        return $this->hasMany(Package::class, 'order_main_id');
    }

    public function package(){
        return $this->hasOne('App\Models\Package','order_main_id','id');
    }

    public function OrderProductSets()
    {
        return $this->hasMany('App\Models\OrderProductSet', 'order_main_id', 'id');
    }

    public function dispatchCartons()
    {
        return $this->hasMany(PackingCarton::class, 'main_order_id', 'id');
    }
    
}
