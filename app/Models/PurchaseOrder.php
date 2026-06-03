<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table= 'purchase_orders';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'master_company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'date',
        'vendor_id',
        'delivery_date',
        'fabric_warehouse_id',
        'remark',
        'transport',
        'is_notify',
        'status',
        'is_closed',
        'created_by',
        'created_at',
        'updated_at'
    ];
    public function company(){
        return $this->hasOne('App\Models\Company','id','master_company_id');
    }
    public function vendor(){
        return $this->hasOne('App\Models\Vendor','id','vendor_id');
    }
    public function items(){
        return $this->hasMany('App\Models\PurchaseOrderItem','purchase_order_id','id');
    }
    public function fabric_warehouse(){
        return $this->hasOne('App\Models\MasterFabricWarehouse','id','fabric_warehouse_id');
    }


    
}
