<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterProductStage;
use App\Http\DataTable\Admin\Master\MasterProductStageDataTable as DataTable;

class MasterProductStageService {
    public function __construct(
        DataTable $datatable,
        MasterProductStage $product_stage
    ) {
        $this->datatable= $datatable;
        $this->product_stage= $product_stage;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){

        $save_data = new MasterProductStage;
        $save_data->name = $request->name;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterProductStage::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = MasterProductStage::find($request->id);
        $update_data->name = $request->name;
        // $update_data->sku = $request->sku;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = MasterProductStage::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}