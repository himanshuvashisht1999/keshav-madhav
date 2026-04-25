<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderReturn extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $fillable = [
        'agent_order_dispatch_id',
        'return_date',
        'total_amount',
        'gst_percentage',
        'discount_amount',
        'discount_percentage',
        'gst_amount',
        'other_charges',
        'grand_total',
        'remark',
        'created_by'
    ];

    public function dispatch()
    {
        return $this->belongsTo(AgentOrderDispatch::class, 'agent_order_dispatch_id');
    }

    public function items()
    {
        return $this->hasMany(AgentOrderReturnItem::class, 'agent_order_return_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
