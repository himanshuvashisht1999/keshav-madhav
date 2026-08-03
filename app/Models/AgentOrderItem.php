<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_order_id',
        'agent_order_dispatch_id',
        'rack_id',
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
        'fitting_name',
        'pattern_name',
        'quantity',
        'mrp',
        'selling_price',
        'barcode',
        'box_qty',
        'scanned_box_qty',
        'scanned_quantity',
        'qrcode',
        'dispatched_at',
        'agent_order_dispatch_id',
        'remark'
    ];

    public function order()
    {
        return $this->belongsTo(AgentOrder::class, 'agent_order_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }

}
