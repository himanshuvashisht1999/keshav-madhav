<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryPrice extends Model
{
    protected $table = 'inventory_prices';

    protected $fillable = [
        'design_id',
        'color_id',
        'size_set_id',
        'mrp',
        'selling_price',
        'price',
        'image',
        'status',
    ];

    public function design()
    {
        return $this->belongsTo(ProductionGoods::class, 'design_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }

    public function images()
    {
        return $this->hasMany(InventoryPriceImage::class, 'inventory_price_id');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('uploads/inventory_prices/' . $this->image) : asset('assets/dist/img/no-image.png');
    }
}
