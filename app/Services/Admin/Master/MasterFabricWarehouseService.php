<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterFabricWarehouse;
use App\Http\DataTable\Admin\Master\MasterFabricWarehouseDataTable as DataTable;

class MasterFabricWarehouseService {
    protected $datatable;
    protected $warehouse;
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
        $save_data = new MasterFabricWarehouse;
        $save_data->cutting_master_name = $request->cutting_master_name;
        $save_data->sku = NULL;
        $save_data->address = $request->address;
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = $this->warehouse->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = $this->warehouse->find($request->id);
        $update_data->cutting_master_name = $request->cutting_master_name;
        $update_data->sku = NULL;
        $update_data->address = $request->address;
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = $this->warehouse->where('id',$request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }
    public function getMasterWarehouse(){
        return $this->warehouse->where('status',1)->get();
    }
}