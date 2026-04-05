<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CuttingPaymentMasterUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:cutting_payment_masters,id',
            'name' => 'required|string|max:255|unique:cutting_payment_masters,name,' . $this->id,
        ];
    }
}
