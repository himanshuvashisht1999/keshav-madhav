<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProductSubStage extends Model
{
    use HasFactory;
    protected $table= 'master_product_sub_stages';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'master_product_stage_id',
        'name',
        'status',
        'created_at',
        'updated_at'
    ];

    
}
