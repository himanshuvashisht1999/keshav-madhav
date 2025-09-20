<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class FabricReceiptStoreRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request){
        // dd($this);
        return [
            'sku'    => 'required|unique:fabric_receipts,sku',
            'vendor_id' => 'required',
            'truck_number' => 'required',
            'time' => 'required',
            'roll' => 'required',
            'received_by' => 'required',
            'shipment_photo' => 'required',
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
