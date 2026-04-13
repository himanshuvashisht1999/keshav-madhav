<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CommitteeUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:committees,id',
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'period' => 'nullable|string|max:255',
        ];
    }
}
