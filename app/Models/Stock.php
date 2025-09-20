<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
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
        'meter',
        'roll',
        'purchase_order_id',
        'status',
        'created_at',
        'updated_at'
    ];
    public function purchase_order(){
        return $this->hasOne('App\Models\PurchaseOrder','id','purchase_order');
    }


    
}
