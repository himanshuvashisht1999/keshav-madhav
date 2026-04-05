<?php

namespace App\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class FabricReceiptUpdateRequest extends FormRequest{
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
    public function rules()
    {
        return [
            'receipt_id' => 'required',
            'vendor_id' => 'required',
            'bill_no' => 'required|string|unique:fabric_receipts,bill_no,' . $this->receipt_id,
            'time' => 'required',
            'amount' => 'required',
            'gst_percentage' => 'required',
            // 'truck_number' => 'required',
            // 'roll' => 'required',
            // 'received_by' => 'required',
        ];
    }

    public function messages(){
        return [

        ];
    }

    public function attributes(){
        return [
        ];
    }
}
