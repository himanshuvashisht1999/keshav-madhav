<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingBox extends Model
{
    protected $fillable = [
        'packing_main_id', 'packing_carton_id', 'box_no', 'box_type'
    ];

    public function carton()
    {
        return $this->belongsTo(PackingCarton::class, 'packing_carton_id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class, 'packing_box_id');
    }
}
