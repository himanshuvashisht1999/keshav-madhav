<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeadStockInventory extends Model
{
    use HasFactory;

    protected $table = 'dead_stock_inventories';

    protected $fillable = [
        'order_main_id',
        'rack_id',
        'product_id',
        'color_id',
        'fitting_id',
        'pattern_id',
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

    public function fitting()
    {
        return $this->belongsTo(MasterProductFitting::class, 'fitting_id');
    }

    public function pattern()
    {
        return $this->belongsTo(MasterDesignPattern::class, 'pattern_id');
    }

    public function size()
    {
        return $this->belongsTo(OrderProductSetDetail::class, 'size_id');
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    // Accessors
    public function getProductNameAttribute()
    {
        return $this->product->name_of_garment ?? 'N/A';
    }

    public function getDesignNumberAttribute()
    {
        return $this->product->design_number ?? 'N/A';
    }

    public function getColorNameAttribute()
    {
        return $this->color->name ?? 'N/A';
    }

    public function getSizeNameAttribute()
    {
        return $this->size->size ?? 'N/A';
    }
}
