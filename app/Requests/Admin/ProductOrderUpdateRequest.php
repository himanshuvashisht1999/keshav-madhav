<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ProductOrderUpdateRequest extends FormRequest{
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
            // 'sku'    => 'required|unique:orders,sku',
            'expected_delivery_date' => 'required',
            'master_customer_id' => 'required',
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
