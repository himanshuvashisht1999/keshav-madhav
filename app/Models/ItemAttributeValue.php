<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAttributeValue extends Model
{
    use HasFactory;
    protected $table= 'item_attribute_values';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'item_attribute_id',
        'value',
        'status',
        'created_at',
        'updated_at'
    ];

    public function attributes() {
        return $this->belongsTo('App\Models\ItemAttribute', 'item_attribute_id', 'id');
    }

    public function item_stocks() {
        return $this->hasMany('App\Models\ItemStock', 'sku', 'sku');
    }
}

