<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoods extends Model
{
    use HasFactory;
    protected $table= 'production_goods';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name_of_garments',
        'master_material_id',
        'master_color_id',
        'master_size_id',
        'master_design_id',
        'fabric_sku',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
