<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    use HasFactory;
    protected $table= 'master_customers';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
