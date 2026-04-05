<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTypeUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:payment_types,id',
            'name' => 'required|string|max:255|unique:payment_types,name,' . $this->id,
        ];
    }
}
