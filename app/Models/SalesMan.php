<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class SalesMan extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function orders()
    {
        return $this->hasMany(AgentOrder::class, 'sales_man_id');
    }
}
