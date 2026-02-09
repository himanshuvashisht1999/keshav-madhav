<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticInventory extends Model
{
    use HasFactory;

    protected $table = 'domestic_inventories';

    protected $fillable = [
        'order_main_id',
        'packing_main_id',
        'packing_carton_id',
        'packing_box_id',
        'product_id',
        'product_name',
        'color_id',
        'color_name',
        'size_set_id',
        'size_set_name',
        'design_number',
        'quantity',
        'mrp',
        'selling_price',
        'price',
        'box_no',
        'carton_no',
        'barcode',
        'qrcode',
        'status'
    ];

    public function orderMain()
    {
        return $this->belongsTo(OrderMain::class, 'order_main_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionGoods::class, 'product_id');
    }

    public function color()
    {
        return $this->belongsTo(MasterColor::class, 'color_id');
    }

    public function carton()
    {
        return $this->belongsTo(PackingCarton::class, 'packing_carton_id');
    }

    public function box()
    {
        return $this->belongsTo(PackingBox::class, 'packing_box_id');
    }
}
