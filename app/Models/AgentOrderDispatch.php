<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderDispatch extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $fillable = [
        'master_customer_id',
        'sales_agent_id',
        'dispatch_date',
        'lr_no',
        'transport_name',
        'total_amount',
        'gst_amount',
        'grand_total',
        'status',
        'created_by'
    ];

    public function orders()
    {
        return $this->belongsToMany(AgentOrder::class, 'agent_order_dispatch_items', 'agent_order_dispatch_id', 'agent_order_id');
    }

    public function dispatchItems()
    {
        return $this->hasMany(AgentOrderDispatchItem::class, 'agent_order_dispatch_id');
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
