<?php

namespace App\Requests\Admin\Master;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ProductionGoodsStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        $companyId = $request->company_id;

        // BASE RULES — always required
        $rules = [
            'design_number'    => 'required',
            'name_of_garment'  => 'required',
            'main_image'  => 'required',
            'sku'              => 'required|unique:production_goods,sku',
        ];

        // If company is GENERAL → general fields are required
        if ($companyId == 1) {
            $rules = array_merge($rules, [
                'type_of_garment' => 'required',
                'garment_pattern' => 'required',
                'master_size_id'  => 'required',
                'master_color_id' => 'required',
            ]);
        }

        // If company is ROYAL → general fields NOT required
        // So we simply DO NOT add them

        return $rules;
    }

    public function messages()
    {
        return [
            'design_number.required'     => 'Design number is required.',
            'name_of_garment.required'   => 'Product name is required.',
            'type_of_garment.required'   => 'Product type is required for General company.',
            'garment_pattern.required'   => 'Product pattern is required for General company.',
            'master_size_id.required'    => 'Product size is required for General company.',
            'master_color_id.required'   => 'Product color is required for General company.',
            'sku.required'               => 'SKU must be generated.',
            'sku.unique'                 => 'SKU already exists in the database.',
        ];
    }

    public function attributes()
    {
        return [];
    }
}
