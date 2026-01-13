<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    use HasFactory;
    
    protected $fillable = ['storeroom_id', 'name', 'capacity', 'status'];
    
    public function storeroom()
    {
        return $this->belongsTo(Storeroom::class, 'storeroom_id');
    }

    public function cartons()
    {
        return $this->hasMany(PackingCarton::class, 'rack_id');
    }
}
