<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductItem extends Model
{
    use HasFactory;
    protected $table= 'order_product_items';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'item_sku',
        'quantity',
        'order_quantity',
        'total_item_quantity',
        'pending_quantity',
        'status',  // 0- Pending, 1 : In Progress , 2: Completed
        'created_at',
        'updated_at'
    ];
    
}
