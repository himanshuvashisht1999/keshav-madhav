<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGodamStageTransactionDetail extends Model
{
    use HasFactory;
    
    protected $table = 'order_godam_stage_transaction_details';
    
    protected $fillable = [
        'order_godam_stage_transaction_id',
        'size',
        'quantity'
    ];

    public function transaction()
    {
        return $this->belongsTo(OrderGodamStageTransaction::class, 'order_godam_stage_transaction_id');
    }
}
