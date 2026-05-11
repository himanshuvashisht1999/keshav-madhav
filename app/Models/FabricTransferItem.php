<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fabric_transfer_id',
        'fabric_receipt_detail_id',
        'fabric_id',
        'meter'
    ];

    public function fabricTransfer()
    {
        return $this->belongsTo(FabricTransfer::class);
    }

    public function fabricReceiptDetail()
    {
        return $this->belongsTo(FabricReceiptDetail::class, 'fabric_receipt_detail_id');
    }

    public function fabric()
    {
        return $this->belongsTo(Fabric::class);
    }
}
