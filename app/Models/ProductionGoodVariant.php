<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoodVariant extends Model
{
    use HasFactory;

    protected $table = 'production_goods_variants';

    protected $fillable = [
        'production_goods_id',
        'master_size_measurement_id',
        'mrp',
        'image',
    ];

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class , 'production_goods_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class , 'master_size_measurement_id');
    }

    public function items()
    {
        return $this->hasMany(ProductionGoodVariantItem::class , 'variant_id');
    }
}
