<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockExpend extends Model
{
    use HasFactory;
    protected $table= 'stock_expends';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'stock_id',
        'roll',
        'roll_no',
        'qrcode',
        'unique_number',
        'status',
        'created_at',
        'updated_at'
    ];
    public function stock(){
        return $this->hasOne('App\Models\Stock','id','stock_id');
    }
    public function getQrcodeAttribute($value)
    {
        if ($value) {
            return asset('assets/qrcodes/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }


    
}
