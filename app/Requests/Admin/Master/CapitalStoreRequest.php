<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CapitalStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:capital_masters,name',
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'nullable|string|in:Credit,Debit',
        ];
    }
}
