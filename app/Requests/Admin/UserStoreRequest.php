<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest{
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
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'gender' =>'required',
            'password' => 'required|min:6', 
            'confirm_password' => 'required|same:password', 
            'image' =>'required',
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
