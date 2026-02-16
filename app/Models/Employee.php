<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'status'
    ];

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }
}
