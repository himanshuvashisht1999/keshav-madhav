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
        'discount_amount',
        'gst_amount',
        'gst_percentage',
        'grand_total',
        'status',
        'created_by'
    ];

    protected $appends = ['paid_amount', 'balance_amount'];

    public function orders()
    {
        return $this->belongsToMany(AgentOrder::class, 'agent_order_dispatch_items', 'agent_order_dispatch_id', 'agent_order_id');
    }

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        return ($this->grand_total ?? 0) - $this->paid_amount;
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
