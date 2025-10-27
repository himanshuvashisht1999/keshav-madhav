<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class SizeMeasurementStoreRequest extends FormRequest{
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
            'size_selection' => 'required',
            'measurement' => 'required',
            'status' =>'required',
            'sku'    => 'required|unique:fabric_dye,sku',
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
