<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterWarehouse extends Model
{
    use HasFactory;
    protected $table= 'master_warehouse';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'address',
        'status',
        'created_at',
        'updated_at'
    ];
    public function blocks(){
        return $this->hasMany('App\Models\MasterWarehouseBlock','master_warehouse_id','id');
    }
    
}
