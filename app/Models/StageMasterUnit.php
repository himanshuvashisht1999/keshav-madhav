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
        'created_at',
        'updated_at'
    ];

    // public function fabric(){
    //     return $this->hasOne('App\Models\Fabric','sku','fabric_sku');
    // }
   
}
