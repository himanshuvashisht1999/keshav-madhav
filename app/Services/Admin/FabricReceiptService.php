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

    public function store(Request $request)
    {
        $po_id = $request->purchase_order_id;

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
                    $mtr = $roll['meter'];
                    $prc = $roll['price'];
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
                $image = $request->file('shipment_photo');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "shipment-image-" . rand() . "_" . time() . "." . $extImage;
                $destinationPath = public_path() . '/assets/receipts/shipment-image';
                $image->move($destinationPath, $imgName);
            }
            $imgName2 = '';
            if ($request->file('challan_photo')) {
                $image = $request->file('challan_photo');
                $extImage = $image->getClientOriginalExtension();
                $imgName2 = "challan-image-" . rand() . "_" . time() . "." . $extImage;
                $destinationPath = public_path() . '/assets/receipts/challan-image';
                $image->move($destinationPath, $imgName2);
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


            if ($save_data->vendor_id) {
                $vendor = Vendor::find($save_data->vendor_id);

                if ($vendor) {
                    $vendor->balance += $save_data->total_amount;
                    $vendor->save();
                }
            }

            foreach ($request->roll_details as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if ($fab_data) {
                    $fabric_sku = $fab_data->sku;
                    $fabric_id = $fab_data->id;

                    $meter = $single_data['meter'];
                    $roll_number = $single_data['roll_no'];
                    $price = $single_data['price'];

                    $po_item_id = 0;
                    if ($po_id > 0) {
                        // Link to real PO item if it exists
                        $po_item = PurchaseOrderItem::where('purchase_order_id', $po_id)
                            ->where('fabric_id', $fabric_id)
                            ->first();

                        if ($po_item) {
                            $po_item_id = $po_item->id;
                            $po_item->update(['status' => 1, 'remaining_quantity' => max(0, $po_item->remaining_quantity - $meter)]); // Keep as active/received
                        }
                    }

                    ////////// work for barcode
                    $qrcode_number = $this->generateUniqueQrNumber();

                    /// code for barcode
                    $barcodeGenerator = new BarcodeGeneratorPNG();
                    $barcodeData = $qrcode_number;
                    $barcodeFileName = $qrcode_number . '_barcode.png';
                    $barcodePath = public_path('assets/barcodes');
                    if (!file_exists($barcodePath)) {
                        mkdir($barcodePath, 0777, true);
                    }
                    file_put_contents(
                        $barcodePath . '/' . $barcodeFileName,
                        $barcodeGenerator->getBarcode($barcodeData, $barcodeGenerator::TYPE_CODE_128, 3, 80)
                    );

                    $fileName = $qrcode_number . '.png';
                    $qrData = json_encode([
                        'fabric_id' => $fabric_id,
                        'shipment_id' => $shipment_id,
                        'roll_number' => $roll_number,
                        'price' => $price
                    ]);

                    $destinationPath = public_path('assets/qrcodes');

                    // Ensure directory exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    // Generate QR Code with GD (no imagick)
                    $result = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qrData)
                        ->size(300)
                        ->margin(10)
                        ->build();

                    $result->saveToFile($destinationPath . '/' . $fileName);

                    ////end barcode

                    $save_data_detail = new FabricReceiptDetail;
                    $save_data_detail->fabric_receipt_id = $save_data->id;

                    $save_data_detail->purchase_order_id = $po_id;
                    $save_data_detail->purchase_order_item_id = $po_item_id;
                    $save_data_detail->fabric_sku = $fabric_sku;
                    $save_data_detail->fabric_id = $fabric_id;
                    $save_data_detail->roll = 1;
                    $save_data_detail->roll_number = $roll_number;
                    $save_data_detail->price_per_meter = $price;
                    $save_data_detail->meter = $meter;
                    $save_data_detail->batch_no = '';
                    $save_data_detail->status = 1;
                    $save_data_detail->barcode = $barcodeFileName;
                    $save_data_detail->qrcode = $fileName;
                    $save_data_detail->qrcode_number = $qrcode_number;
                    $save_data_detail->remaining_quantity = $meter;
                    $save_data_detail->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                    $save_data_detail->shipment_number = $shipment_id;
                    $save_data_detail->save();

                }

            }

            DB::commit();
            return $save_data->id;
        } catch (\Exception $e) {
            DB::rollBack();
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
            'returns.details.fabric'
        ])->where('id', $request->id)->first();
        return $data;
    }

    public function storeReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $receipt = FabricReceipt::find($request->fabric_receipt_id);

            $return = new FabricReturn();
            $return->fabric_receipt_id = $receipt->id;
            $return->date = $request->date ?? date('Y-m-d');
            $return->remarks = $request->remarks;
            $return->return_number = 'RET-' . time() . '-' . rand(100, 999);

            // New fields from request
            $return->sub_total = $request->sub_total ?? 0;
            $return->gst_percentage = $request->gst_percentage ?? 0;
            $return->gst_amount = $request->gst_amount ?? 0;
            $return->discount = $request->discount ?? 0;
            $return->other_charges = $request->other_charges ?? 0;
            $return->total_amount = $request->total_amount ?? 0;
            $return->save();

            $calculated_subtotal = 0;

            foreach ($request->returns as $detail_id => $return_data) {
                if (!isset($return_data['return_meter']) || $return_data['return_meter'] <= 0) {
                    continue;
                }

                $detail = FabricReceiptDetail::find($detail_id);
                if (!$detail)
                    continue;

                $return_meter = (float) $return_data['return_meter'];
                $price_per_meter = (float) ($return_data['price_per_meter'] ?? $detail->price_per_meter);

                // Validate against remaining quantity
                if ($return_meter > $detail->remaining_quantity) {
                    throw new \Exception("Return quantity for roll {$detail->roll_number} exceeds remaining quantity.");
                }

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
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $return->total_amount;
                    $vendor->save();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $return = FabricReturn::with('details.receipt_detail')->find($request->return_id);
            if (!$return) {
                throw new \Exception("Return record not found.");
            }

            $receipt = FabricReceipt::find($return->fabric_receipt_id);

            // 1. Revert OLD return impact
            foreach ($return->details as $old_detail) {
                if ($old_detail->receipt_detail) {
                    $old_detail->receipt_detail->remaining_quantity += $old_detail->return_meter;
                    if ($old_detail->receipt_detail->status == 2) {
                        $old_detail->receipt_detail->status = 1;
                    }
                    $old_detail->receipt_detail->save();
                }
            }

            // Revert Vendor Balance (add back the deduction)
            if ($receipt && $receipt->vendor_id) {
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance += $return->total_amount;
                    $vendor->save();
                }
            }

            // Clear old details
            $return->details()->delete();

            // 2. Apply NEW return impact
            $return->date = $request->date ?? date('Y-m-d');
            $return->remarks = $request->remarks;
            $return->sub_total = $request->sub_total ?? 0;
            $return->gst_percentage = $request->gst_percentage ?? 0;
            $return->gst_amount = $request->gst_amount ?? 0;
            $return->discount = $request->discount ?? 0;
            $return->other_charges = $request->other_charges ?? 0;
            $return->total_amount = $request->total_amount ?? 0;
            $return->save();

            foreach ($request->returns as $detail_id => $return_data) {
                if (!isset($return_data['return_meter']) || $return_data['return_meter'] <= 0) {
                    continue;
                }

                $detail = FabricReceiptDetail::find($detail_id);
                if (!$detail)
                    continue;

                $return_meter = (float) $return_data['return_meter'];
                $price_per_meter = (float) ($return_data['price_per_meter'] ?? $detail->price_per_meter);

                if ($return_meter > $detail->remaining_quantity) {
                    throw new \Exception("Return quantity for roll {$detail->roll_number} exceeds available quantity.");
                }

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
            if ($receipt && $receipt->vendor_id) {
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $return->total_amount;
                    $vendor->save();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        return FabricReceipt::with('details.fabric')->find($request->id);
    }

    public function update(Request $request)
    {
        $update_data = FabricReceipt::find($request->receipt_id);

        if ($request->hasFile('shipment_photo')) {
            $image = $request->file('shipment_photo');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "shipment-image-" . rand() . "_" . time() . "." . $extImage;
            $destinationPath = public_path() . '/assets/receipts/shipment-image';
            $image->move($destinationPath, $imgName);
            $update_data->shipment_photo = $imgName;
        }

        if ($request->hasFile('challan_photo')) {
            $image = $request->file('challan_photo');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "challan-image-" . rand() . "_" . time() . "." . $extImage;
            $destinationPath = public_path() . '/assets/receipts/challan-image';
            $image->move($destinationPath, $imgName);
            $update_data->challan_photo = $imgName;
        }

        $old_total = $update_data->total_amount;
        $old_vendor_id = $update_data->vendor_id;

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
        $update_data->save();

        if ($old_vendor_id) {
            $old_vendor = Vendor::find($old_vendor_id);
            if ($old_vendor) {
                $old_vendor->balance -= $old_total;
                $old_vendor->save();
            }
        }

        if ($update_data->vendor_id) {
            $new_vendor = Vendor::find($update_data->vendor_id);
            if ($new_vendor) {
                $new_vendor->balance += $update_data->total_amount;
                $new_vendor->save();
            }
        }

        $shipment_id = $update_data->shipment_id;

        if ($request->has('roll_details')) {
            $received_ids = [];
            foreach ($request->roll_details as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if (!$fab_data)
                    continue;

                if (isset($single_data['detail_id']) && !empty($single_data['detail_id'])) {
                    $received_ids[] = $single_data['detail_id'];
                    $detail = FabricReceiptDetail::find($single_data['detail_id']);
                } else {
                    $detail = new FabricReceiptDetail();
                    $detail->fabric_receipt_id = $update_data->id;
                    $qrcode_number = $this->generateUniqueQrNumber();
                    $detail->qrcode_number = $qrcode_number;

                    // Generate Barcode
                    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    $barcodeFileName = $qrcode_number . '_barcode.png';
                    $barcodePath = public_path('assets/barcodes');
                    if (!file_exists($barcodePath)) {
                        mkdir($barcodePath, 0777, true);
                    }
                    file_put_contents($barcodePath . '/' . $barcodeFileName, $barcodeGenerator->getBarcode($qrcode_number, $barcodeGenerator::TYPE_CODE_128, 3, 80));
                    $detail->barcode = $barcodeFileName;

                    // Generate QR
                    $fileName = $qrcode_number . '.png';
                    $qrData = json_encode([
                        'fabric_id' => $fab_data->id,
                        'shipment_id' => $shipment_id,
                        'roll_number' => $single_data['roll_no'],
                        'price' => $single_data['price']
                    ]);
                    $destinationPath = public_path('assets/qrcodes');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $result = \Endroid\QrCode\Builder\Builder::create()
                        ->writer(new \Endroid\QrCode\Writer\PngWriter())
                        ->data($qrData)
                        ->size(300)
                        ->margin(10)
                        ->build();
                    $result->saveToFile($destinationPath . '/' . $fileName);
                    $detail->qrcode = $fileName;
                }

                $detail->fabric_sku = $fab_data->sku;
                $detail->fabric_id = $fab_data->id;
                $detail->roll_number = $single_data['roll_no'];
                $detail->price_per_meter = $single_data['price'];
                $detail->meter = $single_data['meter'];
                $detail->remaining_quantity = $single_data['meter'];
                $detail->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                $detail->shipment_number = $shipment_id;
                $detail->status = 1;
                $detail->save();
                $received_ids[] = $detail->id;
            }

            // Delete removed rolls
            $update_data->details()->whereNotIn('id', $received_ids)->delete();
            $update_data->update(['roll' => count($request->roll_details)]);
        }

        return true;
    }

    public function delete(Request $request)
    {
        $receipt = FabricReceipt::find($request->id);
        if ($receipt && $receipt->status != 0 && $receipt->can_delete) {
            if ($receipt->vendor_id) {
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance -= $receipt->total_amount;
                    $vendor->save();
                }
            }

            $receipt->details()->delete();
            $receipt->delete();

            return true;
        }
        return false;
    }
    public function vendors()
    {
        $data = Vendor::where('status', 1)->get();
        return $data;
    }
    public function purchase_orders(Request $request)
    {
        $data = PurchaseOrder::where('vendor_id', $request->vendor_id)->orderBy('id', 'desc')->get();
        return $data;
    }
    public function purchase_order_items($purchase_order_id)
    {
        $data = PurchaseOrderItem::with('fabric')->where('purchase_order_id', $purchase_order_id)->get();
        return $data;
    }


    public function storeDetail(Request $request)
    {
        $fab_receipt_update = FabricReceipt::where('id', $request->id)->update([
            'roll' => count($request->rolls),
        ]);
        $fab_rec_data = FabricReceipt::where('id', $request->id)->first();
        $master_fabric_warehouse_id = $fab_rec_data->master_fabric_warehouse_id;

        foreach ($request->rolls as $single_data) {

            $fab_data = Fabric::where('id', $single_data['fabric_sku'])->first();
            if ($fab_data) {


                $fabric_sku = $fab_data->sku;
                $fabric_id = $fab_data->id;


                $meter = $single_data['meter'];
                $roll_number = $single_data['roll'];
                $purchase_order_id = $request->purchase_order_id;
                $purchase_order_item_id = 0;
                $purchase_order_data = PurchaseOrder::where('id', $purchase_order_id)->first();
                if ($purchase_order_data) {
                    $purchase_order_item_data = PurchaseOrderItem::where('purchase_order_id', $purchase_order_data->id)->where('fabric_sku', $fabric_sku)->first();
                    if ($purchase_order_item_data) {
                        $purchase_order_item_id = $purchase_order_item_data->id;
                    } else {
                        $total_meter = $meter;
                        $price_static = 100;
                        $total_price = $total_meter * $price_static;

                        $save_purchase_order_item = new PurchaseOrderItem;
                        $save_purchase_order_item->purchase_order_id = $purchase_order_id;
                        $save_purchase_order_item->fabric_sku = $fabric_sku;
                        $save_purchase_order_item->sku = '';
                        $save_purchase_order_item->fabric_id = $fab_data->id;
                        $save_purchase_order_item->meter = $total_meter;
                        $save_purchase_order_item->price = $price_static;
                        $save_purchase_order_item->total_price = $total_price;
                        $save_purchase_order_item->save();
                        $purchase_order_item_id = $save_purchase_order_item->id;
                    }

                } else {
                    $receipt_data = FabricReceipt::where('id', $request->id)->first();
                    $save_data_purchase = new PurchaseOrder;
                    $save_data_purchase->sku = '';
                    $save_data_purchase->date = Carbon::now()->format('Y-m-d'); /////We have no date
                    $save_data_purchase->vendor_id = $receipt_data->vendor_id;
                    $save_data_purchase->delivery_date = Carbon::now()->format('Y-m-d'); /// we have no delivery date
                    $save_data_purchase->status = 1;
                    $save_data_purchase->save();
                    $sku_update = PurchaseOrder::where('id', $save_data_purchase->id)->update([
                        'sku' => 'PO-' . $save_data_purchase->id,
                    ]);

                    $total_meter = $meter;
                    $price_static = 100;
                    $total_price = $total_meter * $price_static;

                    $save_purchase_order_item = new PurchaseOrderItem;
                    $save_purchase_order_item->purchase_order_id = $save_data_purchase->id;
                    $save_purchase_order_item->fabric_sku = $fabric_sku;
                    $save_purchase_order_item->sku = '';
                    $save_purchase_order_item->fabric_id = $fab_data->id;
                    $save_purchase_order_item->meter = $total_meter;
                    $save_purchase_order_item->price = $price_static;
                    $save_purchase_order_item->total_price = $total_price;
                    $save_purchase_order_item->save();
                    $purchase_order_item_id = $save_purchase_order_item->id;


                }
                $receipt_data = FabricReceipt::where('id', $request->id)->first();
                $total_roll = $receipt_data->roll;
                $roll_no = $single_data['roll'];

                $purchase_order_item_data = PurchaseOrderItem::where('id', $purchase_order_item_id)->first();
                if ($purchase_order_item_data) {
                    $save_data = new FabricReceiptDetail;
                    // $save_data->sku = $fabric_sku;
                    $save_data->fabric_receipt_id = $request->id;

                    $save_data->purchase_order_id = $purchase_order_item_data->purchase_order_id;
                    $save_data->purchase_order_item_id = $purchase_order_item_data->id;
                    $save_data->fabric_sku = $fabric_sku;
                    $save_data->fabric_id = $fabric_id;
                    $save_data->roll = 1;
                    $save_data->roll_number = $roll_number;
                    $save_data->meter = $meter;
                    $save_data->batch_no = '';
                    $save_data->status = 1;
                    $save_data->save();

                    $fabric_receipt_status_update = FabricReceipt::where('id', $request->id)->update([
                        'status' => 1,
                    ]);

                    ///// save data in stocks
                    $unique_number = $save_data->id . '/' . $total_roll . '/' . $roll_no;
                    $fileName = $save_data->id . '_' . $total_roll . '_' . $roll_no . '.png';

                    $destinationPath = public_path('assets/qrcodes');

                    // Ensure directory exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    // Generate QR Code with GD (no imagick)
                    $result = Builder::create()
                        ->writer(new PngWriter())
                        ->data($unique_number)
                        ->size(300)
                        ->margin(10)
                        ->build();

                    // Save file
                    $result->saveToFile($destinationPath . '/' . $fileName);

                    $save_stock = new Stock;
                    $save_stock->sku = $fabric_sku;
                    $save_stock->fabric_id = $fabric_id;
                    $save_stock->master_fabric_warehouse_id = $master_fabric_warehouse_id;
                    $save_stock->date = Carbon::now()->format('Y-m-d');
                    $save_stock->goods_entry_number = $save_data->id;
                    $save_stock->meter = $meter;
                    $save_stock->roll = $total_roll;
                    /// new col
                    $save_stock->roll_no = $single_data['roll'];
                    $save_stock->qrcode = $fileName;
                    $save_stock->unique_number = $unique_number;
                    $save_stock->batch_no = '';

                    $save_stock->purchase_order_id = $purchase_order_item_data->purchase_order_id;
                    $save_stock->save();


                    $update_purchase_order_item = PurchaseOrderItem::where('id', $purchase_order_item_data->id)->update([
                        'status' => 2,
                    ]);

                }

            }
        }

        return true;
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
    public function new_batch_no()
    {
        // $data = FabricReceiptDetail::orderBy('id','desc')->first();
        // if($data){
        //     $new_batch_no = $data->batch_no + 1;
        // }else{
        //     $new_batch_no = 1;
        // }
        $new_batch_no = 1;

        return $new_batch_no;
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
        if (isset($request->receipt_id) && !empty($request->receipt_id)) {
            $query->where('fabric_receipt_id', '!=', $request->receipt_id);
        }
        $exists = $query->exists();
        return $exists;
    }

    public function checkBillNo($request)
    {
        $query = FabricReceipt::where('bill_no', $request->bill_no);
        if (isset($request->receipt_id) && !empty($request->receipt_id)) {
            $query->where('id', '!=', $request->receipt_id);
        }
        $exists = $query->whereNotNull('bill_no')->where('bill_no', '!=', '')->exists();
        return $exists;
    }

    public function returnFabric(Request $request)
    {
        $detail = FabricReceiptDetail::find($request->detail_id);
        if (!$detail || $detail->status == 2) {
            return false;
        }

        // Check if fabric is already used (remaining_quantity < meter)
        if ($detail->remaining_quantity < $detail->meter) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Mark detail as returned
            $detail->status = 2; // 2 for Returned
            $detail->remaining_quantity = 0;
            $detail->save();

            // Find and delete/deactivate from Stock
            $stock = Stock::where('goods_entry_number', $detail->id)->first();
            if ($stock) {
                $stock->delete();
            }

            // Deduct from vendor balance
            $receipt = FabricReceipt::find($detail->fabric_receipt_id);
            if ($receipt && $receipt->vendor_id) {
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $amount_to_deduct = $detail->meter * $detail->price_per_meter;

                    // Add GST if applicable
                    if ($receipt->gst_percentage > 0) {
                        $amount_to_deduct += ($amount_to_deduct * $receipt->gst_percentage / 100);
                    }

                    $vendor->balance -= $amount_to_deduct;
                    $vendor->save();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
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
            $return = FabricReturn::with('details.receipt_detail')->find($id);
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
            if ($receipt && $receipt->vendor_id) {
                $vendor = Vendor::find($receipt->vendor_id);
                if ($vendor) {
                    $vendor->balance += $return->total_amount;
                    $vendor->save();
                }
            }

            $return->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}