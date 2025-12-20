<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStageTracking extends Model
{
    use HasFactory;
    protected $table= 'order_stage_tracking';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'lot_no',
        'master_product_stage_id',
        'stage_name',
        'expected_time',
        'status',
        'created_at',
        'updated_at'
    ];

    
}
