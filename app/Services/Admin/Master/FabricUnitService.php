<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\FabricUnit;
use App\Http\DataTable\Admin\Master\FabricUnitDataTable as DataTable;

class FabricUnitService {
    public function __construct(
        DataTable $datatable,
        FabricUnit $fabric_unit
    ) {
        $this->datatable= $datatable;
        $this->fabric_unit= $fabric_unit;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new FabricUnit;
        $save_data->name = $request->name;
        $save_data->symbol = $request->symbol;
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = FabricUnit::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = FabricUnit::find($request->id);
        $update_data->name = $request->name;
        $update_data->symbol = $request->symbol;
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = FabricUnit::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}
