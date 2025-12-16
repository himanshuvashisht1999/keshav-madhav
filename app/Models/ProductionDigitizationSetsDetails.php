<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionDigitizationSetsDetails extends Model
{
    use HasFactory;
    protected $table = 'production_digitization_sets_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'production_slip_digitization_parts_id',
        'set_size_id',
        'set_qty',
        'size',
        'qauntity',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    // public function getUnitMaster(){
    //     return $this->hasOne('App\Models\StageMasterUnit','id','stage_master_unit_id');
    // }

}
