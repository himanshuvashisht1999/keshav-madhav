<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentMaster extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'model_name', 'status'];
}
