<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\FabricComposition;
use App\Http\DataTable\Admin\Master\FabricCompositionDataTable as DataTable;

class FabricCompositionService {
    protected $datatable;
    protected $fabric_composition;
    public function __construct(
        DataTable $datatable,
        FabricComposition $fabric_composition
    ) {
        $this->datatable= $datatable;
        $this->fabric_composition= $fabric_composition;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new FabricComposition;
        $save_data->name = $request->name;
        $save_data->sku = null;
        $save_data->status = $request->status ?? 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = FabricComposition::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = FabricComposition::find($request->id);
        $update_data->name = $request->name;
        $update_data->sku = null;
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = FabricComposition::where('id',$request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }

}