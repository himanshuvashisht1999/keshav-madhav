<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_agent_id',
        'master_customer_id',
        'total_qty',
        'total_amount',
        'gst_percentage',
        'gst_amount',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'status',
        'order_date'
    ];

    public function items()
    {
        return $this->hasMany(AgentOrderItem::class, 'agent_order_id');
    }

    public function shop()
    {
        return $this->belongsTo(MasterCustomer::class, 'master_customer_id');
    }

    public function agent()
    {
        return $this->belongsTo(SalesAgent::class, 'sales_agent_id');
    }
}
