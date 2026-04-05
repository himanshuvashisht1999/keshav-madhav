<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPrintingStageTransaction extends Model
{
    use HasFactory;
    protected $table= 'order_printing_stage_transactions';
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
        'sub_stage_id_to',
        'lot_no',
        'quantity',
        'processed_by',
        'remaining_quantity',
        'remarks',
        'status',
        'production_datetime',
        'production_slip_digitization_id',
        'image',
        'type',
        'created_at',
        'updated_at'
    ];

    public function productionSlipDigitization()
    {
        return $this->belongsTo(ProductionSlipDigitization::class, 'production_slip_digitization_id');
    }

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

    public function printingDetails()
    {
        return $this->hasMany(OrderPrintingStageTransactionDetail::class, 'order_printing_stage_transaction_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(OrderPrintingStageTransactionDetail::class, 'order_printing_stage_transaction_id', 'id');
    }

    public function getToUnitMaster()
    {
        return $this->belongsTo(
            'App\Models\StageMasterUnit',
            'sub_stage_id_to',
            'id'
        );
    }

    public function getFromUnitMaster()
    {
        return $this->belongsTo(
            'App\Models\StageMasterUnit',
            'sub_stage_id',
            'id'
        );
    }
    
}
