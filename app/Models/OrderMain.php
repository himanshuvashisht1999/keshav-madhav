<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMain extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table = 'order_main';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'po_number',
        'po_date',
        'order_type',
        'expected_delivery_date',
        'master_customer_id',
        'total_amount',
        'status',
        'is_paid',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['paid_amount', 'balance_amount'];

    public function customer()
    {
        return $this->hasOne('App\Models\MasterCustomer', 'id', 'master_customer_id');
    }
    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'order_main_id', 'id');
    }
    public function order_products()
    {
        return $this->hasMany('App\Models\OrderProduct', 'order_main_id', 'id');
    }
    public function packages()
    {
        return $this->hasMany(Package::class, 'order_main_id');
    }

    public function package()
    {
        return $this->hasOne('App\Models\Package', 'order_main_id', 'id');
    }

    public function OrderProductSets()
    {
        return $this->hasMany('App\Models\OrderProductSet', 'order_main_id', 'id');
    }

    public function orderCuttingStages()
    {
        return $this->hasMany('App\Models\OrderCuttingStage', 'order_main_id', 'id');
    }

    public function orderLots()
    {
        return $this->hasMany('App\Models\OrderLot', 'order_main_id', 'id');
    }

    public function dispatchCartons()
    {
        return $this->hasManyThrough(
            PackingCarton::class,
            PackingMain::class,
            'order_main_id', // Foreign key on PackingMain table
            'packing_main_id', // Foreign key on PackingCarton table
            'id', // Local key on OrderMain table
            'id' // Local key on PackingMain table
        );
    }

    public function packingMains()
    {
        return $this->hasMany(PackingMain::class, 'order_main_id');
    }

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        // For orders, we don't have a definitive 'total_amount' in DB, 
        // but we can track payments. If user marks as paid, balance is 0.
        return $this->is_paid ? 0 : null;
    }

}
