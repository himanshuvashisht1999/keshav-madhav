<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionSlipDigitizationParts extends Model
{
    use HasFactory;
    protected $table = 'production_slip_digitization_parts';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'production_slip_digitization_id',
        'slip_date_time',
        'from_stage_id',
        'from_stage_name',
        'from_unit_id',
        'from_unit_name',
        'to_stage_id',
        'to_stage_name',
        'to_unit_id',
        'to_unit_name',
        'lot_no',
        'design_number',
        'color_id',
        'set_size',
        'single_size',
        'set_quantity',
        'single_quantity',
        'allowed_time',
        'time_type',
        'allowed_till_datetime',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    // public function getUnitMaster(){
    //     return $this->hasOne('App\Models\StageMasterUnit','id','stage_master_unit_id');
    // }

}
