<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageMasterUnit extends Model
{
    use HasFactory;
    protected $table = 'stage_master_units';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'master_fabric_warehouse_id',
        'master_stage_id',
        'name',
        'phone',
        'status',
        'employee_id',
        'password',
        'created_at',
        'updated_at'
    ];

    public function masterStage()
    {
        return $this->hasOne('App\Models\MasterProductStage', 'id', 'master_stage_id');
    }

    public function masterFabricWarehouse()
    {
        return $this->belongsTo('App\Models\MasterFabricWarehouse', 'master_fabric_warehouse_id', 'id');
    }

}
