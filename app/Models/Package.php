<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $table= 'packages';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_main_id',
        'status',
        'created_at',
        'updated_at'
    ];
    
    public function order(){
        return $this->hasOne('App\Models\OrderMain','id','order_main_id');
    }
    public function package_boxes(){
        return $this->hasMany('App\Models\PackageBox','package_id','id');
    }
    
}
