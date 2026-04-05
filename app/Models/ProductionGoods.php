<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionGoods extends Model
{
    use HasFactory, \App\Traits\TrackCreator;
    protected $table= 'production_goods';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'type_of_garment',
        'name_of_garment',
        'master_series_id',
        'master_material_id',
        'master_color_id',
        'master_size_id',
        'master_design_id',
        'fabric_sku',
        'is_printing',
        'is_embroidery',
        'design_number',
        'brand_id',
        'master_product_fitting_id',
        'master_pattern_id',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];
    public function material(){
        return $this->hasOne('App\Models\MasterMaterial','id','master_material_id');
    }
    public function design(){
        return $this->hasOne('App\Models\MasterDesign','id','master_design_id');
    }
    public function size(){
        return $this->hasOne('App\Models\MasterSizeMeasurement','id','master_size_id');
    }
    public function color(){
        return $this->hasOne('App\Models\MasterColor','id','master_color_id');
    }
    public function series(){
        return $this->hasOne(\App\Models\MasterSeries::class, 'id', 'master_series_id');
    }
    public function brand(){
        return $this->belongsTo(\App\Models\Brand::class, 'brand_id');
    }
    public function fitting(){
        return $this->belongsTo(\App\Models\MasterProductFitting::class, 'master_product_fitting_id');
    }
    public function pattern(){
        return $this->belongsTo(\App\Models\MasterDesignPattern::class, 'master_pattern_id');
    }
    public function variants(){
        return $this->hasMany(\App\Models\ProductionGoodVariant::class, 'production_goods_id');
    }
    public function bill_of_materials(){
        return $this->hasMany('App\Models\BillOfMaterial','product_id','id')->where('status',1);
    }
    public function product_stages(){
        return $this->hasMany('App\Models\ProductStage','master_product_id','id')->where('status',1);
    }

    public function images()
    {
        return $this->hasMany(ProductionGoodImage::class, 'product_id');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductionGoodImage::class, 'product_id')->where('is_main', 1);
    }

    public function getMainImgAttribute()
    {
        if ($this->mainImage) {
            return $this->mainImage->image;
        }
        return null;
    }
    
}
