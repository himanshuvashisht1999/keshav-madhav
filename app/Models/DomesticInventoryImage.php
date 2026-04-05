<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticInventoryImage extends Model
{
    use HasFactory;

    protected $table = 'domestic_inventory_images';

    protected $fillable = [
        'product_id',
        'product_name',
        'color_id',
        'size_set_id',
        'fitting_id',
        'pattern_id',
        'image_path',
        'is_main',
        'mrp',
        'selling_price',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }

    public function fitting()
    {
        return $this->belongsTo(MasterProductFitting::class, 'fitting_id');
    }

    public function pattern()
    {
        return $this->belongsTo(MasterDesignPattern::class, 'pattern_id');
    }

    public function getImageUrlAttribute()
    {
        return asset('uploads/inventory_prices/' . $this->image_path);
    }
}
