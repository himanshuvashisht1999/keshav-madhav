<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class HulayatiUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:hulayati_masters,id',
            'name' => 'required|string|unique:hulayati_masters,name,' . $this->id,
        ];
    }
}
