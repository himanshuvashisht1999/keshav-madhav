<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Fabric;
use App\Models\FabricDye;
use App\Models\FabricWidth;
use App\Models\FabricWeave;
use App\Models\FabricComposition;
use App\Models\FabricGsm;
use App\Http\DataTable\Admin\Master\FabricDataTable as DataTable;

class FabricService {
    public function __construct(
        DataTable $datatable,
        Fabric $fabric
    ) {
        $this->datatable= $datatable;
        $this->fabric = $fabric;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        // if($request->file('image')){
        //     $image = $request->file('image');
        //     $extImage = $image->getClientOriginalExtension();
        //     $imgName = "service-".rand()."_".time().".".$extImage;
        //     $destinationPath = public_path().'/assets/services';
        //     $image->move($destinationPath, $imgName);
        // }
        $save_data = new Fabric;
        $save_data->sku = $request->sku;
        $save_data->name = $request->name;
        $save_data->dye_id = $request->dye_id;
        $save_data->width_id = $request->width_id;
        $save_data->weave_type_id = $request->weave_type_id;
        $save_data->gsm_id = $request->gsm_id;
        $save_data->composition_id = $request->composition_id;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = Fabric::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = Fabric::find($request->id);
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
        // $update_data->sku = $request->sku;
        $update_data->name = $request->name;
        $update_data->dye_id = $request->dye_id;
        $update_data->width_id = $request->width_id;
        $update_data->weave_type_id = $request->weave_type_id;
        $update_data->gsm_id = $request->gsm_id;
        $update_data->composition_id = $request->composition_id;
        $update_data->status = 1;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = Fabric::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function fab_width_data(){
        $data = FabricWidth::where('status',1)->get();
        return $data;
    }
    public function fab_dye_data(){
        $data = FabricDye::where('status',1)->get();
        return $data;
    }
    public function fab_gsm_data(){
        $data = FabricGsm::where('status',1)->get();
        return $data;
    }
    public function fab_weave_data(){
        $data = FabricWeave::where('status',1)->get();
        return $data;
    }
    public function fab_composition_data(){
        $data = FabricComposition::where('status',1)->get();
        return $data;
    }

}