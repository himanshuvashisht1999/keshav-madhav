<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderMaterialStoreRequest extends FormRequest{

public function authorize(){
  return true;
}
public function rules(Request $request){
  return[
    'date'=> 'required|date',
    'vendor_id'=>'required|numeric|min:1',
    'delivery_date'=>'required|date|after_or_equal:date',
    'is_notify'=>'required|in:0,1',
    'items.*.item_sku'=>'required',
    'items.*.quantity'=>'required|integer|min:1',
    'items.*.price'=>'required|numeric|min:0.01',
  ];
}

public function messages(){
  return [
    'items.*.quantity.min' => 'Quantity must be greater than 0',
    'items.*.price.min' => 'Price must be greater than 0',
    'delivery_date.after_or_equal' => 'Delivery date must be after or equal to PO date',
  ];
}

public function attributes(){
  return [];
}

}
