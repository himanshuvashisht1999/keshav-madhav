<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBrandDiscount extends Model
{
    protected $fillable = [
        'customer_id',
        'brand_id',
        'discount_percentage',
    ];

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
