<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_voucher_id',
        'item_name',
        'quantity',
        'rate',
        'amount',
    ];

    public function voucher()
    {
        return $this->belongsTo(ConsumableVoucher::class, 'consumable_voucher_id');
    }
}
