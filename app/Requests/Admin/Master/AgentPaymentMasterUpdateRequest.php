<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class AgentPaymentMasterUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:agent_payment_masters,id',
            'name' => 'required|string|max:255|unique:agent_payment_masters,name,' . $this->id,
        ];
    }
}
