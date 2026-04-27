<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class SalesAgent extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'status',
        'see_price',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Hash the password when saving.
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function shops()
    {
        return $this->hasMany(MasterCustomer::class, 'sales_agent_id');
    }

    public function orders()
    {
        return $this->hasMany(AgentOrder::class, 'sales_agent_id');
    }

    public function brandDiscounts()
    {
        return $this->hasMany(SalesAgentBrandDiscount::class, 'sales_agent_id');
    }

    public function currentOpeningBalance()
    {
        return $this->hasOne(MasterOpeningBalance::class, 'master_id')
            ->where('master_type', 'sales_agent')
            ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear());
    }
}
