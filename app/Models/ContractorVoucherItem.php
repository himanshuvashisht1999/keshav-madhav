<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_voucher_id',
        'item_name',
        'quantity',
        'rate',
        'amount',
    ];

    public function voucher()
    {
        return $this->belongsTo(ContractorVoucher::class);
    }
}
