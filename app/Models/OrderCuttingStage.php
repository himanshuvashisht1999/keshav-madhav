<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCuttingStage extends Model
{
    use HasFactory;
    protected $table= 'order_cutting_stage';
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
        'status',
        'created_at',
        'updated_at'
    ];

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

    public function productSet()
    {
        return $this->belongsTo('App\Models\OrderProductSet', 'set_product_id');
    }

    public function cuttingMaster()
    {
        return $this->belongsTo('App\Models\MasterFabricWarehouse', 'to_assign_id');
    }

}
