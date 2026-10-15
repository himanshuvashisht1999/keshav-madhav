<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use DB;
use App\Models\PurchaseOrder;
use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;
use App\Models\Fabric;
use App\Models\Vendor;
use App\Models\Stock;
use App\Models\StockExpend;
use App\Models\MasterProductSubStage;
use App\Models\PurchaseOrderItem;
use App\Models\MasterFabricWarehouse;
use App\Http\DataTable\Admin\FabricReceiptDataTable as DataTable;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\FabricReturn;
use App\Models\FabricReturnDetail;
use PDF;

class FabricReceiptService
{
    protected $datatable;
    protected $fabric_receipt;

    public function __construct(
        DataTable $datatable,
        FabricReceipt $fabric_receipt
    ) {
        $this->datatable = $datatable;
        $this->fabric_receipt = $fabric_receipt;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {

        return $this->datatable->indexList($request);
    }

    /** Challan / shipment photo / other images: pictures or PDF only (never a file the server could run). */
    const UPLOAD_RULE = 'file|mimes:jpg,jpeg,png,gif,webp,bmp,heic,heif,pdf|max:20480';
    const UPLOAD_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'heic', 'heif', 'pdf'];

    public function store(Request $request)
    {
        $po_id = (int) $request->purchase_order_id;
        $moved = []; // files put in public/, removed again if the shipment is not saved

        DB::beginTransaction();
        try {
            if (!$po_id) {
                // Same logic as PurchaseOrderService::store
                $new_po = new PurchaseOrder;
                $new_po->sku = '';
                $new_po->date = date('Y-m-d');
                $new_po->vendor_id = $request->vendor_id;
                $new_po->delivery_date = date('Y-m-d');
                $new_po->status = 1;
                $new_po->save();

                $date_po = \Carbon\Carbon::parse($new_po->date)->format('dmY');
                $sku_po = "PO/" . $date_po . "/" . $new_po->id;
                $new_po->update(['sku' => $sku_po]);
                $po_id = $new_po->id;

                // Group rolls by fabric to create PO items
                $group_fabrics = [];
                foreach ($request->roll_details as $roll) {
                    $fid = $roll['fabric_id'];
                    $mtr = (float) $roll['meter'];
                    $prc = (float) ($roll['price'] ?? 0);
                    if (!isset($group_fabrics[$fid])) {
                        $group_fabrics[$fid] = ['meter' => 0, 'price' => $prc];
                    }
                    $group_fabrics[$fid]['meter'] += $mtr;
                }

                foreach ($group_fabrics as $fid => $fab_info) {
                    $fabric = Fabric::find($fid);
                    if ($fabric) {
                        $po_item = new PurchaseOrderItem;
                        $po_item->purchase_order_id = $po_id;
                        $po_item->fabric_sku = $fabric->sku;
                        $po_item->fabric_id = $fabric->id;
                        $po_item->meter = $fab_info['meter'];
                        $po_item->remaining_quantity = $fab_info['meter']; // Will be reduced in the loop below
                        $po_item->price = $fab_info['price'];
                        $po_item->total_price = $fab_info['meter'] * $fab_info['price'];
                        $po_item->status = 1; // Mark as active
                        $po_item->save();
                    }
                }
                $new_po->update(['status' => 1]); // Marked as active so it shows in lists
            }

            $imgName = '';
            if ($request->file('shipment_photo')) {
                $imgName = $this->saveUpload($request->file('shipment_photo'), 'shipment-image', 'shipment-image', $moved);
            }
            $imgName2 = '';
            if ($request->file('challan_photo')) {
                $imgName2 = $this->saveUpload($request->file('challan_photo'), 'challan-image', 'challan-image', $moved);
            }
            $save_data = new FabricReceipt;
            $save_data->sku = '';
            $save_data->vendor_id = $request->vendor_id;
            $save_data->bill_no = $request->bill_no;
            $save_data->truck_number = $request->truck_number ?? '';
            $save_data->time = $request->time;
            $save_data->roll = count($request->roll_details);
            $save_data->received_by = $request->received_by ?? '';
            $save_data->amount = $request->amount ?? 0.00;
            $save_data->gst_amount = $request->gst_amount ?? 0.00;
            $save_data->gst_percentage = $request->gst_percentage ?? 1;
            $save_data->total_amount = $request->total_amount ?? 0.00;
            $save_data->other_charges = $request->other_charges ?? 0.00;
            $save_data->total_meter = $request->total_meter ?? 0.00;
            $save_data->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
            $save_data->shipment_photo = $imgName;
            $save_data->challan_photo = $imgName2;
            $save_data->status = 1;
            $save_data->save();

            $date = \Carbon\Carbon::parse($request->date)->format('dmY');
            $sku = "FR/" . $date . "/" . $save_data->id;
            $shipment_id = "SHP/" . $date . "/" . $save_data->id;
            $save_data->update([
                'sku' => $sku,
                'shipment_id' => $shipment_id
            ]);

            if ($request->hasFile('other_images')) {
                foreach ($request->file('other_images') as $image) {
                    \App\Models\FabricReceiptOtherImage::create([
                        'fabric_receipt_id' => $save_data->id,
                        'image' => $this->saveUpload($image, 'other-images', 'other-image', $moved)
                    ]);
                }
            }


            if ($save_data->vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($save_data->vendor_id);

                if ($vendor) {
                    $vendor->balance += $save_data->total_amount;
                    $vendor->save();
                }
            }

            // a roll goes to the first PO line of its fabric that still has meters to receive
            $po_lines = FabricPurchaseLink::openLines($po_id);
            $linked_lines = [];
            foreach ($request->roll_details as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if ($fab_data) {
                    $meter = (float) $single_data['meter'];
                    $price = $single_data['price'] ?? 0;
                    $roll_number = $single_data['roll_no'];

                    $po_item_id = FabricPurchaseLink::pickLine($po_lines, (int) $fab_data->id, $meter);
                    if ($po_item_id) {
                        $linked_lines[] = $po_item_id;
                    }

                    $codes = $this->makeRollCodes($fab_data->id, $shipment_id, $roll_number, $price);

                    $save_data_detail = new FabricReceiptDetail;
                    $save_data_detail->fabric_receipt_id = $save_data->id;

                    $save_data_detail->purchase_order_id = $po_id;
                    $save_data_detail->purchase_order_item_id = $po_item_id;
                    $save_data_detail->fabric_sku = $fab_data->sku;
                    $save_data_detail->fabric_id = $fab_data->id;
                    $save_data_detail->roll = 1;
                    $save_data_detail->roll_number = $roll_number;
                    $save_data_detail->price_per_meter = $price;
                    $save_data_detail->meter = $meter;
                    $save_data_detail->batch_no = '';
                    $save_data_detail->status = 1;
                    $save_data_detail->barcode = $codes['barcode'];
                    $save_data_detail->qrcode = $codes['qrcode'];
                    $save_data_detail->qrcode_number = $codes['number'];
                    $save_data_detail->remaining_quantity = $meter;
                    $save_data_detail->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                    $save_data_detail->shipment_number = $shipment_id;
                    $save_data_detail->save();
                }
            }

            if ($linked_lines) {
                PurchaseOrderItem::whereIn('id', array_unique($linked_lines))->update(['status' => 1]); // Keep as active/received
            }
            FabricPurchaseLink::recalcLines(array_keys($po_lines));

            DB::commit();
            return $save_data->id;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->removeFiles($moved);
            throw $e;
        }
    }

