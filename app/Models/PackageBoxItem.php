<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageBoxItem extends Model
{
    use HasFactory;
    protected $table= 'package_box_items';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'package_box_id',
        'product_sku',
        'status',
        'created_at',
        'updated_at'
    ];
    
    public function product(){
        return $this->hasOne('App\Models\ProductionGoods','sku','product_sku');
    }
    
}
