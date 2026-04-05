<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CommissionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:commissions,id',
            'name' => 'required|string|unique:commissions,name,' . $this->id,
            'percentage' => 'required|numeric|min:0|max:100',
        ];
    }
}
