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
        $save_data->master_material_id = $request->master_material_id;
        $save_data->master_color_id = $request->master_color_id;
        $save_data->master_size_id = $request->master_size_id;
        $save_data->master_design_id = $request->master_design_id;
        $save_data->fabric_sku = $request->fabric_sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = ProductionGoods::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ProductionGoods::find($request->id);
        $update_data->name_of_garment = $request->name_of_garment;
        $update_data->master_material_id = $request->master_material_id;
        $update_data->master_color_id = $request->master_color_id;
        $update_data->master_size_id = $request->master_size_id;
        $update_data->master_design_id = $request->master_design_id;
        $update_data->fabric_sku = $request->fabric_sku;
        $update_data->save();
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

}