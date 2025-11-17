<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\ItemAttribute;
use App\Models\ItemAttributeValue;
use App\Models\ItemAttributeValueDetails;

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
        
        $data = ItemAttribute::where('item_id',$request->id)->where('status',1)->get();
        return $data;
    }

    public function store(Request $request){
        $save_data = new ItemAttributeValue;
        $save_data->item_id = $request->id;
        $save_data->sku = $request->sku;
        $save_data->status = 1;
        if ($save_data->save()) {
            foreach($request->value as $key => $val){
                $attribute_sku = $key . "-" . strtoupper(str_replace(' ', '', $val));

                $save_data_details = new ItemAttributeValueDetails;
                $save_data_details->item_attribute_value_id = $save_data->id;
                $save_data_details->value = $val;
                $save_data_details->sku = $attribute_sku;
                $save_data_details->status = 1;
                $save_data_details->save();
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function edit(Request $request){
        $data = ItemAttributeValue::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ItemAttributeValue::find($request->id);
        $update_data->status = $request->status;
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