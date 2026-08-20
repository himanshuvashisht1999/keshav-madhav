<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOutflowDispose extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_outflow_inventory_id',
        'reason',
        'created_by'
    ];

    public function inventory()
    {
        return $this->belongsTo(ProductionOutflowInventory::class, 'production_outflow_inventory_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
