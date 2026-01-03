<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterProductFitting;
use App\Http\DataTable\Admin\Master\MasterFittingDataTable as DataTable;

class MasterFittingService {
    public function __construct(
        DataTable $datatable,
        MasterProductFitting $MasterProductFitting
    ) {
        $this->datatable= $datatable;
        $this->MasterProductFitting= $MasterProductFitting;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){

        $save_data = new MasterProductFitting;
        $save_data->name = $request->name;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterProductFitting::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = MasterProductFitting::find($request->id);
        // if($request->file('image')){
        //     $oldImageName = $update_data->getRawOriginal('image');
        //     if ($oldImageName) {
        //         $oldImagePath = public_path('assets/services/' . $oldImageName);
        //         if (file_exists($oldImagePath)) {
        //             unlink($oldImagePath);
        //         }
        //     }
        //     $image = $request->file('image');
        //     $extImage = $image->getClientOriginalExtension();
        //     $imgName = "service-".rand()."_".time().".".$extImage;
        //     $destinationPath = public_path().'/assets/services';
        //     $image->move($destinationPath, $imgName);
        //     $update_data->image = $imgName;
        // }
        $update_data->name = $request->name;
        // $update_data->sku = $request->sku;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = MasterProductFitting::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}