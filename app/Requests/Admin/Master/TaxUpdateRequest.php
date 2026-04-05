<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class TaxUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:taxes,id',
            'name' => 'required|string|max:255|unique:taxes,name,' . $this->id,
            'percentage' => 'required|numeric|min:0|max:100',
        ];
    }
}
