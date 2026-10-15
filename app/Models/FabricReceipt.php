<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReceipt extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table = 'fabric_receipts';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'shipment_id',
        'bill_no',
        'vendor_id',
        'truck_number',
        'time',
        'roll',
        'received_by',
        'shipment_photo',
        'challan_photo',
        'master_fabric_warehouse_id',
        'amount',
        'gst_amount',
        'gst_percentage',
        'total_amount',
        'other_charges',
        'total_meter',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['paid_amount', 'returned_amount', 'balance_amount', 'can_delete'];

    public function other_images()
    {
        return $this->hasMany(FabricReceiptOtherImage::class, 'fabric_receipt_id', 'id');
    }

    public function getCanDeleteAttribute()
    {
        // 1. Check if rolls have been used (cut, sold, returned, moved to another warehouse, disposed)
        $hasUsedRolls = \App\Services\Admin\FabricPurchaseLink::shipmentHasUsedRolls((int) $this->id);
        if ($hasUsedRolls) return false;

        // payments made for this shipment, or returns made from it
        if ($this->payments->isNotEmpty()) return false;
        if (\App\Models\FabricReturn::where('fabric_receipt_id', $this->id)->exists()) return false;

        // 2. Check if any payment adjustments exist for this shipment
        $hasAdjustments = \App\Models\PaymentAdjustment::where('ref_id', (string)$this->id)
            ->whereIn('adjustment_master_id', [14, 16]) // Vendor master IDs
            ->exists();
        
        return !$hasAdjustments;
    }

    public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }
    public function cutting_master()
    {
        return $this->hasOne('App\Models\MasterFabricWarehouse', 'id', 'master_fabric_warehouse_id');
    }
    public function details()
    {
        return $this->hasMany('App\Models\FabricReceiptDetail', 'fabric_receipt_id', 'id');
    }

    public function returns()
    {
        return $this->hasMany('App\Models\FabricReturn', 'fabric_receipt_id', 'id');
    }

    public function prices()
    {
        return $this->hasMany('App\Models\FabricReceiptPrice', 'fabric_receipt_id', 'id');
    }
    public function getShipmentPhotoAttribute($value)
    {
        if ($value) {
            return asset('assets/receipts/shipment-image/' . $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }
    public function getChallanPhotoAttribute($value)
    {
        if ($value) {
            return asset('assets/receipts/challan-image/' . $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }

    public function payments()
    {
        return $this->morphMany('App\Models\Payment', 'paymentable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    // value of the fabric returned to the vendor from this shipment (Fabric Return / return from the shipment page)
    public function getReturnedAmountAttribute()
    {
        return \App\Services\Admin\FabricPurchaseLink::returnedAmount((int) $this->id);
    }

    // still to pay for this shipment: bill - paid - returned
    public function getBalanceAmountAttribute()
    {
        return round($this->total_amount - $this->paid_amount - $this->returned_amount, 2);
    }
}
