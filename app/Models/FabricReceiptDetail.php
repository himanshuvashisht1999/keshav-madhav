<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReceiptDetail extends Model
{
    use HasFactory;
    protected $table= 'fabric_receipt_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'fabric_receipt_id',
        'purchase_order_id',
        'purchase_order_item_id',
        'fabric_sku',
        'fabric_id',
        'roll',
        'roll_number',
        'price_per_meter',
        'master_fabric_warehouse_id',
        'barcode',
        'qrcode',
        'qrcode_number',
        'shipment_number',
        'remaining_quantity',
        'meter',
        'status',
        'created_at',
        'updated_at',
        'batch_no'
    ];
    public function fabric_receipt(){
        return $this->hasOne('App\Models\FabricReceipt','id','fabric_receipt_id');
    }
    public function purchase_order(){
        return $this->hasOne('App\Models\PurchaseOrder','id','purchase_order_id');
    }
    public function purchase_order_item(){
        return $this->hasOne('App\Models\PurchaseOrderItem','id','purchase_order_item_id');
    }
    public function fabric(){
        return $this->hasOne('App\Models\Fabric','sku','fabric_sku');
    }
    public function master_fabric_warehouse(){
        return $this->hasOne('App\Models\MasterFabricWarehouse','id','master_fabric_warehouse_id');
    }

    public function getQrcodeAttribute($value)
    {
        if ($value) {
            return asset('assets/qrcodes/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }
    public function getBarcodeAttribute($value)
    {
        if ($value) {
            return asset('assets/barcodes/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }

    
}
