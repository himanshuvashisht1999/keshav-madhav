<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPO extends Model
{
    use HasFactory;

    protected $table = 'production_pos';

    protected $fillable = [
        'po_number',
        'order_main_id',
        'vendor_id',
        'customer_id',
        'delivery_date',
        'remark',
        'status'
    ];

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id');
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
        return $this->hasMany(OrderCuttingStage::class, 'production_po_id');
    }
}
