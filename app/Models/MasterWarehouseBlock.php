<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterWarehouseBlock extends Model
{
    use HasFactory;
    protected $table= 'master_warehouse_blocks';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'master_warehouse_id',
        'project_id',
        'sku',
        'name',
        'status',
        'created_at',
        'updated_at'
    ];

    public function masterWarehouse(){
        return $this->belongsTo(MasterWarehouse::class, 'master_warehouse_id', 'id');
    }
    public function getMasterWarehouseWithRacks(){
        return MasterWarehouseBlock::with('masterWarehouse')->where('status',1)->get();
    }

}
