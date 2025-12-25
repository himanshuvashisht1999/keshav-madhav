<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductDesign extends Model
{
    use HasFactory;
    protected $table= 'order_product_designs';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_main_id',
        'order_products_set_id',
        'order_cutting_stage_id',
        'design_number',
        'status',
        'created_at',
        'updated_at'
    ];


    
}
