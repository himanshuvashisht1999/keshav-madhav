<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Fabric extends Model
{
    use HasFactory;
    protected $table = 'fabrics';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'vendor_id',
        'name',
        'dye_id',
        'width_id',
        'weave_type_id',
        'gsm_id',
        'composition_id',
        'image',
        'status',
        'created_at',
        'updated_at'
    ];
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('assets/fabric/'. $value);
        } else {
            return asset('images/image-placeholder.png');
        }
    }
    public function fabric_gsm()
    {
        return $this->hasOne('App\Models\FabricGsm', 'id', 'gsm_id');
    }
    public function fabric_width()
    {
        return $this->hasOne('App\Models\FabricWidth', 'id', 'width_id');
    }
    public function fabric_weave_type()
    {
        return $this->hasOne('App\Models\FabricWeave', 'id', 'weave_type_id');
    }
    public function fabric_composition()
    {
        return $this->hasOne('App\Models\FabricComposition', 'id', 'composition_id');
    }

    public function fabric_vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }

    public function fabric_dye()
    {
        return $this->hasOne('App\Models\FabricDye', 'id', 'dye_id');
    }
    public function other_images()
    {
        return $this->hasMany('App\Models\FabricOtherImage', 'fabric_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany('App\Models\stock', 'sku', 'sku');
    }

    public function receiptDetails()
    {
        return $this->hasMany('App\Models\FabricReceiptDetail', 'fabric_id', 'id');
    }
}
