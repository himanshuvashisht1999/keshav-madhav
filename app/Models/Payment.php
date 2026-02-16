<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_category',
        'payment_type',
        'payee_name',
        'party_type',
        'party_id',
        'paymentable_type',
        'paymentable_id',
        'amount',
        'payment_date',
        'payment_mode',
        'reference_id',
        'remarks',
        'image',
        'created_by',
    ];

    /**
     * Get the party that the payment is for (Vendor, SalesAgent, etc.).
     */
    public function party()
    {
        return $this->morphTo();
    }

    /**
     * Get the paymentable model (FabricReceipt, Order, etc.).
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
