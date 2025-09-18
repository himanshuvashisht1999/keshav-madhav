<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    use HasFactory;
    protected $table= 'fabrics';
    protected $fillable = [
        'id',
        'sno',
        'company_id',
        'sub_company_id',
        'project_id',
        'sku',
        'name',
        'dye_id',
        'width_id',
        'weave_type_id',
        'gsm_id',
        'composition_id',
        'status',
        'created_at',
        'updated_at'
    ];
    public function fabric_gsm(){
        return $this->hasOne('App\Models\FabricGsm','id','gsm_id');
    }
    public function fabric_width(){
        return $this->hasOne('App\Models\FabricWidth','id','width_id');
    }
    public function fabric_weave_type(){
        return $this->hasOne('App\Models\FabricWeave','id','weave_type_id');
    }
    public function fabric_composition(){
        return $this->hasOne('App\Models\FabricComposition','id','composition_id');
    }
    public function fabric_dye(){
        return $this->hasOne('App\Models\FabricDye','id','dye_id');
    }
    
}
