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

    
}
