<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductStage extends Model
{
    use HasFactory;
    protected $table= 'order_product_stages';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'order_product_id',
        'stage_id',
        'sequence',
        'total_qty',
        'completed_qty',
        'pending_qty',
        'status',  // 0- Pending, 1 : In Progress , 2: Completed
        'created_at',
        'updated_at'
    ];

    public function stage(){
        return $this->hasOne('App\Models\MasterProductStage','id','stage_id');
    }



    
}
