<?php

namespace App\Requests\Admin\Master;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ProductionGoodsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Request $request)
    {
        $companyId = $request->company_id;

        // Base rules common for all companies
        $rules = [
            'design_number' => 'required|unique:production_goods,design_number,' . $request->id,
            //'name_of_garment'  => 'required',
            // 'sku'              => 'required|unique:production_goods,sku,' . $request->id,
        ];

        // If company is GENERAL (1) → extra fields required
        if ($companyId == 1) {
            $rules = array_merge($rules, [
                'type_of_garment' => 'required',
                'garment_pattern' => 'required',
                'master_size_id' => 'required',
                'master_color_id' => 'required',
            ]);
        }

        // If company is ROYAL (2) → these fields are not required
        // so we simply don't add them

        return $rules;
    }

    public function messages()
    {
        return [
            'design_number.required' => 'Design number is required.',
            'design_number.unique' => 'Design number already exists.',
            'name_of_garment.required' => 'Product name is required.',
            'type_of_garment.required' => 'Product type is required for General company.',
            'garment_pattern.required' => 'Product pattern is required for General company.',
            'master_size_id.required' => 'Product size is required for General company.',
            'master_color_id.required' => 'Product color is required for General company.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already used by another product.',
        ];
    }

    public function attributes()
    {
        return [
            // optional: pretty labels
            'design_number' => 'design number',
            'name_of_garment' => 'product name',
            'type_of_garment' => 'product type',
            'garment_pattern' => 'product pattern',
            'master_size_id' => 'product size',
            'master_color_id' => 'product color',
            'sku' => 'SKU',
        ];
    }
}
