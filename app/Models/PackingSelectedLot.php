<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingSelectedLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_main_id',
        'slip_id',
        'lot_no'
    ];

    public function packingMain()
    {
        return $this->belongsTo(PackingMain::class, 'packing_main_id');
    }
}
