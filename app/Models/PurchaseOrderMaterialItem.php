<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderMaterialItem extends Model{
  use HasFactory;
  protected $table='purchase_order_material_items';
  protected $fillable=[
    'id', 'sno', 'company_id', 'sub_company_id', 'project_id', 'sku', 'purchase_order_material_id', 'item_attribute_value_sku', 'quantity', 'price', 'total_price', 'status', 'created_at', 'updated_at'
  ];

  public function item_attribute_value(){
    return $this->hasOne('App\Models\ItemAttributeValue', 'sku', 'item_attribute_value_sku');
  }

}