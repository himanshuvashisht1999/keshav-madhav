<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProductStage extends Model
{
    use HasFactory;
    protected $table= 'master_product_stages';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'status',
        'created_at',
        'updated_at'
    ];

    public function masterProductSubStage()
    {
        return $this->hasMany('App\Models\MasterProductSubStage', 'id', 'master_product_stage_id');
    }
    public function sub_stages(){
        return $this->hasMany('App\Models\MasterProductSubStage','master_product_stage_id','id');
    }
    
}
