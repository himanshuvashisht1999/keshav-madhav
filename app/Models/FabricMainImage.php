<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricMainImage extends Model
{
    use HasFactory;

    protected $table = 'fabric_main_images';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'fabric_sku',
        'image',
        'status',
        'updated_at',
        'created_at'
    ];

    public function otherImages()
    {
        return $this->hasMany('App\Models\OtherImage', 'main_id');
    }
}
