<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class FactoryHeadUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:factory_head_masters,id',
            'name' => 'required|string|unique:factory_head_masters,name,' . $this->id,
            'balance' => 'required|numeric',
            'balance_type' => 'required|string|in:Credit,Debit',
        ];
    }
}
