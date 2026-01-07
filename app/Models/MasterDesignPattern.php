<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDesignPattern extends Model
{
    use HasFactory;
    protected $table= 'master_design_patterns';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'pattern_img',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
