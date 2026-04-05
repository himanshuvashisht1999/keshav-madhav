<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table = 'companies';
    protected $fillable = [
        'id',
        'name',
        'address',
        'gst_number',
        'phone',
        'email',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];
}
