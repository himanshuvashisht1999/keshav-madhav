<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by',
    ];

    public function products()
    {
        return $this->hasMany(ProductionGoods::class, 'brand_id');
    }
}
