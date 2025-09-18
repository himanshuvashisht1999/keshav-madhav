<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    use HasFactory;
    protected $table= 'fabrics';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'dye_id',
        'width_id',
        'weave_type_id',
        'gsm_id',
        'composition_id',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
