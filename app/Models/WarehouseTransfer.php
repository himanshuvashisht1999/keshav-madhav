<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'from_storeroom_id',
        'to_storeroom_id',
        'to_rack_id',
        'transferred_by',
        'notes'
    ];

    public function items()
    {
        return $this->hasMany(WarehouseTransferItem::class);
    }

    public function fromStoreroom()
    {
        return $this->belongsTo(Storeroom::class, 'from_storeroom_id');
    }

    public function toStoreroom()
    {
        return $this->belongsTo(Storeroom::class, 'to_storeroom_id');
    }

    public function toRack()
    {
        return $this->belongsTo(Rack::class, 'to_rack_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
