<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatchPurchase extends Model
{
    use HasFactory;

    protected $table = 'order_dispatch_purchases';

    protected $fillable = [
        'order_dispatch_id',
        'domestic_inventory_purchase_id',
        'purchase_history_id',
        'product_id',
        'color_id',
        'size_set_id',
        'rack_id',
        'boxes_count',
        'pieces_per_box',
        'total_pieces',
        'mrp',
        'selling_price',
        'total_amount',
        'status'
    ];

    public function orderDispatch()
    {
        return $this->belongsTo(OrderDispatch::class, 'order_dispatch_id');
    }

    public function purchase()
    {
        return $this->belongsTo(DomesticInventoryPurchase::class, 'domestic_inventory_purchase_id');
    }

    public function purchaseHistory()
    {
        return $this->belongsTo(DomesticInventoryHistory::class, 'purchase_history_id');
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

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }
}
