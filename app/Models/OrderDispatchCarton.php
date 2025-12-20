<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatchCarton extends Model
{
    use HasFactory;
    protected $table= 'order_dispatch_cartons';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'customer_id',
        'main_order_id',
        'carton_details_id',
        'total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    
}
