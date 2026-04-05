<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'adjustment_master_id',
        'ref_id',
        'type',
        'payment_mode',
        'payment_account_id',
        'amount',
        'date',
        'remarks',
    ];

    public function master()
    {
        return $this->belongsTo(AdjustmentMaster::class, 'adjustment_master_id');
    }

    public function account()
    {
        if ($this->payment_mode == 'bank') {
            return $this->belongsTo(BankAccount::class, 'payment_account_id');
        } else {
            return $this->belongsTo(CashPayment::class, 'payment_account_id');
        }
    }
    
    public function getEntityAttribute()
    {
        $modelName = $this->master->model_name;
        if (class_exists($modelName)) {
            return $modelName::find($this->ref_id);
        }
        return null;
    }

    public function getEntityNameAttribute()
    {
        $entity = $this->entity;
        if (!$entity) return 'N/A';
        
        if ($this->master->model_name == 'App\Models\BankAccount') {
            return $entity->bank_name . ' (' . $entity->account_number . ')';
        }
        return $entity->name ?? 'N/A';
    }
}
