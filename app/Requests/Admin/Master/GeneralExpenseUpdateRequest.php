<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class GeneralExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:general_expenses,id',
            'name' => 'required|string|max:255|unique:general_expenses,name,' . $this->id,
        ];
    }
}
