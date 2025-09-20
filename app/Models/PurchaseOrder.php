<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table= 'purchase_orders';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'date',
        'vendor_id',
        'delivery_date',
       
        'status',
        'created_at',
        'updated_at'
    ];
    public function vendor(){
        return $this->hasOne('App\Models\Vendor','id','vendor_id');
    }
    public function items(){
        return $this->hasMany('App\Models\PurchaseOrderItem','purchase_order_id','id');
    }


    
}
