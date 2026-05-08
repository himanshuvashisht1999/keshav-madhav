<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCuttingStage extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table= 'order_cutting_stage';
    protected $appends = ['fabric_names'];
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_main_id',
        'from_assign_id',
        'to_assign_id',
        'vendor_id',
        'customer_id',
        'is_po',
        'rate',
        'set_product_id',
        'lot_no',
        'fabric_id',
        'master_fitting_id',
        'master_pattern_id',    
        'quantity',
        'remaining_quantity',
        'till_allowed_time',
        'time_type',
        'allowed_time',
        'processed_by',
        'remarks',
        'belt',
        'production_po_id',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];

    public function productionPO()
    {
        return $this->belongsTo(ProductionPO::class, 'production_po_id');
    }

    // public function from_stage(){
    //     return $this->hasOne('App\Models\MasterProductStage','id','from_stage_id');
    // }
    // public function to_stage(){
    //     return $this->hasOne('App\Models\MasterProductStage','id','to_stage_id');
    // }
    // public function orderProduct()
    // {
    //     return $this->belongsTo(OrderProduct::class, 'order_product_id');
    // }
    // public function warehouseDetails(){
    //     return $this->hasOne('App\Models\MasterFabricWarehouse','id','to_assign_id');
    // }
    public function cutting_master(){
        return $this->hasOne('App\Models\StageMasterUnit','id','to_assign_id');
    }
    public function fabric(){
        return $this->hasOne('App\Models\Fabric','id','fabric_id');
    }
    public function pattern(){
        return $this->hasOne('App\Models\MasterDesignPattern','id','master_pattern_id');
    }
    public function master_fitting(){
        return $this->hasOne('App\Models\MasterProductFitting','id','master_fitting_id');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor','vendor_id','id');
    }
    public function customer(){
        return $this->belongsTo('App\Models\MasterCustomer','customer_id','id');
    }

    public function productSet()
    {
        return $this->belongsTo('App\Models\OrderProductSet', 'set_product_id');
    }

    public function orderMain()
    {
        return $this->belongsTo('App\Models\OrderMain', 'order_main_id');
    }

    public function getFabricNamesAttribute()
    {
        if (!$this->fabric_id) return '-';
        $ids = explode(',', $this->fabric_id);
        return \App\Models\Fabric::whereIn('id', $ids)->pluck('name')->implode(', ');
    }
}

