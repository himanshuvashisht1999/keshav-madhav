<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPatternPart extends Model
{
    use HasFactory;
    protected $table= 'master_pattern_parts';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'part_no',
        'parts_img',
        'is_printing',
        'is_embroidery',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
