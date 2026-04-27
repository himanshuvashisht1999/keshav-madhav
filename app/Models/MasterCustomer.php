<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    use HasFactory;
    protected $table = 'master_customers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'type',
        'subtype',
        'parent_id',
        'sales_agent_id',
        'see_price',
        'balance',
        'password',
    ];

    public function brandDiscounts()
    {
        return $this->hasMany(CustomerBrandDiscount::class, 'customer_id');
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = \Hash::make($value);
        }
    }

    public function agent()
    {
        return $this->belongsTo(SalesAgent::class, 'sales_agent_id');
    }

    public function parent()
    {
        return $this->belongsTo(MasterCustomer::class, 'parent_id');
    }

    public function shops()
    {
        return $this->hasMany(MasterCustomer::class, 'parent_id');
    }

    public function orders()
    {
        return $this->hasMany(OrderMain::class, 'master_customer_id');
    }

    public function currentOpeningBalance()
    {
        return $this->hasOne(MasterOpeningBalance::class, 'master_id')
            ->where('master_type', 'customer')
            ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear());
    }
}
