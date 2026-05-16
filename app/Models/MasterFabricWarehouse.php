<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterFabricWarehouse extends Model
{
    use HasFactory;
    protected $table= 'master_fabric_warehouse';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'cutting_master_name',
        'address',
        'status',
        'created_at',
        'updated_at'
    ];
    public function blocks(){
        return $this->hasMany('App\Models\MasterWarehouseBlock','master_warehouse_id','id');
    }
    public function cuttingStages()
    {
        return $this->hasMany('App\Models\OrderCuttingStage', 'to_assign_id');
    }

    public function cuttingUnits()
    {
        return $this->hasMany('App\Models\StageMasterUnit', 'master_fabric_warehouse_id', 'id');
    }

    public function printingUnits()
    {
        return $this->hasMany('App\Models\StageMasterUnit', 'master_fabric_warehouse_id', 'id');
    }
}
