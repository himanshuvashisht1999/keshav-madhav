<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrder extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $fillable = [
        'sales_agent_id',
        'party_type',
        'master_customer_id',
        'master_vendor_id',
        'total_qty',
        'total_amount',
        'gst_percentage',
        'gst_amount',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'status',
        'order_type',
        'sale_type',
        'expected_dispatch_date',
        'created_by',
        'order_date',
        'remark',
        'booking_station',
        'transport',
        'other_charges'
    ];

    protected $appends = ['paid_amount', 'balance_amount'];

    public function items()
    {
        return $this->hasMany(AgentOrderItem::class, 'agent_order_id');
    }

    public function fabricItems()
    {
        return $this->hasMany(AgentOrderFabricItem::class, 'agent_order_id');
    }

    public function shop()
    {
        return $this->belongsTo(MasterCustomer::class, 'master_customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'master_vendor_id');
    }

    public function party()
    {
        if ($this->party_type === 'vendor') {
            return $this->vendor();
        }
        return $this->shop();
    }

    public function agent()
    {
        return $this->belongsTo(SalesAgent::class, 'sales_agent_id');
    }

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        return $this->grand_total - $this->paid_amount;
    }

    public function dispatches()
    {
        return $this->belongsToMany(AgentOrderDispatch::class, 'agent_order_dispatch_items', 'agent_order_id', 'agent_order_dispatch_id');
    }
}
