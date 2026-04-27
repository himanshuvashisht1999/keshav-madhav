<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_batch_id',
        'product_id',
        'size_set_id',
        'barcode',
        'qrcode',
        'discount_percent'
    ];

    public function batch()
    {
        return $this->belongsTo(SampleBatch::class, 'sample_batch_id');
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
