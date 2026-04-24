<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class LoanStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:loan_masters,name',
            'balance' => 'required|numeric',
            'balance_type' => 'required|string|in:Credit,Debit',
        ];
    }
}
