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
        'roll',
        'meter',
        'status',
        'created_at',
        'updated_at'
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

    
}
