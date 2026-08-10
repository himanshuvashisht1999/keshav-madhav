<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PackingMain;

use App\Models\PackingItem;

class PackingCarton extends Model
{
    use HasFactory;
    protected $table= 'packing_cartons';
    protected $fillable = [
        'id',
        'packing_main_id',
        'carton_no',
        'rack_id',
        'barcode',
        'weight',
        'dimensions',
        'note'
    ];

    public function main()
    {
        return $this->belongsTo(PackingMain::class, 'packing_main_id');
    }
    
    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class, 'packing_carton_id');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'main_order_id', 'id' );
    }

    public function cartonsSession()
    {
        return $this->belongsTo(CartonPackingSession::class, 'carton_packing_session_id', 'id' );
    }
}
