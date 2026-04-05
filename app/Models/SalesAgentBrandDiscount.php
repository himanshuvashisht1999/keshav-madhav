<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesAgentBrandDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_agent_id',
        'brand_id',
        'discount_percentage',
    ];

    public function salesAgent()
    {
        return $this->belongsTo(SalesAgent::class, 'sales_agent_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
