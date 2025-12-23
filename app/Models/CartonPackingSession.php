<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartonPackingSession extends Model
{
    use HasFactory;
    protected $table= 'carton_packing_session';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'carton_packing_session_no',
        'customer_id',
        'main_order_id',
        'carton_details_id',
        'total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    public function cartonsDetails(){
        return $this->hasMany('App\Models\OrderDispatchCartonsDetails','cartons_id','id');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'main_order_id', 'id' );
    }

}
