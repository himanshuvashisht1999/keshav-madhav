<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterColorsStoreRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(){
        $name = trim($this->input('name') ?? '');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('master_colors', 'name')->where(function ($query) use ($name) {
                    return $query->where('status', 1)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)]);
                }),
            ],
            'sku'    => 'nullable',
            'status' => 'required',
        ];
    }

    public function messages(){
        return [
            'name.unique' => 'This color name already exists in the master list.',
        ];
    }

    public function attributes(){
        return [
        ];
    }
}
