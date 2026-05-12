<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'barcode',
        'quantity',
        'reason',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fabricReceiptDetail()
    {
        return $this->belongsTo(FabricReceiptDetail::class, 'item_id');
    }

    public function domesticInventory()
    {
        return $this->belongsTo(DomesticInventory::class, 'item_id');
    }
}
