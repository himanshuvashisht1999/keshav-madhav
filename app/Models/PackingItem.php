<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingItem extends Model
{
    protected $fillable = [
        'packing_main_id', 'packing_carton_id', 'packing_box_id', 'size_id', 'quantity'
    ];

    public function size()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_id');
    }

    public function detail()
    {
        return $this->belongsTo(OrderProductSetDetail::class, 'size_id');
    }
}
