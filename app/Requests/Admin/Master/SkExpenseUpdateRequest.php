<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class SkExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:sk_expenses,id',
            'name' => 'required|string|max:255|unique:sk_expenses,name,' . $this->id,
        ];
    }
}
