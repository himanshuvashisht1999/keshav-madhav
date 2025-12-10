<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class SizeMeasurementUpdateRequest extends FormRequest{
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
            'name' => 'required|unique:master_size_measurements,name,' . $request->id,
            'size_group' => 'required',
            'status' =>'required',
            // 'sku'    => 'required|unique:master_size_measurements,sku,' . $request->id,
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
