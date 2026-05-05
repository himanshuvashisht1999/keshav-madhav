<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_good_id',
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

    public function consumableGood()
    {
        return $this->belongsTo(ConsumableGood::class);
    }

    public function items()
    {
        return $this->hasMany(ConsumableVoucherItem::class);
    }
}
