<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStock extends Model
{
    use HasFactory;
    protected $table= 'item_stocks';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'date',
        'goods_entry_number',
        'quantity',
        'box',
        'purchase_order_id',
        'status',
        'created_at',
        'updated_at',
        'box_no',
        'qrcode',
        'unique_number',
        'batch_no'
    ];

    public function purchase_order(){
        return $this->hasOne('App\Models\PurchaseOrderMaterial','id','purchase_order_id');
    }

    public function getQrcodeAttribute($value)
    {
        if ($value) {
            return asset('assets/qrcodes/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }

    public function item_attribute_value()
    {
        return $this->belongsTo('App\Models\ItemAttributeValue', 'sku', 'sku');
    }
}
