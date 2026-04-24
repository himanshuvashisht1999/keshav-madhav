<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HulayatiMaster extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'balance', 'status'];
}
