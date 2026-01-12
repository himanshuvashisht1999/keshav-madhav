<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricRollAssigningsDetail extends Model
{
    use HasFactory;
    protected $table = 'production_fabric_roll_assigning_details';
    protected $fillable = [
        'production_fabric_roll_assigning_id',
        'order_product_set_detail_id',
        'size',
        'quantity',
        'status'
    ];

    public function orderProductSetDetail() {
        return $this->belongsTo(OrderProductSetDetail::class, 'order_product_set_detail_id');
    }
}
