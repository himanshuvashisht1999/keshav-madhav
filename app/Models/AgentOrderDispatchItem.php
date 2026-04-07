<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderDispatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_order_dispatch_id',
        'agent_order_id'
    ];

    public function order()
    {
        return $this->belongsTo(AgentOrder::class, 'agent_order_id');
    }

    public function dispatch()
    {
        return $this->belongsTo(AgentOrderDispatch::class, 'agent_order_dispatch_id');
    }
}
