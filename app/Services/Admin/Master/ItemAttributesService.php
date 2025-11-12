<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\ItemAttribute;
use App\Http\DataTable\Admin\Master\ItemAttributesDataTable as DataTable;

class ItemAttributesService {
    public function __construct(
        DataTable $datatable,
        ItemAttribute $item_attributes
    ) {
        $this->datatable= $datatable;
        $this->item_attributes= $item_attributes;
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
        $save_data = new ItemAttribute;
        $save_data->name = $request->name;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = ItemAttribute::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ItemAttribute::find($request->id);
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
        $data = ItemAttribute::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}