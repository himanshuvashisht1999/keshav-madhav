<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricInventoryHistory extends Model
{
    use HasFactory;

    protected $table = 'fabric_inventory_histories';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'fabric_id',
        'old_warehouse_id',
        'new_warehouse_id',
        'roll_number',
        'quantity',
        'type',
        'reference_id',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fabric()
    {
        return $this->belongsTo(Fabric::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function oldWarehouse()
    {
        return $this->belongsTo(MasterFabricWarehouse::class, 'old_warehouse_id');
    }

    public function newWarehouse()
    {
        return $this->belongsTo(MasterFabricWarehouse::class, 'new_warehouse_id');
    }
}
