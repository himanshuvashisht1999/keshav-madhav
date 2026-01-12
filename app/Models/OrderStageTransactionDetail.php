<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStageTransactionDetail extends Model
{
    use HasFactory;
    
    protected $table = 'order_stage_transaction_details';
    
    protected $fillable = [
        'order_stage_transaction_id',
        'size',
        'quantity'
    ];

    public function transaction()
    {
        return $this->belongsTo(OrderStageTransaction::class, 'order_stage_transaction_id');
    }
}
