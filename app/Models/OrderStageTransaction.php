<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStageTransaction extends Model
{
    use HasFactory;
    protected $table= 'order_stage_transactions';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'from_stage_id',
        'to_stage_id',
        'sub_stage_id',
        'lot_no',
        'quantity',
        'processed_by',
        'remaining_quantity',
        'remarks',
        'status',
        'created_at',
        'updated_at'
    ];

    public function from_stage(){
        return $this->hasOne('App\Models\MasterProductStage','id','from_stage_id');
    }
    public function to_stage(){
        return $this->hasOne('App\Models\MasterProductStage','id','to_stage_id');
    }
    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id');
    }

    
}
