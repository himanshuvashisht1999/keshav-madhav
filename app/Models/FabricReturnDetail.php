<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'fabric_return_id',
        'fabric_receipt_detail_id',
        'fabric_id',
        'return_meter',
        'price_per_meter'
    ];

    public function fabric_return()
    {
        return $this->belongsTo(FabricReturn::class, 'fabric_return_id');
    }

    public function receipt_detail()
    {
        return $this->belongsTo(FabricReceiptDetail::class, 'fabric_receipt_detail_id');
    }

    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }
}
