<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBrandDiscount extends Model
{
    protected $fillable = [
        'customer_id',
        'brand_id',
        'discount_percentage',
    ];

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Replace a customer's per-brand discounts with the submitted values.
     */
    public static function saveForCustomer($customerId, $input)
    {
        static::where('customer_id', $customerId)->delete();

        foreach ((array) $input as $brandId => $discount) {
            if ($discount === null || $discount === '') {
                continue;
            }
            static::create([
                'customer_id' => $customerId,
                'brand_id' => $brandId,
                'discount_percentage' => $discount,
            ]);
        }
    }

    /**
     * Copy a sales agent's brand discounts to every customer under that agent.
     */
    public static function syncFromAgent($salesAgentId)
    {
        $agentMap = SalesAgentBrandDiscount::where('sales_agent_id', $salesAgentId)
            ->pluck('discount_percentage', 'brand_id');

        foreach (MasterCustomer::where('sales_agent_id', $salesAgentId)->pluck('id') as $customerId) {
            static::where('customer_id', $customerId)->delete();
            foreach ($agentMap as $brandId => $discount) {
                static::create([
                    'customer_id' => $customerId,
                    'brand_id' => $brandId,
                    'discount_percentage' => $discount,
                ]);
            }
        }
    }
}
