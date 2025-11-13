<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAttribute extends Model
{
    use HasFactory;
    protected $table= 'item_attributes';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'item_id',
        'name',
        'input_type',
        'status',
        'created_at',
        'updated_at'
    ];

    public function item() {
        return $this->belongsTo('App\Models\Item', 'item_attribute_id', 'id');
    }

    public function item_attribute_values(){
        return $this->hasMany('App\Models\ItemAttributeValue','item_attribute_id','id');
    }

}
