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
        // Generate SKU based on name,  check sku name exists or not in table
        $raw_sku = $request->name."-". $request->customer_id."-". $request->design_number;
        $sku = strtoupper($raw_sku);

        // Check if SKU already exists
        if (MasterSizeMeasurement::where('sku', $sku)->exists()) {
            return back()->with('error', 'SKU already exists, please choose a different name.');
        }
        $save_data = new MasterSizeMeasurement;
        $save_data->name = $request->set_size;
        $save_data->corporate_company_id = $request->customer_id;
        $save_data->design_number = $request->design_number;
        $save_data->no_of_pcs = $request->no_of_pcs;
        $save_data->set_size = $request->set_size;
        $save_data->size_group =  implode(',', $request->size_group);

        // $save_data->size_type = $request->size_type ?? 0;
        // $save_data->size_selection = $request->size_selection;
        // // for size type set size_selection as to and measurement as from
        // $save_data->measurement = $request->measurement;
        $save_data->sku = $sku; // convert upper case
        $save_data->status = $request->status;
        $save_data->save();
        return true;
    }   

    public function edit(Request $request){
        $data = MasterSizeMeasurement::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        // Generate SKU based on name,  check sku name exists or not in table
        $raw_sku = $request->name."-". $request->customer_id."-". $request->design_number;
        $sku = strtoupper($raw_sku);

        // Check if SKU already exists
        if (MasterSizeMeasurement::where('id', '!=', $request->id)
                ->where('sku', $sku)
                ->exists()) {

            return back()->with('error', 'SKU already exists, please choose a different name.');
        }
        $update_data = MasterSizeMeasurement::find($request->id);
        $update_data->name = $request->set_size;
        $update_data->corporate_company_id = $request->customer_id;
        $update_data->design_number = $request->design_number;
        $update_data->no_of_pcs = $request->no_of_pcs;
        $update_data->set_size = $request->set_size;
       
        // $update_data->size_group =  $request->size_group ? str_replace(' ', '', $request->size_group) : '';
        $update_data->size_group =   implode(',', $request->size_group);
        $update_data->sku = strtoupper($request->name); // convert upper case
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

    public function getSelectedSizes(Request $request){
        $data = MasterSizeMeasurement::where('status',1)->get();
        return $data;
    }
}