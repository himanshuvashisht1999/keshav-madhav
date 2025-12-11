<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ProductOrderStoreRequest extends FormRequest{
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
            'designList' => 'required|array|min:1',
            'designList.*' => 'required|exists:production_goods,id',
            'colourList' => 'required|array|min:1',
            'colourList.*' => 'required',
            'sizeList' => 'required|array|min:1',
            'sizeList.*' => 'required',
            'product_quantity' => 'required|array|min:1',
            'product_quantity.*' => 'required|integer|min:1',
            'corporate_order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
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
