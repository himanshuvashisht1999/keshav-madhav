<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricUnit extends Model
{
    use HasFactory;
    protected $table= 'fabric_unit';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'symbol',
        'name',
        'status',
        'created_at',
        'updated_at'
    ];
}
