<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ItemReceiptDetailStoreRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules(Request $request){
        return [
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
