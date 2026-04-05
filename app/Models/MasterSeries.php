<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSeries extends Model
{
    use HasFactory;
    protected $table = 'master_series';
    protected $fillable = [
        'id', 'sno', 'company_id', 'sub_company_id', 'project_id',
        'sku', 'name', 'status', 'created_at', 'updated_at'
    ];
}