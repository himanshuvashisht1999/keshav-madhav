<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class ConsumableGoodUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:consumable_goods,id',
            'name' => 'required|string|max:255|unique:consumable_goods,name,' . $this->id,
        ];
    }
}
