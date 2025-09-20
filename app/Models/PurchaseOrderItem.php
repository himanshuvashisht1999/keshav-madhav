<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    protected $table= 'purchase_order_items';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'purchase_order_id',
        'fabric_sku',
        'fabric_id',
        'roll',
        'price',
        'status',
        'created_at',
        'updated_at'
    ];
    public function fabric(){
        return $this->hasOne('App\Models\Fabric','id','fabric_id');
    }


    
}
