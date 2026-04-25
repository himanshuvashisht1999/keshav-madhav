<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_order_return_id',
        'item_type',
        'item_id',
        'quantity',
        'price',
        'subtotal',
        'tax_amount',
        'total'
    ];

    public function returnHeader()
    {
        return $this->belongsTo(AgentOrderReturn::class, 'agent_order_return_id');
    }

    public function originalItem()
    {
        if ($this->item_type === 'standard') {
            return $this->belongsTo(AgentOrderItem::class, 'item_id');
        } else {
            return $this->belongsTo(AgentOrderFabricItem::class, 'item_id');
        }
    }
}
