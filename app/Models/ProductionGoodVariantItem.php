<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoodVariantItem extends Model
{
    use HasFactory;

    protected $table = 'production_goods_variant_colors';

    protected $fillable = [
        'variant_id',
        'master_color_id',
        'barcode',
        'image',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductionGoodVariant::class, 'variant_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'master_color_id');
    }
}
