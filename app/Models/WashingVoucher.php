<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashingVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'washing_master_id',
        'order_lot_id',
        'voucher_date',
        'voucher_number',
        'sub_total',
        'gst',
        'other_charges',
        'round_off',
        'total_amount',
        'document',
        'remarks',
        'status',
    ];

    public function washingMaster()
    {
        return $this->belongsTo(WashingMaster::class);
    }

    public function items()
    {
        return $this->hasMany(WashingVoucherItem::class);
    }

    public function orderLot()
    {
        return $this->belongsTo(FabricRollAssigning::class, 'order_lot_id');
    }
}
