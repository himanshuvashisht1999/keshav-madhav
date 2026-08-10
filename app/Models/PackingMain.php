<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingMain extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $fillable = [
        'order_main_id', 'slip_id', 'packing_date', 'remarks', 'status', 'created_by'
    ];

    public function order()
    {
        return $this->belongsTo(OrderMain::class , 'order_main_id');
    }

    public function cartons()
    {
        return $this->hasMany(PackingCarton::class , 'packing_main_id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class , 'packing_main_id');
    }

    public function outflows()
    {
        return $this->hasMany(ProductionOutflowInventory::class, 'slip_id', 'slip_id');
    }
}
