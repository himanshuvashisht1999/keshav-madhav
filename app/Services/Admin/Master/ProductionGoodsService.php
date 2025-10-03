<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterDesign;
use App\Models\MasterMaterial;
use App\Models\MasterSizeMeasurement;
use App\Models\Fabric;
use App\Models\BillOfMaterial;
use App\Http\DataTable\Admin\Master\ProductionGoodsDataTable as DataTable;

class ProductionGoodsService {
    public function __construct(
        DataTable $datatable,
        ProductionGoods $production_goods
    ) {
        $this->datatable= $datatable;
        $this->production_goods= $production_goods;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new ProductionGoods;
        $save_data->sku = $request->sku;
        $save_data->name_of_garment = $request->name_of_garment;
        $save_data->type_of_garment = $request->type_of_garment;
        $save_data->master_size_id = $request->master_size_id;
        $save_data->garment_pattern = $request->garment_pattern;
        $save_data->fabric_sku = '';
        $save_data->status = 1;
        $save_data->save();

        foreach($request->fabric_sku as $single){
            $save_boe = new BillOfMaterial;
            $save_boe->garment_id = $save_data->id;
            $save_boe->fabric_sku = $single;
            $save_boe->save();
        }
        return true;
    }

    public function edit(Request $request){
        $data = ProductionGoods::with('bill_of_materials')->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ProductionGoods::find($request->id);
        $update_data->name_of_garment = $request->name_of_garment;
        $update_data->type_of_garment = $request->type_of_garment;
        $update_data->master_size_id = $request->master_size_id;
        $update_data->garment_pattern = $request->garment_pattern;
        $update_data->fabric_sku = '';
        $update_data->save();
        BillOfMaterial::where('garment_id', $request->id)
                  ->whereNotIn('fabric_sku', $request->fabric_sku)->update([
                    'status' => 0
                  ]);
        foreach($request->fabric_sku as $single){
            $old_data = BillOfMaterial::where('garment_id',$request->id)->where('fabric_sku',$single)->first();
            if($old_data){
                $save_boe = BillOfMaterial::where('garment_id',$request->id)->where('fabric_sku',$single)->first();
            }else{
                $save_boe = new BillOfMaterial;
            }
            
            $save_boe->garment_id = $request->id;
            $save_boe->fabric_sku = $single;
            $save_boe->status = 1;
            $save_boe->save();
        }
        return true;
    }

    public function delete(Request $request){
        $data = ProductionGoods::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function colors(){
        $data = MasterColor::where('status',1)->get();
        return $data;
    }
    public function sizes(){
        $data = MasterSizeMeasurement::where('status',1)->get();
        return $data;
    }
    public function designs(){
        $data = MasterDesign::where('status',1)->get();
        return $data;
    }
    public function materials(){
        $data = MasterMaterial::where('status',1)->get();
        return $data;
    }
    public function fabrics(){
        $data = Fabric::where('status',1)->get();
        return $data;
    }
    public function garment_types(){
        $data = ProductionGoods::where('status',1)->select('type_of_garment')->distinct()->get();
        return $data;
    }
    public function garment_patterns(){
        $data = ProductionGoods::where('status',1)->select('garment_pattern')->distinct()->get();
        return $data;
    }

}