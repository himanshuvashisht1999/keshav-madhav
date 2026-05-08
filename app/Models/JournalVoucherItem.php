<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_voucher_id',
        'master_type',
        'master_id',
        'amount',
        'type',
        'narration'
    ];

    public function voucher()
    {
        return $this->belongsTo(JournalVoucher::class, 'journal_voucher_id');
    }

    public function master()
    {
        return $this->morphTo(null, 'master_type', 'master_id');
    }
}
