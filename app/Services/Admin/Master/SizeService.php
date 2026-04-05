<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterSize;
use App\Models\MasterSizeSelection;
use App\Http\DataTable\Admin\Master\SizeDataTable as DataTable;

class SizeService {
    protected $datatable;
    protected $master_size;
    public function __construct(
        DataTable $datatable,
        MasterSize $master_size
    ) {
        $this->datatable= $datatable;
        $this->master_size= $master_size;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){

        $save_data = new MasterSize;
        $save_data->size = $request->size;
        $save_data->sku = $request->sku ?? '';
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterSize::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = MasterSize::find($request->id);
        $update_data->size = $request->size ?? '';
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }


    public function getSizes(){
        $data = MasterSize::where('status',1)->get();
        return $data;
    }
    public function delete(Request $request){
        $data = MasterSize::where('id',$request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }

}