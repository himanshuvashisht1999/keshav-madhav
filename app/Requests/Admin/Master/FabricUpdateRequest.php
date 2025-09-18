<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class FabricUpdateRequest extends FormRequest{
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
        $id = $request->id;
        return [
            'name' => 'required',
            'sku'    => 'required|unique:fabrics,sku,' . $id,
            'dye_id' => 'required',
            'width_id' => 'required',
            'weave_type_id' => 'required',
            'gsm_id' => 'required',
            'composition_id' => 'required',
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
