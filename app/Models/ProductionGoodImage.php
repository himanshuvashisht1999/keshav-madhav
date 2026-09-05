<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoodImage extends Model
{
    use HasFactory;
    protected $table= 'production_good_images';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'product_id',
        'title',
        'is_main',
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

    
}
