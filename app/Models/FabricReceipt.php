<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReceipt extends Model
{
    use HasFactory;
    protected $table= 'fabric_receipts';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'vendor_id',
        'truck_number',
        'time',
        'roll',
        'received_by',
        'shipment_photo',
        'status',
        'created_at',
        'updated_at'
    ];
    public function vendor(){
        return $this->hasOne('App\Models\Vendor','id','vendor_id');
    }
    public function details(){
        return $this->hasMany('App\Models\FabricReceiptDetail','fabric_receipt_id','id');
    }
    public function getShipmentPhotoAttribute($value)
    {
        if ($value) {
            return asset('assets/shipment-image/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    } 

    
}
