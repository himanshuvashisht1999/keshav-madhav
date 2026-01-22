<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLot extends Model
{
    use HasFactory;
    protected $table= 'order_lots';
    protected $fillable = [
        'id',
        'order_main_id',
        'order_products_set_id',
        'production_slip_digitization_id',
        'lot_no',
        'is_printing',
        'is_stitching',
        'lot_no',
        'production_datetime',
        'status',
        'created_at',
        'updated_at'
    ];

    public function orderMain(){
        return $this->hasOne('App\Models\OrderMain','id','order_main_id');
    }
    public function orderProductSet(){
        return $this->hasOne('App\Models\OrderProductSet','id','order_products_set_id');
    }
}
