<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table= 'stocks';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'date',
        'goods_entry_number',
        'master_fabric_warehouse_id',
        'meter',
        'roll',
        'purchase_order_id',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'roll_no',
        'qrcode',
        'unique_number',
        'batch_no'
    ];
    public function purchase_order(){
        return $this->hasOne('App\Models\PurchaseOrder','id','purchase_order');
    }
    public function getQrcodeAttribute($value)
    {
        if ($value) {
            return asset('assets/qrcodes/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }
    public function fabric()
    {
        return $this->belongsTo('App\Models\fabric', 'sku', 'sku');
    }
    // public function expends(){
    //     return $this->hasMany('App\Models\StockExpend','stock_id','id');
    // }


    
}
