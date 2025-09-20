<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\Item;
use App\Http\DataTable\Admin\Master\VendorDataTable as DataTable;

class VendorService {
    public function __construct(
        DataTable $datatable,
        Vendor $vendor
    ) {
        $this->datatable= $datatable;
        $this->vendor= $vendor;
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
        $save_data = new Vendor;
        $save_data->name = $request->name;
        $save_data->items = serialize($request->items);
        $save_data->phone = $request->phone;
        $save_data->email = $request->email;
        $save_data->address = $request->address;
        $save_data->sku = $request->sku;
        // $save_data->image = $imgName;
        $save_data->status = $request->status;
        $save_data->description = $request->description;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = Vendor::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = Vendor::find($request->id);
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
        $update_data->items = serialize($request->items);
        $update_data->phone = $request->phone;
        $update_data->email = $request->email;
        // $update_data->sku = $request->sku;
        $update_data->address = $request->address;
        $update_data->status = $request->status;
        $update_data->description = $request->description;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = Vendor::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function items(){
        $data = Item::where('status',1)->get();
        return $data;
    }

}