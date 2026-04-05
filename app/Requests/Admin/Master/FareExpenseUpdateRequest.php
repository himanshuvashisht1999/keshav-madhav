<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class FareExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:fare_expenses,id',
            'name' => 'required|string|max:255|unique:fare_expenses,name,' . $this->id,
        ];
    }
}
