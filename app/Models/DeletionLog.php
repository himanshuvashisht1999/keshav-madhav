<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionLog extends Model
{
    use HasFactory;

    protected $table = 'deletion_logs';

    protected $fillable = [
        'module',
        'record_id',
        'payload',
        'deleted_by',
        'restored_at',
        'restored_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'restored_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function restoredByUser()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    public function getPartyNameAttribute()
    {
        $payload = $this->payload;
        if (!is_array($payload)) {
            return null;
        }

        // For Agent Order or Order modules
        if (str_contains(strtolower($this->module), 'order')) {
            $order = $payload['order'] ?? $payload;
            if (!empty($order['shop_name']) && $order['shop_name'] !== 'N/A') {
                return $order['shop_name'];
            }
            if (!empty($order['party_name'])) {
                return $order['party_name'];
            }
            if (!empty($order['shop']['name'])) {
                return $order['shop']['name'];
            }
            if (!empty($order['vendor']['name'])) {
                return $order['vendor']['name'];
            }
            $partyType = $order['party_type'] ?? 'customer';
            if ($partyType === 'vendor' && !empty($order['master_vendor_id'])) {
                return \App\Models\Vendor::where('id', $order['master_vendor_id'])->value('name');
            } elseif (!empty($order['master_customer_id'])) {
                return \App\Models\MasterCustomer::where('id', $order['master_customer_id'])->value('name');
            }
        }

        // Check other modules
        return $payload['party_name'] 
            ?? $payload['customer_name'] 
            ?? $payload['shop_name'] 
            ?? $payload['vendor_name'] 
            ?? null;
    }
}
