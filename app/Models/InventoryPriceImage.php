<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPriceImage extends Model
{
    use HasFactory;

    protected $table = 'inventory_price_images';

    protected $fillable = [
        'inventory_price_id',
        'image_path',
        'is_main',
        'status'
    ];

    public function inventoryPrice()
    {
        return $this->belongsTo(InventoryPrice::class, 'inventory_price_id');
    }

    public function getImageUrlAttribute()
    {
        return asset('uploads/inventory_prices/' . $this->image_path);
    }
}
