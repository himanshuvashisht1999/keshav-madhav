<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticInventoryPurchase extends Model
{
    use HasFactory;

    protected $table = 'domestic_inventory_purchases';

    protected $fillable = [
        'vendor_id',
        'customer_id',
        'production_po_id',
        'user_id',
        'purchase_date',
        'sub_total',
        'gst_type',
        'gst_value',
        'gst',
        'other_amount',
        'discount',
        'total_amount',
        'remarks'
    ];

    public function productionPO()
    {
        return $this->belongsTo(ProductionPO::class, 'production_po_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(DomesticInventoryHistory::class, 'purchase_id');
    }
}
