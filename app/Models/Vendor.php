<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $table= 'vendors';
    protected $fillable = [
        'id',
        'name',
        'type',
        'phone', 
        'email', 
        'address',
        'status',
        'created_at',
        'updated_at'
    ];
    
}
