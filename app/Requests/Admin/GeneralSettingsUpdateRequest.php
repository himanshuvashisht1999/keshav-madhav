<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsUpdateRequest extends FormRequest{
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
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'title' => 'required',
            'meta_title' =>'required',
            'meta_keywords' =>'required',
            'meta_description' =>'required',
            'agent_app_show_stock' => 'nullable|boolean',
            'agent_app_allow_over_stock' => 'nullable|boolean',
            'agent_app_allow_over_stock_sample' => 'nullable|boolean',
            // 'header_script' => 'required',
            // 'footer_script' => 'required',
            // 'facebook' => 'required',
            // 'twitter' => 'required',
            // 'instagram' => 'required',
            // 'linkedin' => 'required',
            // 'android_app' => 'required',
            // 'ios_app' => 'required',
            
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
