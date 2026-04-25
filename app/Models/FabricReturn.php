<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'fabric_receipt_id',
        'date',
        'total_amount',
        'remarks',
        'sub_total',
        'gst_percentage',
        'gst_amount',
        'discount',
        'other_charges'
    ];

    public function receipt()
    {
        return $this->belongsTo(FabricReceipt::class, 'fabric_receipt_id');
    }

    public function details()
    {
        return $this->hasMany(FabricReturnDetail::class, 'fabric_return_id');
    }
}
