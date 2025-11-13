<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoodsItem extends Model
{
    use HasFactory;
    protected $table= 'production_good_items';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'product_id',
        'item_sku',
        'item_attribute_value_sku',
        'quantity',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
