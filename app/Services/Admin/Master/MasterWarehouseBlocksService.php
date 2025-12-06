<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterWarehouseBlock;
use App\Requests\Admin\Master\MasterWarehouseBlocksStoreRequest;
use App\Requests\Admin\Master\MasterWarehouseBlocksUpdateRequest;
use App\Http\DataTable\Admin\Master\MasterWarehouseBlocksDataTable as DataTable;

class MasterWarehouseBlocksService {
    public function __construct(
        DataTable $datatable,
        MasterWarehouseBlock $warehouse_blocks
    ) {
        $this->datatable= $datatable;
        $this->warehouse_blocks = $warehouse_blocks;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(MasterWarehouseBlocksStoreRequest $request){
        // if($request->file('image')){
        //     $image = $request->file('image');
        //     $extImage = $image->getClientOriginalExtension();
        //     $imgName = "service-".rand()."_".time().".".$extImage;
        //     $destinationPath = public_path().'/assets/services';
        //     $image->move($destinationPath, $imgName);
        // }
        $save_data = new MasterWarehouseBlock;
        $save_data->name = $request->name;
        $save_data->sku = $request->sku;
        $save_data->master_warehouse_id = $request->master_warehouse_id;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = MasterWarehouseBlock::where('id',$request->id)->first();
        return $data;
    }
    public function update(MasterWarehouseBlocksUpdateRequest $request){
        $update_data = MasterWarehouseBlock::find($request->id);
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
        $update_data->master_warehouse_id = $request->master_warehouse_id;
        // $update_data->sku = $request->sku;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = MasterWarehouseBlock::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function getMasterWarehouseWithRacks(){
        return MasterWarehouseBlock::with('masterWarehouse')->where('status',1)->get();
    }
}