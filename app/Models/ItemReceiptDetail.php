<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReceiptDetail extends Model
{
    use HasFactory;
    protected $table= 'items_receipt_details';
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
        'box',
        'quantity',
        'batch_no',
        'status',
        'created_at',
        'updated_at'
    ];
    public function item_receipt(){
        return $this->hasOne('App\Models\ItemReceipt','id','fabric_receipt_id');
    }
    public function purchase_order(){
        return $this->hasOne('App\Models\PurchaseOrderMaterial','id','purchase_order_id');
    }
    public function purchase_order_item(){
        return $this->hasOne('App\Models\PurchaseOrderMaterialItem','id','purchase_order_item_id');
    }
    public function item_attribute_value(){
        return $this->hasOne('App\Models\ItemAttributeValue','sku','fabric_sku');
    }

    
}
