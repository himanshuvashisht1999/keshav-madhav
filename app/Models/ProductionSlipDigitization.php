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

   
}
