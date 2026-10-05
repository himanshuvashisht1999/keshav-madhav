<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterColorsUpdateRequest extends FormRequest{
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
        $id = $this->id;
        $name = trim($this->input('name') ?? '');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('master_colors', 'name')->where(function ($query) use ($name) {
                    return $query->where('status', 1)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)]);
                })->ignore($id),
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
