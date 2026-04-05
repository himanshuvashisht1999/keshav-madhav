<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class SalesAgentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|exists:sales_agents,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sales_agents,email,' . $this->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string',
            'status' => 'required|in:1,0',
            'brand_discounts' => 'nullable|array',
            'brand_discounts.*' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
