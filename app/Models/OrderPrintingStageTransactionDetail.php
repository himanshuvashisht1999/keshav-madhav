<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPrintingStageTransactionDetail extends Model
{
    use HasFactory;
    
    protected $table = 'order_printing_stage_transaction_details';
    
    protected $fillable = [
        'order_printing_stage_transaction_id',
        'size',
        'quantity'
    ];

    public function transaction()
    {
        return $this->belongsTo(OrderPrintingStageTransaction::class, 'order_printing_stage_transaction_id');
    }
}
