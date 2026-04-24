<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CommitteeStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'balance' => 'required|numeric',
            'balance_type' => 'required|string|in:Credit,Debit',
            'period' => 'nullable|string|max:255',
        ];
    }
}
