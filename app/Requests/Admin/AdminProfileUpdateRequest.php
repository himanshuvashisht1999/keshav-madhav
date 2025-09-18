<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class AdminProfileUpdateRequest extends FormRequest{
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
    public function rules(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            // 'last_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ];
    
        if ($request->filled('password')) {
            $rules['password'] = 'required|min:6';
            $rules['confirm_password'] = 'required|same:password';
        }
    
        return $rules;
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
