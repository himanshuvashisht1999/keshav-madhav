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
        'payment_method_type',
        'payment_method_id',
        'payment_type_id',
        'reference_id',
        'remarks',
        'image',
        'created_by',
    ];

    /**
     * Get the payment method (Bank or Cash).
     */
    public function paymentMethod()
    {
        return $this->morphTo('paymentMethod', 'payment_method_type', 'payment_method_id');
    }

    /**
     * Get the payment type (master category).
     */
    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

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
