<?php

namespace App\Requests\Admin;

use App\Models\FabricReceiptDetail;
use App\Models\PurchaseOrder;
use App\Services\Admin\FabricReceiptService;

/**
 * Checks shared by the new / edit Fabric Shipment forms. The screens already do the same checks in the
 * browser; here they are enforced on the server too, so nothing wrong can be saved.
 */
trait FabricShipmentChecks
{
    use ShowsErrorPopup;

    protected function shipmentRules(): array
    {
        return [
            'master_fabric_warehouse_id' => 'required|integer|exists:master_fabric_warehouse,id',
            'amount' => 'required|numeric|min:0',
            'gst_percentage' => 'required|numeric|min:0|max:100',
            'gst_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric|min:0',
            'total_meter' => 'nullable|numeric|min:0',
            'roll_details' => 'required|array|min:1',
            'roll_details.*.detail_id' => 'nullable|integer',
            'roll_details.*.fabric_id' => 'required|integer|exists:fabrics,id',
            'roll_details.*.roll_no' => 'required|max:255',
            'roll_details.*.meter' => 'required|numeric|gt:0',
            'roll_details.*.price' => 'nullable|numeric|min:0',
            'shipment_photo' => 'nullable|' . FabricReceiptService::UPLOAD_RULE,
            'challan_photo' => 'nullable|' . FabricReceiptService::UPLOAD_RULE,
            'other_images' => 'nullable|array',
            'other_images.*' => FabricReceiptService::UPLOAD_RULE,
        ];
    }

    protected function shipmentMessages(): array
    {
        return [
            'roll_details.required' => 'Add at least one roll.',
            'roll_details.*.meter.gt' => 'Every roll must have meters more than 0.',
            'roll_details.*.price.min' => 'A roll price cannot be below 0.',
            'roll_details.*.roll_no.required' => 'Every roll needs a roll no.',
        ];
    }

    public function attributes()
    {
        return [
            'master_fabric_warehouse_id' => 'warehouse',
            'gst_percentage' => 'GST %',
            'roll_details.*.fabric_id' => 'roll fabric',
            'roll_details.*.roll_no' => 'roll no',
            'roll_details.*.meter' => 'roll meter',
            'roll_details.*.price' => 'roll price',
            'shipment_photo' => 'shipment photo',
            'challan_photo' => 'challan',
            'other_images.*' => 'other image',
        ];
    }

    /**
     * Totals must match the rolls, a roll no must not repeat for one fabric inside this shipment
     * (other shipments may use the same roll no), a PO must belong to the vendor.
     */
    protected function checkShipment($validator, int $receiptId = 0): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }
        $rolls = array_values((array) $this->input('roll_details', []));
        $existing = $receiptId
            ? FabricReceiptDetail::where('fabric_receipt_id', $receiptId)->get(['id', 'fabric_id', 'roll_number'])->keyBy('id')
            : collect();
        $seen = [];
        $sumMeter = 0;
        $sumAmount = 0;
        foreach ($rolls as $roll) {
            $meter = (float) $roll['meter'];
            $sumMeter += $meter;
            $sumAmount += round($meter * (float) ($roll['price'] ?? 0), 2);

            $no = trim((string) $roll['roll_no']);
            $fabricId = (int) $roll['fabric_id'];
            $key = $fabricId . '|' . mb_strtolower($no);
            if (isset($seen[$key])) {
                $validator->errors()->add('roll_details', "Roll no {$no} is entered twice for the same fabric.");
                return;
            }
            $seen[$key] = true;

            $detailId = (int) ($roll['detail_id'] ?? 0);
            $old = $detailId ? $existing->get($detailId) : null;
            if ($detailId && !$old) {
                $validator->errors()->add('roll_details', 'A roll of this shipment was not found. Please open the shipment again.');
                return;
            }
        }

        $amount = (float) $this->input('amount');
        if (abs($sumAmount - $amount) > 0.05 + 0.005 * count($rolls)) {
            $validator->errors()->add('amount', 'Amount (' . number_format($amount, 2) . ') must equal the total of the rolls (' . number_format($sumAmount, 2) . ').');
        }
        if ($this->filled('total_meter') && abs($sumMeter - (float) $this->input('total_meter')) > 0.01) {
            $validator->errors()->add('total_meter', 'Total meter (' . (float) $this->input('total_meter') . ') must equal the meters of the rolls (' . round($sumMeter, 2) . ').');
        }
        $parts = $amount + (float) $this->input('gst_amount', 0) + (float) $this->input('other_charges', 0);
        if ($this->filled('total_amount') && abs((float) $this->input('total_amount') - $parts) > 0.51) {
            $validator->errors()->add('total_amount', 'Total amount must be Amount + GST amount + Other charges (' . number_format($parts, 2) . ').');
        }
        if ($this->filled('total_amount') ? (float) $this->input('total_amount') < 0 : $parts < 0) {
            $validator->errors()->add('total_amount', 'Total amount cannot be below 0.');
        }

        $poId = (int) $this->input('purchase_order_id');
        if ($poId) {
            $po = PurchaseOrder::find($poId);
            if (!$po || (int) $po->status !== 1 || $po->is_closed) {
                $validator->errors()->add('purchase_order_id', 'The selected PO is not open any more. Please choose another PO.');
            } elseif ((int) $po->vendor_id !== (int) $this->input('vendor_id')) {
                $validator->errors()->add('vendor_id', 'The vendor must be the vendor of the selected PO.');
            }
        }
    }
}
