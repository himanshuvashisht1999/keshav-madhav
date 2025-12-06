<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class MasterWarehouseBlocksUpdateRequest extends FormRequest{
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
            'name' => 'required',
            'sku'    => 'required|unique:master_warehouse_blocks,sku,' . $request->id,
            'master_warehouse_id' => 'required',
            // 'status' =>'required',
        ];
    }

    public function messages(){
        return [
            'master_warehouse_id.required' => 'The warehouse field is required.',
        ];
    }

    public function attributes(){
        return [
            'master_warehouse_id' => 'warehouse',
        ];
    }
}
