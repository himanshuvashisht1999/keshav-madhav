<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ProductionGoodsStoreRequest extends FormRequest{
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
            'name_of_garments' => 'required',
            'master_material_id' => 'required',
            'master_color_id' => 'required',
            'master_size_id' => 'required',
            'master_design_id' => 'required',
            'fabric_sku' => 'required',
            'sku'    => 'required|unique:production_goods,sku',
            // 'status' =>'required',
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
