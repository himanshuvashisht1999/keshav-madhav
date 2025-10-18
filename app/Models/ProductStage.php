<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStage extends Model
{
    use HasFactory;
    protected $table= 'product_stages';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'master_product_id',
        'master_stage_id',       
        'status',
        'created_at',
        'updated_at'
    ];
    
}
