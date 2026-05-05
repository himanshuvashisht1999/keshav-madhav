<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_id',
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

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function items()
    {
        return $this->hasMany(ContractorVoucherItem::class);
    }

    public function orderLot()
    {
        return $this->belongsTo(FabricRollAssigning::class, 'order_lot_id');
    }
}
