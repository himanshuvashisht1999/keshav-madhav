<?php

namespace App\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class TourExpenseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:tour_expenses,id',
            'name' => 'required|string|max:255|unique:tour_expenses,name,' . $this->id,
        ];
    }
}
