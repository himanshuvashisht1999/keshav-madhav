<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class ContractorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:contractors,id',
            'name' => 'required|string|max:255|unique:contractors,name,' . $this->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'required|numeric',
            'balance_type' => 'required|string|in:Credit,Debit',
        ];
    }
}
