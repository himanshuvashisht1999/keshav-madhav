<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductItemTransaction extends Model
{
    use HasFactory;
    protected $table= 'order_product_item_transactions';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'from_stage_id',
        'to_stage_id',
        'sub_stage_id',
        'lot_no',
        'quantity',
        'remaining_quantity',
        'processed_by',
        'remarks',
        'issue_from',
        'issue_to',
        'status',  // 0- Pending, 1 : In Progress , 2: Completed
        'created_at',
        'updated_at'
    ];
    
}
