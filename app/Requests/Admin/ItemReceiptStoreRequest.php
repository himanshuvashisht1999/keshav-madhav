<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ItemReceiptStoreRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(Request $request){
        return [
            'vendor_id' => 'required',
            'truck_number' => 'required',
            'time' => 'required',
            'box' => 'required',
            'received_by' => 'required',
            'shipment_photo' => 'required',
            'challan_photo' => 'required',
        ];
    }
    public function messages(){
        return [
        ];
    }
    public function attributes(){
        return [
        ];
    }
}
