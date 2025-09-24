<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricOtherImage extends Model
{
    use HasFactory;

    protected $table = 'fabric_other_images';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'main_id',
        'image',
        'created_at',
        'updated_at'
    ];

    public function mainImage()
    {
        return $this->belongsTo('App\Models\MainImage', 'main_id');
    }
}
