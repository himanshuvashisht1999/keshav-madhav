<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class MachineryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:machinery_masters,id',
            'name' => 'required|string|unique:machinery_masters,name,' . $this->id,
            'balance' => 'required|numeric',
            'balance_type' => 'required|string|in:Credit,Debit',
        ];
    }
}
