<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderStoreRequest extends FormRequest{
    use ShowsErrorPopup;

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
            // 'sku'    => 'required|unique:purchase_orders,sku',
            'date' => 'required',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'delivery_date' => 'required',
            'fabric_warehouse_id' => 'required',
            'master_company_id' => 'required',
            'fabrics' => 'required|array|min:1',
            'fabrics.*.fabric_id' => 'required|integer|exists:fabrics,id',
            'fabrics.*.meter' => 'required|numeric|gt:0',
            'fabrics.*.price' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(){
        return [
            'fabrics.required' => 'Add at least one fabric line.',
        ];
    }

    public function attributes(){
        return [
            'fabrics.*.fabric_id' => 'fabric',
            'fabrics.*.meter' => 'meters',
            'fabrics.*.price' => 'price',
        ];
    }
}
