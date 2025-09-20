<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\FabricWidth;
use App\Http\DataTable\Admin\Master\FabricWidthDataTable as DataTable;

class FabricWidthService {
    public function __construct(
        DataTable $datatable,
        FabricWidth $fabric_width
    ) {
        $this->datatable= $datatable;
        $this->fabric_width= $fabric_width;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new FabricWidth;
        $save_data->name = $request->name;
        $save_data->unit = $request->unit;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = FabricWidth::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = FabricWidth::find($request->id);
        $update_data->name = $request->name;
        $update_data->unit = $request->unit;
        // $update_data->sku = $request->sku;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = FabricWidth::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}