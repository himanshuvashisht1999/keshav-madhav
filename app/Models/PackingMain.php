<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingMain extends Model
{
    protected $fillable = [
        'order_main_id', 'slip_id', 'packing_date', 'remarks', 'status'
    ];

    public function order()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id');
    }

    public function cartons()
    {
        return $this->hasMany(PackingCarton::class, 'packing_main_id');
    }

    public function boxes()
    {
        return $this->hasMany(PackingBox::class, 'packing_main_id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class, 'packing_main_id');
    }
}
