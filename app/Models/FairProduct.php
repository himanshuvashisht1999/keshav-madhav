<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'fair_batch_id',
        'product_id',
        'size_set_id',
        'barcode',
        'qrcode',
        'discount_percent',
        'barcode_count'
    ];

    public function batch()
    {
        return $this->belongsTo(FairBatch::class, 'fair_batch_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }
}
