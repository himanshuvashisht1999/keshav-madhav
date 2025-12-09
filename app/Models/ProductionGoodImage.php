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
        'is_main',
        'image',
        'status',
        'created_at',
        'updated_at'
    ];

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('assets/products/'. $value);
        } else {
            return asset('assets/products/default-image.png');
        }
    }

    
}
