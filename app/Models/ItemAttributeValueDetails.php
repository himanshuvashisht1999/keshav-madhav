<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAttributeValueDetails extends Model
{
    use HasFactory;
    protected $table= 'item_attribute_values_details';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'item_attribute_value_id',
        'value',
        'status',
        'created_at',
        'updated_at'
    ];

}

