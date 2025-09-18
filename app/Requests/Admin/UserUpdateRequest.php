<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest{
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
            'first_name' => 'required',
            'branch_id' =>'required',
            'email' =>'required|email|unique:users,email,'.$request->id.",id",
            'phone' =>'required|unique:users,phone,'.$request->id.",id",
            'gender' =>'required',
            // 'image' =>'required',
            'address' =>'required',
            'role_id' =>'required',
            'status' =>'required',
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
