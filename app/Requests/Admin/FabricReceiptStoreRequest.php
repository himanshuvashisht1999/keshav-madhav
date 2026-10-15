<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class FabricReceiptStoreRequest extends FormRequest{
    use FabricShipmentChecks;

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
    public function rules(Request $request){
        // dd($this);
        return array_merge([
            // 'sku'    => 'required|unique:fabric_receipts,sku',
            'vendor_id' => 'required|integer|exists:vendors,id',
            // the same bill no may be used by different vendors, not twice by one vendor
            'bill_no' => ['required', 'string', \Illuminate\Validation\Rule::unique('fabric_receipts', 'bill_no')->where('vendor_id', (int) $this->vendor_id)],
            // 'truck_number' => 'required',
            'time' => 'required',
            'purchase_order_id' => 'nullable|integer',
            //'roll' => 'required',
            // 'received_by' => 'required',
            // 'shipment_photo' => 'required',
            // 'challan_photo' => 'required',
        ], $this->shipmentRules());
    }

    public function messages()
    {
        return array_merge($this->shipmentMessages(), ['bill_no.unique' => 'This bill no is already entered for this vendor.']);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->checkShipment($validator);
        });
    }
}
