<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseAgent extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $table = 'purchase_agents';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'created_at',
        'updated_at',
        'created_by'
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class, 'purchase_agent_id');
    }
}
