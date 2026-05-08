<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'date',
        'narration',
        'total_debit',
        'total_credit',
        'created_by'
    ];

    public function items()
    {
        return $this->hasMany(JournalVoucherItem::class);
    }
}
