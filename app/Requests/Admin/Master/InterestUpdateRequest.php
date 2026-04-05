<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class InterestUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:interests,id',
            'name' => 'required|string|max:255|unique:interests,name,' . $this->id,
            'percentage' => 'required|numeric|min:0|max:100',
        ];
    }
}
