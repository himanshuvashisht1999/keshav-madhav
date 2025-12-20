<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStageWiseTimeTracking extends Model
{
    use HasFactory;
    protected $table= 'order_stage_wise_time_tracking';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'lot_no',
        'stage_id_1',
        'stage_id_2',
        'stage_id_3',
        'stage_id_4',
        'stage_id_5',
        'stage_id_6',
        'stage_id_7',
        'stage_id_8',
        'stage_id_9',
        'stage_id_10',
        'stage_id_11',
        'stage_id_12',
        'status',
        'created_at',
        'updated_at'
    ];

    
}
