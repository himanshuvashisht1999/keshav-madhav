<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageBox extends Model
{
    use HasFactory;
    protected $table= 'package_boxes';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'package_id',
        'order_main_id',
        'quantity',
        'description',
        'warehouse_id',
        'master_warehouse_block_id',
        'status',
        'created_at',
        'updated_at'
    ];
    
    public function order(){
        return $this->hasOne('App\Models\OrderMain','id','order_main_id');
    }
    public function package_boxes_items(){
        return $this->hasMany('App\Models\PackageBoxItem','package_box_id','id');
    }
    public function warehouse(){
        return $this->hasOne('App\Models\MasterWarehouse','id','warehouse_id');
    }
    public function rack(){
        return $this->hasOne('App\Models\MasterWarehouseBlock','id','master_warehouse_block_id');
    }
    
}
