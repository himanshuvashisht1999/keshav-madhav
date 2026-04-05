<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class MasterFabricWarehouseStoreRequest extends FormRequest{
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
            'cutting_master_name' => [
                'required',
                \Illuminate\Validation\Rule::unique('master_fabric_warehouse', 'cutting_master_name')->whereNot('status', 3)
            ],
            'status' =>'required',
            'sku'    => 'nullable',
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
