<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionSlipDigitization extends Model
{
    use HasFactory;
    protected $table = 'production_slip_digitization';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'from_stage_id',
        'to_stage_id',
        'stage_master_unit_id',
        'lot_no',
        'slip_file',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    // public function getUnitMaster(){
    //     return $this->hasOne('App\Models\StageMasterUnit','id','stage_master_unit_id');
    // }

    public function getUnitMaster()
    {
        return $this->belongsTo(
            'App\Models\StageMasterUnit',
            'stage_master_unit_id',
            'id'
        );
    }

    public function fromStage()
    {
        return $this->belongsTo(\App\Models\MasterProductStage::class, 'from_stage_id', 'id');
    }
   
    public function packingMain()
    {
        return $this->hasOne('App\Models\PackingMain', 'slip_id', 'id');
    }

    public function fabricRollAssignings()
    {
        return $this->hasMany(\App\Models\FabricRollAssigning::class, 'production_slip_digitization_id', 'id');
    }
}
