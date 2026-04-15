<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class VendorUpdateRequest extends FormRequest{
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
        $id = $request->id;
        return [
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('vendors', 'name')->whereNot('status', 3)->ignore($id)
            ],
            'status' =>'required',
            'sku'    => 'nullable',
            'balance' => 'nullable|numeric',
            'type' => 'required|in:Credit,Debit',
            'purchase_agent_id' => 'nullable|exists:purchase_agents,id',
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
