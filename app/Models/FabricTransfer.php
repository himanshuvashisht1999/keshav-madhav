<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'transferred_by',
        'remarks'
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(MasterFabricWarehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(MasterFabricWarehouse::class, 'to_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function items()
    {
        return $this->hasMany(FabricTransferItem::class);
    }
}
