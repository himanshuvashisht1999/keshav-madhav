<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductSet extends Model
{
    use HasFactory;
    protected $table = 'order_products_sets';
    protected $appends = ['size_set_name', 'color_name', 'fabric_names', 'assigned_fabrics'];
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_id',
        'order_main_id',
        'product_sku',
        'bar_code',
        'production_goods_id',
        'design_number',
        'set_size',
        'color_id',
        'set_quantity',
        'no_of_pcs',
        'total_quantity',
        'remain_set_quantity',
        'remain_total_quantity',
        'basic_amount',
        'gst',
        'total_amount',
        'stage_master_unit_id',
        'fabric_id',
        'master_product_fitting_id',
        'master_design_pattern_id',
        'remark',
        'is_printing',
        'printing_unit_id',
        'status',
        'start_date',
        'end_date',
        'complete_date',
        'created_at',
        'updated_at'
    ];

    public function getFabricNamesAttribute()
    {
        if (!$this->fabric_id)
            return '-';
        $ids = explode(',', $this->fabric_id);
        return \App\Models\Fabric::whereIn('id', $ids)->pluck('name')->implode(', ');
    }


    public function getAssignedFabricsAttribute()
    {
        $fabricIds = [];
        if ($this->fabric_id) {
            $fabricIds = array_filter(array_map('trim', explode(',', $this->fabric_id)));
        }

        if (empty($fabricIds) && $this->orderCuttingStages()->exists()) {
            $stages = $this->orderCuttingStages()->get();
            foreach ($stages as $osc) {
                if ($osc->fabric_id) {
                    $oscIds = array_filter(array_map('trim', explode(',', $osc->fabric_id)));
                    $fabricIds = array_merge($fabricIds, $oscIds);
                }
            }
            $fabricIds = array_unique($fabricIds);
        }

        if (empty($fabricIds))
            return collect();

        return \App\Models\Fabric::whereIn('id', $fabricIds)->with('receiptDetails')->get();
    }
    public function cuttingStages()
    {
        return $this->hasMany(OrderCuttingStage::class, 'set_product_id');
    }
    public function orderCuttingStages()
    {
        return $this->hasMany(OrderCuttingStage::class, 'set_product_id');
    }
    public function order_cutting_stage()
    {
        return $this->hasOne(OrderCuttingStage::class, 'set_product_id');
    }
    public function stage_master_unit()
    {
        return $this->belongsTo(StageMasterUnit::class, 'stage_master_unit_id');
    }
    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }
    public function master_design_pattern()
    {
        return $this->belongsTo(MasterDesignPattern::class, 'master_design_pattern_id');
    }

    public function colors()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }
    public function master_product_fitting()
    {
        return $this->belongsTo(MasterProductFitting::class, 'master_product_fitting_id');
    }
    public function size_measurement()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'set_size');
    }

    public function sizeMeasurement()
    {
        return $this->hasOne(MasterSizeMeasurement::class, 'id', 'set_size')
            ->join(
                'order_products_sets',
                'order_products_sets.set_size',
                '=',
                'master_size_measurements.id'
            )
            ->whereColumn(
                'master_size_measurements.design_number',
                'order_products_sets.design_number'
            )
            ->select('master_size_measurements.*');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'production_goods_id');
    }

    public function product_set_details()
    {
        return $this->hasMany(OrderProductSetDetail::class, 'order_products_set_id');
    }

    public function getSizeSetNameAttribute()
    {
        return $this->size_measurement ? $this->size_measurement->name : 'N/A';
    }

    public function getColorNameAttribute()
    {
        return $this->colors ? $this->colors->name : 'N/A';
    }
    public function lots()
    {
        return $this->hasMany(FabricRollAssigning::class, 'order_products_set_id');
    }
}
