<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamplingInventory extends Model
{
    use HasFactory;

    protected $table = 'sampling_inventories';

    protected $fillable = [
        'order_main_id',
        'rack_id',
        'product_id',
        'color_id',

        'size_id',
        'quantity',
        'barcode',
        'remarks'
    ];

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(OrderProductSetDetail::class, 'size_id');
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }
}
