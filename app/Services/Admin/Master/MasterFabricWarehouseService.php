<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterFabricWarehouse;
use App\Http\DataTable\Admin\Master\MasterFabricWarehouseDataTable as DataTable;

class MasterFabricWarehouseService {
    public function __construct(
        DataTable $datatable,
        MasterFabricWarehouse $warehouse
    ) {
        $this->datatable= $datatable;
        $this->warehouse = $warehouse;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        // if($request->file('image')){
        //     $image = $request->file('image');
        //     $extImage = $image->getClientOriginalExtension();
        //     $imgName = "service-".rand()."_".time().".".$extImage;
        //     $destinationPath = public_path().'/assets/services';
        //     $image->move($destinationPath, $imgName);
        // }
        $save_data = new MasterFabricWarehouse;
        $save_data->cutting_master_name = $request->cutting_master_name;
        $save_data->sku = $request->sku;
        $save_data->address = $request->address;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterFabricWarehouse::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = MasterFabricWarehouse::find($request->id);
        $update_data->cutting_master_name = $request->cutting_master_name;
        $update_data->address = $request->address;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = MasterFabricWarehouse::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function getMasterWarehouse(){
        return MasterFabricWarehouse::where('status',1)->get();
    }
}