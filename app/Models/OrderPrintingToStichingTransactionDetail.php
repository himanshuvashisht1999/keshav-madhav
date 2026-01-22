<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPrintingToStichingTransactionDetail extends Model
{
    use HasFactory;
    
    protected $table = 'order_printing_to_stiching_transaction_details';
    
    protected $fillable = [
        'order_printing_to_stiching_transaction_id',
        'size',
        'quantity'
    ];

    public function transaction()
    {
        return $this->belongsTo(OrderPrintingToStichingTransaction::class, 'order_printing_to_stiching_transaction_id');
    }
}
