<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class ElectricityExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:electricity_expenses,id',
            'name' => 'required|string|max:255|unique:electricity_expenses,name,' . $this->id,
        ];
    }
}