    public function view(Request $request)
    {
        $data = FabricReceipt::with([
            'vendor',
            'details.purchase_order',
            'details.purchase_order_item',
            'details.fabric',
            'details.returns',
            'returns',
            'returns.details',
            'returns.details.fabric',
            'other_images'
        ])->where('id', $request->id)->first();
        return $data;
    }

    /** Return from the shipment screen: totals are worked out here from the rolls, not taken from the page. */
    public function storeReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $receipt = FabricReceipt::findOrFail($request->fabric_receipt_id);
            [$lines, $subTotal] = $this->returnLines($request, $receipt);
            $totals = $this->returnTotals($request, $subTotal);

            $return = new FabricReturn();
            $return->fabric_receipt_id = $receipt->id;
            $return->vendor_id = $receipt->vendor_id;
            $return->date = $request->date ?? date('Y-m-d');
            $return->remarks = $request->remarks;
            $return->return_number = $this->newReturnNumber();
            $return->sub_total = $subTotal;
            $return->gst_percentage = $totals['gst_percentage'];
            $return->gst_amount = $totals['gst_amount'];
            $return->discount = $totals['discount'];
            $return->other_charges = $totals['other_charges'];
            $return->total_amount = $totals['total'];
            $return->save();

            foreach ($lines as [$detail, $return_meter, $price_per_meter]) {
                // Create Return Detail
                $return_detail = new FabricReturnDetail();
                $return_detail->fabric_return_id = $return->id;
                $return_detail->fabric_receipt_detail_id = $detail->id;
                $return_detail->fabric_id = $detail->fabric_id;
                $return_detail->return_meter = $return_meter;
                $return_detail->price_per_meter = $price_per_meter;
                $return_detail->save();

                // Update Detail Remaining Quantity
                $detail->remaining_quantity -= $return_meter;
                // if ($detail->remaining_quantity <= 0) {
                //     $detail->status = 2; // Fully Returned
                // }
                $detail->save();
            }

