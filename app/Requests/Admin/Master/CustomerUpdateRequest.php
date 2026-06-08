<?php

namespace App\Requests\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        $rules = [
            'name' => 'required',
            'balance' => 'nullable|numeric',
            'balance_type' => 'required|in:Credit,Debit',
            'payment_term_days' => 'nullable|integer',
        ];

        // If it's an existing record, we might need to check if it's a shop (parent_id exists)
        $customer = \App\Models\MasterCustomer::find($this->id);

        if (($this->type == 'domestic' && $this->subtype == 'agent') || ($customer && $customer->parent_id)) {
            $rules['name'] = 'nullable';
            $rules['shop_name'] = 'required';
            // $rules['shop_phone'] = 'required';
        }

        if ($this->type == 'domestic' && $this->subtype == 'direct') {
            $rules['brand_discounts'] = 'nullable|array';
            $rules['brand_discounts.*'] = 'nullable|numeric|min:0|max:100';
        }

        return $rules;
    }

    public function messages()
    {
        return [

        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
