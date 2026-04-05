<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCapital extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'payment_method_type',
        'payment_method_id',
        'transaction_date',
        'remarks',
    ];

    public function paymentMethod()
    {
        if ($this->payment_method_type == 'Bank') {
            return $this->belongsTo(BankAccount::class, 'payment_method_id');
        } else {
            return $this->belongsTo(CashPayment::class, 'payment_method_id');
        }
    }
}
