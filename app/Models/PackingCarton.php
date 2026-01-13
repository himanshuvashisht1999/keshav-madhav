<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PackingMain;
use App\Models\PackingBox;
use App\Models\PackingItem;

class PackingCarton extends Model
{
    use HasFactory;
    protected $table= 'packing_cartons';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'carton_packing_session_id',
        'customer_id',
        'main_order_id',
        'carton_details_id',
        'total_quantity',
        'status',
        'created_at',
        'updated_at'
    ];

    public function cartonsDetails(){
        return $this->hasMany('App\Models\PackingCartonsDetails','cartons_id','id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class, 'packing_carton_id');
    }

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'main_order_id', 'id' );
    }

    public function cartonsSession()
    {
        return $this->belongsTo(CartonPackingSession::class, 'carton_packing_session_id', 'id' );
    }
}
