<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricReceiptPrice extends Model
{
    use HasFactory;
    protected $table= 'fabric_receipts_prices';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'fabric_receipt_id',
        'fabrics_id',
        'price',
        'rolls',
        'status',
        'created_at',
        'updated_at'
    ];

    public function fabric_receipt(){
        return $this->hasOne('App\Models\FabricReceipt','id','fabric_receipt_id');
    }

    
}
