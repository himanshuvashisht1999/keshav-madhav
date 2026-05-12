<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticInventoryHistory extends Model
{
    use HasFactory;

    protected $table = 'domestic_inventory_histories';

    protected $fillable = [
        'user_id',
        'customer_id',
        'vendor_id',
        'old_product_id',
        'old_size_set_id',
        'old_color_id',
        'old_fitting_id',
        'old_pattern_id',
        'old_rack_id',
        'old_warehouse_id',
        'new_product_id',
        'new_size_set_id',
        'new_color_id',
        'new_fitting_id',
        'new_pattern_id',
        'new_rack_id',
        'new_warehouse_id',
        'box_quantity',
        'mrp',
        'pieces_per_box',
        'purchase_rate',
        'purchase_id',
        'type',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function oldProduct()
    {
        return $this->belongsTo(ProductionGoods::class, 'old_product_id');
    }

    public function newProduct()
    {
        return $this->belongsTo(ProductionGoods::class, 'new_product_id');
    }

    public function oldColor()
    {
        return $this->belongsTo(MasterColor::class, 'old_color_id');
    }

    public function newColor()
    {
        return $this->belongsTo(MasterColor::class, 'new_color_id');
    }

    public function oldSizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'old_size_set_id');
    }

    public function newSizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'new_size_set_id');
    }

    public function oldFitting()
    {
        return $this->belongsTo(MasterProductFitting::class, 'old_fitting_id');
    }

    public function newFitting()
    {
        return $this->belongsTo(MasterProductFitting::class, 'new_fitting_id');
    }

    public function oldPattern()
    {
        return $this->belongsTo(MasterDesignPattern::class, 'old_pattern_id');
    }

    public function newPattern()
    {
        return $this->belongsTo(MasterDesignPattern::class, 'new_pattern_id');
    }

    public function oldRack()
    {
        return $this->belongsTo(Rack::class, 'old_rack_id');
    }

    public function newRack()
    {
        return $this->belongsTo(Rack::class, 'new_rack_id');
    }

    public function oldWarehouse()
    {
        return $this->belongsTo(Storeroom::class, 'old_warehouse_id');
    }

    public function newWarehouse()
    {
        return $this->belongsTo(Storeroom::class, 'new_warehouse_id');
    }
}
