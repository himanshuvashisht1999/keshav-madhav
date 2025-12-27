<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingCartonsDetails extends Model
{
    use HasFactory;
    protected $table= 'packing_cartons_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'cartons_id',
        'design_number',
        'set_size_id',
        'bar_code',
        'set_quantity',
        'color_id',
        'status',
        'created_at',
        'updated_at'
    ];

    public function colors()
    {
        return $this->hasOne(MasterColor::class, 'id', 'color_id');
    }

    public function sizeMeasurement()
    {
        return $this->hasOne(MasterSizeMeasurement::class, 'id', 'set_size_id');
    }
    
}
