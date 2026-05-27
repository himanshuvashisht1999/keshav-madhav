<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashingVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'washing_voucher_id',
        'order_lot_id',
        'item_name',
        'quantity',
        'rate',
        'amount',
    ];

    public function voucher()
    {
        return $this->belongsTo(WashingVoucher::class);
    }

    public function orderLot()
    {
        return $this->belongsTo(FabricRollAssigning::class, 'order_lot_id');
    }
}
