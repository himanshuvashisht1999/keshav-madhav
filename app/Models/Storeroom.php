<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storeroom extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description', 'status', 'order_taken', 'order_priority'];

    public function racks()
    {
        return $this->hasMany(Rack::class, 'storeroom_id');
    }
}
