<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSizeMeasurement extends Model
{
    use HasFactory;
    protected $table= 'master_size_measurements';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'size_selection_id',
        'measurement',
        'base_cloth_consumption',
        'status',
        'created_at',
        'updated_at'
    ];

    // public function type_master(){
    //     return $this->hasOne('App\Models\TypeMaster','id','type_master_id');
    // }
    
}
