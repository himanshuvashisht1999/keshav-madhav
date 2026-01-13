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
        'order_products_set_id',
        'lot_no',
        'order_no',
        'stage_master_unit_id',
        'to_stage_id',
        'roll_no',
        'meter',
        'slip_create_date_time',
        'slip_file',
        'status',
        'created_at',
        'updated_at'
    ];

    public function stageMasterUnit()
    {
        return $this->belongsTo(\App\Models\StageMasterUnit::class, 'stage_master_unit_id');
    }

    public function order_product_set()
    {
        return $this->belongsTo(\App\Models\OrderProductSet::class, 'order_products_set_id');
    }
}