            // Deduct from vendor balance
            if ($receipt->vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $return->total_amount;
                    $vendor->save();
                }
            }

            DB::commit();
            FabricPurchaseLink::forgetReturned(); // the shipment's pending amount changes
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $return = FabricReturn::with('details.receipt_detail')->lockForUpdate()->find($request->return_id);
            if (!$return) {
                throw new \RuntimeException("Return record not found.");
            }

            $receipt = FabricReceipt::find($return->fabric_receipt_id);
            if (!$receipt) {
                throw new \RuntimeException("The shipment of this return was not found.");
            }
            if ($this->returnHasOtherShipments($return)) {
                throw new \RuntimeException("Return {$return->return_number} has rolls of several shipments, so it cannot be edited here. Open it from Fabric Return.");
            }

            // 1. Revert OLD return impact
            foreach ($return->details as $old_detail) {
                if ($old_detail->receipt_detail) {
                    $new_qty = min($old_detail->receipt_detail->meter, $old_detail->receipt_detail->remaining_quantity + $old_detail->return_meter);
                    $old_detail->receipt_detail->remaining_quantity = $new_qty;
                    if ($old_detail->receipt_detail->status == 2) {
                        $old_detail->receipt_detail->status = 1;
                    }
                    $old_detail->receipt_detail->save();
                }
            }

            // Revert Vendor Balance (add back the deduction)
            if ($receipt->vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance += $return->total_amount;
                    $vendor->save();
                }
            }

            // Clear old details
            $return->details()->delete();

            // 2. Apply NEW return impact (rolls are read again, after the old return was put back)
            [$lines, $subTotal] = $this->returnLines($request, $receipt);
            $totals = $this->returnTotals($request, $subTotal);

            $return->vendor_id = $return->vendor_id ?: $receipt->vendor_id;
            $return->date = $request->date ?? date('Y-m-d');
            $return->remarks = $request->remarks;
            $return->sub_total = $subTotal;
            $return->gst_percentage = $totals['gst_percentage'];
            $return->gst_amount = $totals['gst_amount'];
            $return->discount = $totals['discount'];
            $return->other_charges = $totals['other_charges'];
            $return->total_amount = $totals['total'];
            $return->save();

            foreach ($lines as [$detail, $return_meter, $price_per_meter]) {
                // Create New Return Detail
                $return_detail = new FabricReturnDetail();
                $return_detail->fabric_return_id = $return->id;
                $return_detail->fabric_receipt_detail_id = $detail->id;
                $return_detail->fabric_id = $detail->fabric_id;
                $return_detail->return_meter = $return_meter;
                $return_detail->price_per_meter = $price_per_meter;
                $return_detail->save();

                // Update Detail Remaining Quantity
                $detail->remaining_quantity -= $return_meter;
                $detail->save();
            }

            // Update Vendor Balance with NEW total
            if ($receipt->vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $return->total_amount;
                    $vendor->save();
                }
            }

            DB::commit();
            FabricPurchaseLink::forgetReturned(); // the shipment's pending amount changes
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** Rolls of a return form: only rolls of this shipment, at most their remaining meters. [[roll, meters, price], ...], sub total */
    private function returnLines(Request $request, FabricReceipt $receipt): array
    {
        $lines = [];
        $subTotal = 0;
        foreach ((array) $request->returns as $detail_id => $return_data) {
            $return_meter = (float) ($return_data['return_meter'] ?? 0);
            if ($return_meter <= 0) {
                continue;
            }
            $detail = FabricReceiptDetail::where('fabric_receipt_id', $receipt->id)->lockForUpdate()->find((int) $detail_id);
            if (!$detail) {
                throw new \RuntimeException('A roll in this return does not belong to this shipment.');
            }
            $price = $return_data['price_per_meter'] ?? null;
            if ($price !== null && $price !== '' && !is_numeric($price)) {
                throw new \RuntimeException("The price of roll {$detail->roll_number} is not valid.");
            }
            $price_per_meter = ($price === null || $price === '') ? (float) $detail->price_per_meter : (float) $price;
            if ($price_per_meter < 0) {
                throw new \RuntimeException("The price of roll {$detail->roll_number} is not valid.");
            }
            if ($return_meter > (float) $detail->remaining_quantity + 0.0001) {
                throw new \RuntimeException("Return quantity for roll {$detail->roll_number} exceeds remaining quantity ({$detail->remaining_quantity} m).");
            }
            $lines[] = [$detail, $return_meter, $price_per_meter];
            $subTotal += $return_meter * $price_per_meter;
        }
        if (!$lines) {
            throw new \RuntimeException('Please enter a return meter for at least one roll.');
        }
        return [$lines, round($subTotal, 2)];
    }

    /** GST, charges and total of a return; the total = sub total + GST + other charges - discount. */
    private function returnTotals(Request $request, float $subTotal): array
    {
        $num = function ($field) use ($request) {
            $value = $request->input($field);
            if ($value !== null && $value !== '' && !is_numeric($value)) {
                throw new \RuntimeException("The {$field} value is not a number.");
            }
            return round((float) $value, 2);
        };
        $t = [
            'gst_percentage' => $num('gst_percentage'),
            'gst_amount' => $num('gst_amount'),
            'other_charges' => $num('other_charges'),
            'discount' => $num('discount'),
        ];
        if ($t['gst_percentage'] < 0 || $t['gst_percentage'] > 100 || $t['gst_amount'] < 0 || $t['other_charges'] < 0 || $t['discount'] < 0) {
            throw new \RuntimeException('GST, other charges and discount cannot be below 0 (GST % at most 100).');
        }
        $t['total'] = round($subTotal + $t['gst_amount'] + $t['other_charges'] - $t['discount'], 2);
        if ($t['total'] < 0) {
            throw new \RuntimeException('The discount is more than the return amount.');
        }
        return $t;
    }

    private function newReturnNumber(): string
    {
        do {
            $number = 'RET-' . time() . '-' . rand(100, 999);
        } while (FabricReturn::where('return_number', $number)->exists());
        return $number;
    }

    public function downloadReport(Request $request)
    {
        $data = $this->view($request);
        $pdf = PDF::loadView('admin.fabric_receipt.report_pdf', compact('data'));

        $fileName = 'Fabric_Shipment_' . str_replace('/', '_', $data->shipment_id) . '.pdf';
        return $pdf->download($fileName);
    }

    public function edit(Request $request)
    {
        return FabricReceipt::with(['details.fabric', 'other_images'])->find($request->id);
    }

    /**
     * Edit a shipment. Every check is done before anything is saved, and everything is saved together
     * (vendor balance, rolls, PO lines) or nothing at all.
     *  - a roll that is already used (cut, sold, returned, moved, disposed) cannot be removed, and its meters cannot change
     *  - the vendor cannot change once there are payments or returns, or when the shipment came against a PO
     *  - rolls moved to another warehouse stay there; new rolls are linked to the shipment's PO
     */
    public function update(Request $request)
    {
        $moved = [];
        DB::beginTransaction();
        try {
            $update_data = FabricReceipt::lockForUpdate()->findOrFail($request->receipt_id);
            $details = FabricReceiptDetail::where('fabric_receipt_id', $update_data->id)->lockForUpdate()->get()->keyBy('id');
            $old_total = (float) $update_data->total_amount;
            $old_vendor_id = (int) $update_data->vendor_id;
            $old_warehouse_id = (int) $update_data->master_fabric_warehouse_id;
            $po_id = (int) ($details->pluck('purchase_order_id')->filter()->first() ?? 0);
            $po = $po_id ? PurchaseOrder::find($po_id) : null;

            // ---- 1. checks (nothing is changed yet) ----
            if ((int) $request->vendor_id !== $old_vendor_id) {
                if ($update_data->payments()->exists()) {
                    throw new \RuntimeException('The vendor cannot be changed because payments are already made for this shipment.');
                }
                if (FabricReturn::where('fabric_receipt_id', $update_data->id)->exists()
                    || FabricReturnDetail::whereIn('fabric_receipt_detail_id', $details->keys()->all() ?: [0])->exists()) {
                    throw new \RuntimeException('The vendor cannot be changed because fabric of this shipment has been returned.');
                }
                if ($po && !FabricPurchaseLink::isAutoPo($po)) {
                    throw new \RuntimeException("The vendor cannot be changed because this shipment was received against {$po->sku}.");
                }
            }

            $kept_ids = [];
            foreach ($request->roll_details as $single_data) {
                $detail_id = (int) ($single_data['detail_id'] ?? 0);
                if (!$detail_id) {
                    continue;
                }
                $detail = $details->get($detail_id);
                if (!$detail) {
                    throw new \RuntimeException('A roll of this shipment was not found. Please open the shipment again.');
                }
                $kept_ids[] = $detail_id;
                if ((int) $detail->fabric_id !== (int) $single_data['fabric_id']) {
                    throw new \RuntimeException("The fabric of roll '{$detail->roll_number}' cannot be changed.");
                }
                $used_quantity = $detail->meter - $detail->remaining_quantity;
                if ($used_quantity > 0 && abs($detail->meter - (float) $single_data['meter']) > 0.0001) {
                    throw new \RuntimeException("Cannot edit meter for roll '{$detail->roll_number}' because it has already been used in production.");
                }
            }
            // (filter keeps the roll ids as keys; except()/only() of a model list would renumber it)
            $removed = $details->filter(fn($d, $id) => !in_array($id, $kept_ids, true));
            $used_removed = FabricPurchaseLink::usedRolls($removed->keys()->all());
            if ($used_removed) {
                $names = $removed->filter(fn($d, $id) => in_array($id, $used_removed, true))->pluck('roll_number')->implode(', ');
                throw new \RuntimeException("Cannot remove roll(s) {$names} because they are already used (cut, sold, returned or moved).");
            }

            // ---- 2. files and shipment details ----
            if ($request->hasFile('shipment_photo')) {
                $update_data->shipment_photo = $this->saveUpload($request->file('shipment_photo'), 'shipment-image', 'shipment-image', $moved);
            }
            if ($request->hasFile('challan_photo')) {
                $update_data->challan_photo = $this->saveUpload($request->file('challan_photo'), 'challan-image', 'challan-image', $moved);
            }
            if ($request->hasFile('other_images')) {
                foreach ($request->file('other_images') as $image) {
                    \App\Models\FabricReceiptOtherImage::create([
                        'fabric_receipt_id' => $update_data->id,
                        'image' => $this->saveUpload($image, 'other-images', 'other-image', $moved)
                    ]);
                }
            }

            $update_data->vendor_id = $request->vendor_id;
            $update_data->bill_no = $request->bill_no;
            $update_data->truck_number = $request->truck_number ?? '';
            $update_data->time = $request->time;
            $update_data->received_by = $request->received_by ?? '';
            $update_data->amount = $request->amount ?? 0.00;
            $update_data->gst_amount = $request->gst_amount ?? 0.00;
            $update_data->gst_percentage = $request->gst_percentage ?? 1;
            $update_data->other_charges = $request->other_charges ?? 0.00;
            $update_data->total_amount = $request->total_amount ?? 0.00;
            $update_data->total_meter = $request->total_meter ?? 0.00;
            $update_data->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
            $update_data->roll = count($request->roll_details);
            $update_data->save();

            // vendor balance: take the old bill off the old vendor, add the new bill to the (new) vendor
            if ($old_vendor_id) {
                $old_vendor = Vendor::lockForUpdate()->find($old_vendor_id);
                if ($old_vendor) {
                    $old_vendor->balance -= $old_total;
                    $old_vendor->save();
                }
            }
            if ($update_data->vendor_id) {
                $new_vendor = Vendor::lockForUpdate()->find($update_data->vendor_id);
                if ($new_vendor) {
                    $new_vendor->balance += $update_data->total_amount;
                    $new_vendor->save();
                }
            }
            if ($po && FabricPurchaseLink::isAutoPo($po) && (int) $po->vendor_id !== (int) $update_data->vendor_id) {
                $po->update(['vendor_id' => $update_data->vendor_id]); // the automatic PO belongs to this shipment only
            }

            // ---- 3. rolls ----
            $shipment_id = $update_data->shipment_id;
            $touched_lines = $removed->pluck('purchase_order_item_id')->all();
            FabricReceiptDetail::whereIn('id', $removed->keys()->all() ?: [0])->delete();

            $new_rolls = [];
            foreach ($request->roll_details as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if (!$fab_data) {
                    continue;
                }
                $detail_id = (int) ($single_data['detail_id'] ?? 0);
                if (!$detail_id) {
                    $new_rolls[] = [$fab_data, $single_data];
                    continue;
                }
                $detail = $details->get($detail_id);
                $used_quantity = $detail->meter - $detail->remaining_quantity;
                $detail->fabric_sku = $fab_data->sku;
                $detail->roll_number = $single_data['roll_no'];
                $detail->price_per_meter = $single_data['price'] ?? 0;
                $detail->meter = (float) $single_data['meter'];
                $detail->remaining_quantity = max(0, $detail->meter - $used_quantity);
                // a roll moved to another warehouse stays there
                if (!$detail->master_fabric_warehouse_id || (int) $detail->master_fabric_warehouse_id === $old_warehouse_id) {
                    $detail->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                }
                $detail->shipment_number = $shipment_id;
                $detail->save();
                $touched_lines[] = $detail->purchase_order_item_id;
            }

            $po_lines = $po_id ? FabricPurchaseLink::openLines($po_id) : [];
            foreach ($new_rolls as [$fab_data, $single_data]) {
                $meter = (float) $single_data['meter'];
                $price = $single_data['price'] ?? 0;
                $codes = $this->makeRollCodes($fab_data->id, $shipment_id, $single_data['roll_no'], $price);

                $detail = new FabricReceiptDetail();
                $detail->fabric_receipt_id = $update_data->id;
                $detail->purchase_order_id = $po_id ?: null;
                $detail->purchase_order_item_id = $po_id ? FabricPurchaseLink::pickLine($po_lines, (int) $fab_data->id, $meter) : null;
                $detail->fabric_sku = $fab_data->sku;
                $detail->fabric_id = $fab_data->id;
                $detail->roll = 1;
                $detail->roll_number = $single_data['roll_no'];
                $detail->price_per_meter = $price;
                $detail->meter = $meter;
                $detail->remaining_quantity = $meter;
                $detail->batch_no = '';
                $detail->status = 1;
                $detail->barcode = $codes['barcode'];
                $detail->qrcode = $codes['qrcode'];
                $detail->qrcode_number = $codes['number'];
                $detail->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                $detail->shipment_number = $shipment_id;
                $detail->save();
            }

            // ---- 4. PO: "left to receive" follows the rolls ----
            FabricPurchaseLink::recalcLines(array_merge($touched_lines, array_keys($po_lines)));
            if ($po) {
                FabricPurchaseLink::syncAutoPo($po->id);
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->removeFiles($moved);
            throw $e;
        }
    }

    public function delete(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $receipt = FabricReceipt::lockForUpdate()->find($request->id);
            if (!$receipt || $receipt->status == 0 || !$receipt->can_delete) {
                return false;
            }
            $rolls = $receipt->details()->get(['id', 'purchase_order_id', 'purchase_order_item_id']);

            if ($receipt->vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $receipt->total_amount;
                    $vendor->save();
                }
            }

            $receipt->details()->delete();
            $receipt->delete();

            // the PO gets back the meters of the deleted rolls; an automatic PO of this shipment is removed
            FabricPurchaseLink::recalcLines($rolls->pluck('purchase_order_item_id')->all());
            foreach ($rolls->pluck('purchase_order_id')->filter()->unique() as $po_id) {
                FabricPurchaseLink::syncAutoPo((int) $po_id);
            }

            return true;
        });
    }

    public function vendors()
    {
        $data = Vendor::where('status', 1)->get();
        return $data;
    }
    public function purchase_order_items($purchase_order_id)
    {
        $data = PurchaseOrderItem::with('fabric')->where('purchase_order_id', $purchase_order_id)->get();
        return $data;
    }

    /** POs that can still receive fabric: open, not closed, and some meters left to receive. */
    public function pendingPurchaseOrders()
    {
        return PurchaseOrder::with('vendor')->where('status', 1)->where('is_closed', 0)
            ->whereHas('items', function ($q) {
                $q->where('remaining_quantity', '>', 0);
            })
            ->orderBy('id', 'desc')->get();
    }

    public function fabrics()
    {
        $data = Fabric::where('status', 1)->get();
        return $data;
    }
    public function fabric_list_by_vendor($vendor_id)
    {
        $data = Fabric::where('status', 1)->where('vendor_id', $vendor_id)->get();
        return $data;
    }

    public function cutting_units()
    {
        $data = MasterFabricWarehouse::where('status', 1)->get();
        return $data;
    }

    private function generateUniqueQrNumber()
    {
        do {
            // Generate 16-digit numeric code
            $qrcode_number = mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999);

            // Check DB
            $exists = FabricReceiptDetail::where('qrcode_number', $qrcode_number)->exists();

        } while ($exists);

        return $qrcode_number;
    }

    /** Roll number, barcode picture and QR picture of a new roll. */
    private function makeRollCodes($fabric_id, $shipment_id, $roll_number, $price): array
    {
        $qrcode_number = $this->generateUniqueQrNumber();

        $barcodeGenerator = new BarcodeGeneratorPNG();
        $barcodeFileName = $qrcode_number . '_barcode.png';
        $barcodePath = public_path('assets/barcodes');
        if (!file_exists($barcodePath)) {
            mkdir($barcodePath, 0777, true);
        }
        file_put_contents(
            $barcodePath . '/' . $barcodeFileName,
            $barcodeGenerator->getBarcode($qrcode_number, $barcodeGenerator::TYPE_CODE_128, 3, 80)
        );

        $fileName = $qrcode_number . '.png';
        $qrData = json_encode([
            'fabric_id' => $fabric_id,
            'shipment_id' => $shipment_id,
            'roll_number' => $roll_number,
            'price' => $price
        ]);
        $destinationPath = public_path('assets/qrcodes');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        // Generate QR Code with GD (no imagick)
        Builder::create()
            ->writer(new PngWriter())
            ->data($qrData)
            ->size(300)
            ->margin(10)
            ->build()
            ->saveToFile($destinationPath . '/' . $fileName);

        return ['number' => $qrcode_number, 'barcode' => $barcodeFileName, 'qrcode' => $fileName];
    }

    /**
     * Saves an uploaded challan / photo in public/assets/receipts/<folder>. The file name ending comes from what the
     * file really is (a picture or a PDF), never from the name it was uploaded with.
     */
    public function saveUpload($file, string $folder, string $prefix, array &$moved = []): string
    {
        $ext = strtolower((string) $file->extension());
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!$file->isValid() || !in_array($ext, self::UPLOAD_TYPES, true)) {
            throw new \RuntimeException('Only pictures or PDF files can be uploaded.');
        }
        $name = $prefix . '-' . rand() . '_' . time() . '.' . $ext;
        $destinationPath = public_path('assets/receipts/' . $folder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file->move($destinationPath, $name);
        $moved[] = $destinationPath . '/' . $name;
        return $name;
    }

    private function removeFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /** True when a return (made on the Fabric Return screen) also has rolls of other shipments. */
    public function returnHasOtherShipments(FabricReturn $return): bool
    {
        return FabricReturnDetail::where('fabric_return_id', $return->id)
            ->whereHas('receipt_detail', function ($q) use ($return) {
                $q->where('fabric_receipt_id', '!=', $return->fabric_receipt_id);
            })->exists();
    }

    public function scan(Request $request)
    {
        $code = $request->code;

        if (!$code) {
            abort(404, 'Invalid scan');
        }

        // Find by barcode / qrcode number
        $detail = FabricReceiptDetail::with([
            'fabric',
            'fabric_receipt.vendor',
            'fabric_receipt.cutting_master'
        ])->where('qrcode_number', $code)->first();
        if (!$detail) {
            abort(404, 'Record not found');
        }

        return $detail;

    }

    public function checkRollNo($request)
    {
        $query = FabricReceiptDetail::where('roll_number', $request->roll_no);
        if ($request->has('fabric_id') && !empty($request->fabric_id)) {
            $query->where('fabric_id', $request->fabric_id);
        }
        if (isset($request->receipt_id) && !empty($request->receipt_id)) {
            $query->where('fabric_receipt_id', '!=', $request->receipt_id);
        }
        return $query->exists();
    }

    public function checkBillNo($request)
    {
        $query = FabricReceipt::where('bill_no', $request->bill_no);
        // the same bill no may be used by different vendors
        if (!empty($request->vendor_id)) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if (isset($request->receipt_id) && !empty($request->receipt_id)) {
            $query->where('id', '!=', $request->receipt_id);
        }
        $exists = $query->whereNotNull('bill_no')->where('bill_no', '!=', '')->exists();
        return $exists;
    }

    public function downloadReturnReport(Request $request)
    {
        $return = FabricReturn::with(['receipt.vendor', 'details.fabric', 'details.receipt_detail'])->find($request->id);
        if (!$return) {
            abort(404, 'Return record not found');
        }

        $pdf = \PDF::loadView('admin.fabric_receipt.return_report_pdf', compact('return'));
        $fileName = 'Fabric_Return_' . str_replace('/', '_', $return->return_number) . '.pdf';
        return $pdf->download($fileName);
    }

    public function deleteReturn($id)
    {
        DB::beginTransaction();
        try {
            $return = FabricReturn::with('details.receipt_detail')->lockForUpdate()->find($id);
            if (!$return) {
                return false;
            }

            $receipt = FabricReceipt::find($return->fabric_receipt_id);

            // Revert Roll Quantities
            foreach ($return->details as $detail) {
                if ($detail->receipt_detail) {
                    $detail->receipt_detail->remaining_quantity += $detail->return_meter;

                    // Reset status to active if it was returned
                    if ($detail->receipt_detail->status == 2) {
                        $detail->receipt_detail->status = 1;
                    }

                    $detail->receipt_detail->save();
                }
                $detail->delete();
            }

            // Revert Vendor Balance
            $vendor_id = $return->vendor_id ?? ($receipt ? $receipt->vendor_id : null);
            if ($vendor_id) {
                $vendor = Vendor::lockForUpdate()->find($vendor_id);
                if ($vendor) {
                    $vendor->balance += $return->total_amount;
                    $vendor->save();
                }
            }

            $return->delete();

            DB::commit();
            FabricPurchaseLink::forgetReturned(); // the shipment's pending amount changes
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
