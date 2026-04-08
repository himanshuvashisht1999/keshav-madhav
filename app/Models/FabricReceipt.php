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

    protected $appends = ['paid_amount', 'balance_amount', 'can_delete'];

    public function getCanDeleteAttribute()
    {
        // A shipment can be deleted only if NONE of its rolls have been used
        // i.e., remaining_quantity must be equal to the original meter for ALL details
        return !$this->details()->whereColumn('remaining_quantity', '<', 'meter')->exists();
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

    public function getBalanceAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
}
