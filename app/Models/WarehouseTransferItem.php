<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_transfer_id',
        'domestic_inventory_id',
        'packing_carton_id',
        'from_rack_id',
        'product_id',
        'color_id',
        'size_set_id',
        'quantity'
    ];

    public function transfer()
    {
        return $this->belongsTo(WarehouseTransfer::class, 'warehouse_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function sizeSet()
    {
        return $this->belongsTo(MasterSizeMeasurement::class, 'size_set_id');
    }

    public function fromRack()
    {
        return $this->belongsTo(Rack::class, 'from_rack_id');
    }
    
    public function inventory()
    {
        return $this->belongsTo(DomesticInventory::class, 'domestic_inventory_id');
    }
    
    public function carton()
    {
        return $this->belongsTo(PackingCarton::class, 'packing_carton_id');
    }
}
