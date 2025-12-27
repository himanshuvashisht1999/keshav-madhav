<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatchDetails extends Model
{
    use HasFactory;
    protected $table= 'order_dispatch_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_dispatch_id',
        'carton_packing_id',
        'carton_packing_session_id',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
