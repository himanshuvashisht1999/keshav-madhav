<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $table= 'vendors';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'phone', 
        'email', 
        'address',
        'items',
        'status',
        'created_at',
        'updated_at'
    ];

    public function fabrics(){
        return $this->hasMany('App\Models\Fabric','vendor_id','id');
    }
    
}
