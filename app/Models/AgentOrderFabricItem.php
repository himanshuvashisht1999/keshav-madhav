<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderFabricItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_order_id',
        'fabric_id',
        'fabric_receipt_detail_id',
        'meter',
        'selling_price',
        'agent_order_dispatch_id'
    ];

    public function order()
    {
        return $this->belongsTo(AgentOrder::class, 'agent_order_id');
    }

    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }

    public function roll()
    {
        return $this->belongsTo(FabricReceiptDetail::class, 'fabric_receipt_detail_id');
    }
}
