<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatch extends Model
{
    use HasFactory;
    protected $table= 'order_dispatch';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'dispatch_date',
        'customer_id',
        'main_order_id',
        'dispatch_by',
        'total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    public function dispatchDetails(){
        return $this->hasMany('App\Models\OrderDispatchDetails','order_dispatch_id','id');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'main_order_id', 'id' );
    }
}
