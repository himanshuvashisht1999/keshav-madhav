<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class MasterProductSubStageStoreRequest extends FormRequest{
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
            'name' => 'required|string|max:255',
            'sku'    => 'required|unique:master_product_sub_stages,sku',
            // 'status' =>'required',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Unit name is required',
            'sku.required' => 'SKU is required',
        ];
    }

    public function attributes(){
        return [
            'name' =>'Unit Name',
            'sku' =>'SKU',
        ];
    }
}
