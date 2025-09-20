<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\FabricDye;
use App\Http\DataTable\Admin\Master\FabricDyeDataTable as DataTable;

class FabricDyeService {
    public function __construct(
        DataTable $datatable,
        FabricDye $fabric_dye
    ) {
        $this->datatable= $datatable;
        $this->fabric_dye= $fabric_dye;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new FabricDye;
        $save_data->color = $request->color;
        $save_data->pantone = $request->pantone;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = FabricDye::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = FabricDye::find($request->id);
        $update_data->color = $request->color;
        $update_data->pantone = $request->pantone;
        
        // $update_data->sku = $request->sku;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = FabricDye::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}