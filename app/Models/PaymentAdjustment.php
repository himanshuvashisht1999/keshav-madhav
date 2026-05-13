<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'adjustment_master_id',
        'ref_id',
        'type',
        'payment_mode',
        'payment_account_id',
        'amount',
        'date',
        'remarks',
    ];

    public function master()
    {
        return $this->belongsTo(AdjustmentMaster::class, 'adjustment_master_id');
    }

    public function account()
    {
        if ($this->payment_mode == 'bank') {
            return $this->belongsTo(BankAccount::class, 'payment_account_id');
        } else {
            return $this->belongsTo(CashPayment::class, 'payment_account_id');
        }
    }
    
    public function getEntityAttribute()
    {
        $modelName = $this->master->model_name;
        $refId = $this->ref_id;

        if (str_starts_with($refId, 'OD:')) {
            return \App\Models\OrderDispatch::find(substr($refId, 3));
        } elseif (str_starts_with($refId, 'AOD:')) {
            return \App\Models\AgentOrderDispatch::find(substr($refId, 4));
        } elseif (str_starts_with($refId, 'OR:')) {
            return \App\Models\OrderMain::find(substr($refId, 3));
        }

        if (class_exists($modelName)) {
            return $modelName::find($refId);
        }
        return null;
    }

    public function getEntityNameAttribute()
    {
        $entity = $this->entity;
        if (!$entity) {
            return $this->ref_id ?? 'N/A';
        }
        
        $displayName = '';

        if (str_starts_with($this->ref_id, 'OD:') || str_starts_with($this->ref_id, 'AOD:') || str_starts_with($this->ref_id, 'OR:')) {
            // For dispatches/orders, show the party name
            if (str_starts_with($this->ref_id, 'AOD:')) {
                $displayName = ($entity->party->name ?? $entity->shop->name ?? $entity->vendor->name ?? 'N/A');
            } else {
                $displayName = $entity->customer->name ?? $entity->shop->name ?? 'N/A';
            }
        } elseif ($this->master->model_name == 'App\Models\FabricReceipt') {
            // For shipments, show the vendor name
            $displayName = $entity->vendor->name ?? 'N/A';
        } else {
            // For direct masters (Customer, Vendor, Employee, etc.), use their name
            $displayName = $entity->name ?? $entity->shipment_id ?? $entity->sku ?? ('#' . ($entity->id ?? 'N/A'));
        }

        return $displayName;
    }

    public function getParentItemAttribute()
    {
        $entity = $this->entity;
        if (!$entity) return ['id' => $this->ref_id, 'name' => 'N/A'];

        $modelName = $this->master->model_name;
        if ($modelName == 'App\Models\FabricReceipt') {
            return ['id' => $entity->vendor_id, 'name' => $entity->vendor->name ?? 'Vendor'];
        }

        if (str_contains($this->ref_id, 'OD:') || str_contains($this->ref_id, 'AOD:') || str_contains($this->ref_id, 'OR:')) {
            $customerId = $entity->customer_id ?? $entity->master_customer_id;
            $customerName = ($entity->customer->name ?? $entity->shop->name ?? 'Customer');
            return ['id' => $customerId, 'name' => $customerName];
        }
        
        return ['id' => $this->ref_id, 'name' => $this->entity_name];
    }
}
