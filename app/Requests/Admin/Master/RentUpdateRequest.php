<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class RentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:rents,id',
            'name' => 'required|string|max:255|unique:rents,name,' . $this->id,
        ];
    }
}
