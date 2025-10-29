<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterSizeSelection;
use App\Http\DataTable\Admin\Master\SizeMeasurementDataTable as DataTable;

class SizeMeasurementService {
    public function __construct(
        DataTable $datatable,
        MasterSizeMeasurement $master_size_measurement
    ) {
        $this->datatable= $datatable;
        $this->master_size_measurement= $master_size_measurement;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){

        $save_data = new MasterSizeMeasurement;
        $save_data->size_selection = $request->size_selection;
        $save_data->measurement = $request->measurement;
        $save_data->sku = $request->sku;
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterSizeMeasurement::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = MasterSizeMeasurement::find($request->id);
        $update_data->size_selection = $request->size_selection;
        $update_data->measurement = $request->measurement;
        $update_data->status = $request->status;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = MasterSizeMeasurement::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function size_selections(){
        $data = MasterSizeMeasurement::where('status',1)->get();
        return $data;
    }

}