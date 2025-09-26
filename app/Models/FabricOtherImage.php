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
        'fabric_id',
        'image',
        'status',
        'created_at',
        'updated_at'
    ];
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('assets/fabric/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }


}
