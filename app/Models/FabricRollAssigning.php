<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FabricRollAssigning extends Model
{
    use HasFactory;
    protected $table = 'production_fabric_roll_assigning';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'lot_no',
        'order_no',
        'stage_master_unit_id',
        'roll_no',
        'meter',
        'slip_create_date_time',
        'slip_file',
        'status',
        'created_at',
        'updated_at'
    ];
}
