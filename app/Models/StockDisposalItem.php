<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDisposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_disposal_main_id',
        'item_id',
        'barcode',
        'quantity'
    ];

    public function main()
    {
        return $this->belongsTo(StockDisposalMain::class, 'stock_disposal_main_id');
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
