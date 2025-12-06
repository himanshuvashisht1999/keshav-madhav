<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseDetail extends Model
{
    use HasFactory;
    protected $table= 'warehouse_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'from_stage_id',
        'master_warehouse_block_id',
        'lot_no',
        'original_qty',
        'remaining_qty',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    public function from_stage(){
        return $this->hasOne('App\Models\MasterProductStage','id','from_stage_id');
    }
    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id');
    }
    
}
