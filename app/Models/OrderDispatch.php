<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatch extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table = 'order_dispatch';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'dispatch_date',
        'customer_id',
        'main_order_id',
        'dispatch_by',
        'total_quantity',
        'gst_percentage',
        'discount_percentage',
        'total_amount',
        'status',
        'is_paid',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['paid_amount', 'balance_amount'];

    public function dispatchDetails()
    {
        return $this->hasMany('App\Models\OrderDispatchDetails', 'order_dispatch_id', 'id');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'main_order_id', 'id');
    }

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
}
