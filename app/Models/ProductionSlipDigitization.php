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
        'bill_number',
        'total_pieces',
        'save_type',
        'slip_file',
        'order_product_set_id',
        'remarks',
        'status',
        'type',
        'created_at',
        'updated_at'
    ];

    // public function getUnitMaster(){
    //     return $this->hasOne('App\Models\StageMasterUnit','id','stage_master_unit_id');
    // }

    public function getUnitMaster()
    {
        return $this->belongsTo(
            'App\Models\StageMasterUnit',
            'stage_master_unit_id',
            'id'
        );
    }

    public function fromStage()
    {
        return $this->belongsTo(\App\Models\MasterProductStage::class, 'from_stage_id', 'id');
    }

    public function toStage()
    {
        return $this->belongsTo(\App\Models\MasterProductStage::class, 'to_stage_id', 'id');
    }

    public function packingMain()
    {
        return $this->hasOne('App\Models\PackingMain', 'slip_id', 'id');
    }

    public function orderLots()
    {
        return $this->hasMany(\App\Models\OrderLot::class, 'production_slip_digitization_id', 'id');
    }

    public function orderStageTransaction()
    {
        return $this->hasMany(\App\Models\OrderStageTransaction::class, 'production_slip_digitization_id', 'id');
    }

    public function orderPrintingStageTransaction()
    {
        return $this->hasMany(\App\Models\OrderPrintingStageTransaction::class, 'production_slip_digitization_id', 'id');
    }

    public function orderPrintingToStichingTransaction()
    {
        return $this->hasMany(\App\Models\OrderPrintingToStichingTransaction::class, 'production_slip_digitization_id', 'id');
    }

    public function orderGodamStageTransaction()
    {
        return $this->hasMany(\App\Models\OrderGodamStageTransaction::class, 'production_slip_digitization_id', 'id');
    }

    public function fabricRollAssignings()
    {
        return $this->hasMany(\App\Models\FabricRollAssigning::class, 'production_slip_digitization_id', 'id');
    }

    public function orderProductSet()
    {
        return $this->belongsTo(OrderProductSet::class, 'order_product_set_id');
    }

    public function parts()
    {
        return $this->hasMany(\App\Models\ProductionSlipDigitizationParts::class, 'production_slip_digitization_id', 'id');
    }

    public function getTotalDigitizedPiecesAttribute()
    {
        // For Cutting stage slip (stage 3)
        if ($this->from_stage_id == 3) {
            $total = 0;
            if ($this->fabricRollAssignings()->exists()) {
                foreach ($this->fabricRollAssignings as $roll) {
                    if ($roll->fabricRollAssigningsDetail) {
                        $total += $roll->fabricRollAssigningsDetail->sum('quantity');
                    }
                }
            }
            if ($total > 0) {
                return $total;
            }
        }

        // For Printing stage slip (stage 1)
        if ($this->from_stage_id == 1) {
            $total = (int)$this->orderPrintingToStichingTransaction()->sum('quantity');
            if ($total > 0) return $total;
            return (int)$this->orderPrintingStageTransaction()->sum('quantity');
        }

        // For Godam stage slip (stage 13)
        if ($this->from_stage_id == 13) {
            return (int)$this->orderGodamStageTransaction()->sum('quantity');
        }

        // For Stitching (stage 4) and other general stages
        if ($this->orderStageTransaction()->exists()) {
            return (int)$this->orderStageTransaction()->sum('quantity');
        }

        // Fallback for general cases
        $total = 0;
        if ($this->fabricRollAssignings()->exists()) {
            foreach ($this->fabricRollAssignings as $roll) {
                if ($roll->fabricRollAssigningsDetail) {
                    $total += $roll->fabricRollAssigningsDetail->sum('quantity');
                }
            }
            if ($total > 0) return $total;
        }

        $total += (int)$this->orderStageTransaction()->sum('quantity');
        $total += (int)$this->orderPrintingStageTransaction()->sum('quantity');
        $total += (int)$this->orderPrintingToStichingTransaction()->sum('quantity');
        $total += (int)$this->orderGodamStageTransaction()->sum('quantity');

        return $total;
    }
}
