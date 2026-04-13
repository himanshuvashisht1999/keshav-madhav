<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CapitalUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:capital_masters,id',
            'name' => 'required|string|unique:capital_masters,name,' . $this->id,
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'nullable|string|in:Credit,Debit',
        ];
    }
}
