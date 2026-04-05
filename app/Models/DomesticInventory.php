<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticInventory extends Model
{
    use HasFactory;

    protected $table = 'domestic_inventories';
    

    protected $fillable = [
        'order_main_id',
        'packing_main_id',
        'packing_carton_id',
        'packing_box_id',
        'rack_id',
        'product_id',
        'color_id',
        'fitting_id',
        'pattern_id',
        'size_set_id',
        'quantity',
        'box_no',
        'carton_no',
        'barcode',
        'status'
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

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }

    public function carton()
    {
        return $this->belongsTo(PackingCarton::class, 'packing_carton_id');
    }

    public function box()
    {
        return $this->belongsTo(PackingBox::class, 'packing_box_id');
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    // Accessors for easier use in labels
    public function getProductNameAttribute()
    {
        $series = $this->product->series->name ?? '';
        $name = $this->product->name_of_garment ?? '';
        return trim($series . ' ' . $name);
    }

    public function getDesignNumberAttribute()
    {
        return $this->product->design_number ?? '';
    }

    public function getFittingNameAttribute()
    {
        return $this->fitting->name ?? '';
    }

    public function getPatternNameAttribute()
    {
        return $this->pattern->name ?? '';
    }

    public function getSizeSetNameAttribute()
    {
        return $this->sizeSet->name ?? '';
    }

    public function getColorNameAttribute()
    {
        return $this->color->name ?? '';
    }

    public function master_pricing()
    {
        return $this->hasOne(DomesticInventoryImage::class, 'product_id', 'product_id')
            ->where('color_id', $this->color_id)
            ->where('size_set_id', $this->size_set_id)
            ->where('fitting_id', $this->fitting_id)
            ->where('pattern_id', $this->pattern_id)
            ->where('is_main', 1);
    }
}
