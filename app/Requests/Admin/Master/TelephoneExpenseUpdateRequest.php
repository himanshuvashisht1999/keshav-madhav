<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class TelephoneExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:telephone_expenses,id',
            'name' => 'required|string|max:255|unique:telephone_expenses,name,' . $this->id,
        ];
    }
}
