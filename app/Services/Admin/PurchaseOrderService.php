<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrder;
use App\Models\Fabric;
use App\Models\Vendor;
use App\Models\PurchaseOrderItem;
use App\Models\FabricReceiptDetail;
use App\Models\GeneralSettings;
use App\Mail\PurchaseOrderMail;
use App\Models\MasterFabricWarehouse;
use App\Models\Company;
use Illuminate\Support\Facades\Mail;
use App\Http\DataTable\Admin\PurchaseOrderDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        DataTable $datatable,
        PurchaseOrder $purchase_order
    ) {
        $this->datatable = $datatable;
        $this->purchase_order = $purchase_order;
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
        // all or nothing: a failed line never leaves an empty PO behind
        return DB::transaction(function () use ($request) {
            $save_data = new PurchaseOrder;
            $save_data->sku = '';
            $save_data->date = $request->date;
            $save_data->vendor_id = $request->vendor_id;
            $save_data->master_company_id = $request->master_company_id;
            $save_data->remark = $request->remark;
            $save_data->transport = $request->transport;
            $save_data->delivery_date = $request->delivery_date;
            $save_data->is_notify = $request->is_notify ?? 0;
            $save_data->fabric_warehouse_id = $request->fabric_warehouse_id;
            $save_data->status = 1;
            $save_data->save();
            $date = \Carbon\Carbon::parse($request->date)->format('dmY');
            $sku = "PO/" . $date . "/" . $save_data->id;
            $save_data->update([
                'sku' => $sku
            ]);

            foreach ($request->fabrics as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if ($fab_data) {
                    $meter = (float) $single_data['meter'];
                    $price = (float) ($single_data['price'] ?? 0); // the price box is optional
                    $save_po_item = new PurchaseOrderItem;
                    $save_po_item->purchase_order_id = $save_data->id;
                    $save_po_item->fabric_sku = $fab_data->sku;
                    $save_po_item->fabric_id = $fab_data->id;
                    $save_po_item->meter = $meter;
                    $save_po_item->remaining_quantity = $meter;
                    $save_po_item->price = $price;
                    $save_po_item->total_price = $meter * $price;
                    $save_po_item->save();
                }
            }

            // ✅ Send email with same $data you use in view
            // $data = PurchaseOrder::with('items', 'vendor')->find($save_data->id);
            // if ($data->vendor && $data->vendor->email) {
            //     Mail::to($data->vendor->email)->send(new PurchaseOrderMail($data));
            // }

            return true;
        });
    }
    public function view(Request $request)
    {
        $data = PurchaseOrder::with('items.fabric.fabric_composition', 'vendor', 'fabric_warehouse')->where('id', $request->id)->first();
        return $data;
    }


    public function downloadReport(Request $request)
    {
        $data = $this->view($request);
        $general_setting = $this->general_setting();
        $pdf = \PDF::loadView('admin.purchase_order.report_pdf', compact('data', 'general_setting'));

        $fileName = 'Purchase_Order_' . str_replace('/', '_', $data->sku) . '.pdf';
        return $pdf->download($fileName);
    }

    public function sendWhatsappReport(Request $request)
    {
        $data = $this->view($request);
        $general_setting = $this->general_setting();
        $pdf = \PDF::loadView('admin.purchase_order.report_pdf', compact('data', 'general_setting'));

        $fileName = 'Purchase_Order_' . str_replace('/', '_', $data->sku) . '.pdf';

        $phone = $request->phone ?? ($data->vendor->phone ?? '');

        if (empty($phone)) {
            return back()->with('error', 'No phone number available for this vendor.');
        }

        // saved in private storage (not the public folder), only to be sent
        $physicalPath = whatsapp_pdf_path($fileName);
        $pdf->save($physicalPath);

        $msg = "Dear {$data->vendor->name},\n\nYour Purchase Order #{$data->sku} has been generated.\nPlease find it attached.\n\nThank you!";
        $status = send_whatsapp_attachment($phone, $msg, $physicalPath, $fileName);

        if ($status !== false) {
            return back()->with('success', 'WhatsApp PO sent to ' . $phone . ' successfully.');
        } else {
            return back()->with('error', 'Failed to send WhatsApp message. Please check API credentials or phone number.');
        }
    }

    public function edit(Request $request)
    {
        // each line comes with the meters already received on it, so the form can lock those lines
        $data = PurchaseOrder::with(['fabric_warehouse', 'items' => function ($q) {
            $q->withCount('receipts')->withSum('receipts', 'meter')->orderBy('id');
        }])->where('id', $request->id)->first();
        if ($data) {
            $data->received_rolls = FabricReceiptDetail::where('purchase_order_id', $data->id)->count();
        }
        return $data;
    }

    /**
     * Saves the PO in place: lines keep their id, so rolls already received stay linked to them.
     * A line with received rolls cannot be removed or get another fabric, and the vendor cannot change
     * once fabric has been received. "Left to receive" = ordered meters - received meters.
     */
    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $update_data = PurchaseOrder::lockForUpdate()->findOrFail($request->id);
            $lines = PurchaseOrderItem::with('fabric')->where('purchase_order_id', $update_data->id)->get()->keyBy('id');
            $rollsOnLine = FabricReceiptDetail::whereIn('purchase_order_item_id', $lines->keys()->all() ?: [0])
                ->groupBy('purchase_order_item_id')->selectRaw('purchase_order_item_id, COUNT(*) AS n')
                ->pluck('n', 'purchase_order_item_id');
            $received = FabricPurchaseLink::receivedByLine($lines->keys()->all());

            if ((int) $request->vendor_id !== (int) $update_data->vendor_id
                && FabricReceiptDetail::where('purchase_order_id', $update_data->id)->exists()) {
                throw new \RuntimeException('The vendor of this PO cannot be changed because fabric has already been received on it.');
            }

            $update_data->date = $request->date;
            $update_data->vendor_id = $request->vendor_id;
            $update_data->master_company_id = $request->master_company_id;
            $update_data->remark = $request->remark;
            $update_data->transport = $request->transport;
            $update_data->delivery_date = $request->delivery_date;
            $update_data->fabric_warehouse_id = $request->fabric_warehouse_id;
            $update_data->status = 1;
            $update_data->save();

            $kept = [];
            foreach ($request->fabrics as $single_data) {
                $fab_data = Fabric::where('id', $single_data['fabric_id'])->first();
                if (!$fab_data) {
                    continue;
                }
                $item_id = (int) ($single_data['item_id'] ?? 0);
                if ($item_id) {
                    $save_po_item = $lines->get($item_id);
                    if (!$save_po_item || in_array($item_id, $kept, true)) {
                        throw new \RuntimeException('A fabric line of this PO was not found. Please open the PO again and save.');
                    }
                    if (($rollsOnLine[$item_id] ?? 0) > 0 && (int) $save_po_item->fabric_id !== (int) $fab_data->id) {
                        throw new \RuntimeException('The fabric of line "' . ($save_po_item->fabric->name ?? $save_po_item->fabric_sku) . '" cannot be changed because fabric has already been received on it.');
                    }
                } else {
                    $save_po_item = new PurchaseOrderItem;
                    $save_po_item->purchase_order_id = $update_data->id;
                }
                $meter = (float) $single_data['meter'];
                $price = (float) ($single_data['price'] ?? 0);
                $save_po_item->fabric_sku = $fab_data->sku;
                $save_po_item->fabric_id = $fab_data->id;
                $save_po_item->meter = $meter;
                $save_po_item->remaining_quantity = max(0, $meter - ($received[$item_id] ?? 0));
                $save_po_item->price = $price;
                $save_po_item->total_price = $meter * $price;
                $save_po_item->save();
                $kept[] = $save_po_item->id;
            }

            foreach ($lines as $id => $line) {
                if (in_array($id, $kept, true)) {
                    continue;
                }
                if (($rollsOnLine[$id] ?? 0) > 0) {
                    throw new \RuntimeException('Line "' . ($line->fabric->name ?? $line->fabric_sku) . '" cannot be removed because ' . $rollsOnLine[$id] . ' rolls have already been received on it.');
                }
                $line->delete();
            }

            return true;
        });
    }

    public function delete(Request $request)
    {
        $rolls = FabricReceiptDetail::where('purchase_order_id', $request->id)->count();
        if ($rolls > 0) {
            throw new \RuntimeException("This PO cannot be deleted because {$rolls} rolls have already been received on it. To stop using it, close it from Reports > Purchase Order.");
        }
        $data = PurchaseOrder::where('id', $request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function vendors()
    {
        $data = Vendor::with('fabrics')->where('status', 1)->get();
        return $data;
    }
    public function fabrics()
    {
        $data = Fabric::where('status', 1)->get();
        return $data;
    }
    public function vendor($vendor_id)
    {
        $data = Vendor::where('id', $vendor_id)->get();
        return $data;
    }
    public function fabrics_per_vendor($vendor_id)
    {
        $data = Fabric::where('status', 1)->where('vendor_id', $vendor_id)->get();
        return $data;
    }
    public function fabric_warehouses()
    {
        $data = MasterFabricWarehouse::where('status', 1)->get();
        return $data;
    }
    public function companies()
    {
        $data = Company::where('status', 1)->get();
        return $data;
    }
    public function general_setting()
    {
        $data = GeneralSettings::where('status', 1)->first();
        return $data;
    }
}
