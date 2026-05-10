<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStageTransaction extends Model
{
    use HasFactory;
    protected $table = 'order_stage_transactions';
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
        'production_datetime',
        'production_slip_digitization_id',
        'image',
        'status',
        'type',
        'start_date',
        'end_date',
        'complete_date',
        'created_at',
        'updated_at'
    ];

    public function productionSlipDigitization()
    {
        return $this->belongsTo(ProductionSlipDigitization::class, 'production_slip_digitization_id');
    }

    public function from_stage()
    {
        return $this->belongsTo(MasterProductStage::class, 'from_stage_id');
    }
    public function to_stage()
    {
        return $this->belongsTo(MasterProductStage::class, 'to_stage_id');
    }

    public function fromStage()
    {
        return $this->from_stage();
    }
    public function toStage()
    {
        return $this->to_stage();
    }

    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id');
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

    public function fromUnit()
    {
        return $this->getFromUnitMaster();
    }
    public function toUnit()
    {
        return $this->getToUnitMaster();
    }

    public function details()
    {
        return $this->hasMany(OrderStageTransactionDetail::class, 'order_stage_transaction_id', 'id');
    }
}
