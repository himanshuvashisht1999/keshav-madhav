<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_order_id',
        'packing_box_id',
        'box_no',
        'carton_no',
        'product_id',
        'color_id',
        'size_id',
        'size_set_id',
        'product_name',
        'design_number',
        'color_name',
        'size_name',
        'size_set_name',
        'quantity',
        'mrp',
        'selling_price',
        'barcode',
        'qrcode'
    ];

    public function order()
    {
        return $this->belongsTo(AgentOrder::class, 'agent_order_id');
    }
}
