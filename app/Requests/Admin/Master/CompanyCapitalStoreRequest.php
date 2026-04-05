<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCapitalStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'payment_method_type' => 'required|string|in:Bank,Cash',
            'payment_method_id' => 'required|integer',
            'transaction_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}
