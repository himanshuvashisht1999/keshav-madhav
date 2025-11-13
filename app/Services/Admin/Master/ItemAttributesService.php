<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\ItemAttribute;
use App\Models\ItemAttributeValue;
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

    public function attributes(Request $request){
        $data = ItemAttribute::where('item_id',$request->item_id)->where('status',1)->get();
        return $data;
    }

    public function store(Request $request){
        $save_data = new ItemAttributeValue;
        $save_data->value = $request->value;
        $save_data->item_attribute_id = $request->item_attribute_id;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request){
        $data = ItemAttributeValue::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ItemAttributeValue::find($request->id);
        $update_data->value = $request->value;
        $update_data->item_attribute_id = $request->item_attribute_id;
        $update_data->save();
        return true;
    }

    public function delete(Request $request){
        $data = ItemAttributeValue::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}