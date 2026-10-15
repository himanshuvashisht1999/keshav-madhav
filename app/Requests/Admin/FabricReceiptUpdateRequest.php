<?php

namespace App\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FabricReceiptUpdateRequest extends FormRequest
{
    use FabricShipmentChecks;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge([
            'receipt_id' => 'required|integer|exists:fabric_receipts,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            // the same bill no may be used by different vendors, not twice by one vendor
            'bill_no' => ['required', 'string', \Illuminate\Validation\Rule::unique('fabric_receipts', 'bill_no')->where('vendor_id', (int) $this->vendor_id)->ignore((int) $this->receipt_id)],
            'time' => 'required',
            // 'truck_number' => 'required',
            // 'roll' => 'required',
            // 'received_by' => 'required',
        ], $this->shipmentRules());
    }

    public function messages()
    {
        return array_merge($this->shipmentMessages(), ['bill_no.unique' => 'This bill no is already entered for this vendor.']);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->checkShipment($validator, (int) $this->receipt_id);
        });
    }
}
