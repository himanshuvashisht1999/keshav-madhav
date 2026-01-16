<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricRollAssigningsDetail extends Model
{
    use HasFactory;
    protected $table = 'production_fabric_roll_assigning_details';
    protected $fillable = [
        'id',
        'production_fabric_roll_assigning_id',
        'order_product_set_detail_id',
        'size',
        'quantity',
        'status'
    ];

    public function orderProductSetDetail() {
        return $this->belongsTo(OrderProductSetDetail::class, 'order_product_set_detail_id');
    }

    public function fabricRollAssigning()
    {
        return $this->belongsTo(\App\Models\FabricRollAssigning::class, 'production_fabric_roll_assigning_id', 'id');
    }

}
