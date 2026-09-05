<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = [
        'id',
        'product_id',
        'title',
        'image',
        'status',
        'created_at',
        'updated_at'
    ];

    public function getImageAttribute($value)
    {
        if ($value) {
            if (file_exists(public_path('product/' . $value))) {
                return asset('product/' . $value);
            }
            if (file_exists(public_path('assets/products/' . $value))) {
                return asset('assets/products/' . $value);
            }
            return asset('product/' . $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }
}
