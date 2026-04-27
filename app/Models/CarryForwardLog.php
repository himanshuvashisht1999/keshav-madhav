<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarryForwardLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year',
        'user_id',
    ];
}
