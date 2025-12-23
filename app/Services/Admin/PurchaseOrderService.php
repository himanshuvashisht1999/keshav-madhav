<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrder;
use App\Models\Fabric;
use App\Models\Vendor;
use App\Models\PurchaseOrderItem;
use App\Models\ProductionGoods;
use App\Models\FabricReceiptDetail;
use App\Models\GeneralSettings;
use App\Mail\PurchaseOrderMail;
use App\Models\MasterFabricWarehouse;
use Illuminate\Support\Facades\Mail;
use App\Http\DataTable\Admin\PurchaseOrderDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService {
    public function __construct(
        DataTable $datatable,
        PurchaseOrder $purchase_order
    ) {
        $this->datatable= $datatable;
        $this->purchase_order = $purchase_order;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
       
        return $this->datatable->indexList($request);
    }
    public function adjustment(Request $request){
        $query = PurchaseOrderItem::Join('purchase_orders','purchase_orders.id','purchase_order_items.purchase_order_id')->where('purchase_orders.status',1)->where('purchase_order_items.status',1);
        if ($request->has('vendor_id') && $request->filled('vendor_id')) {
            $query->where('purchase_orders.vendor_id', $request->get('vendor_id'));
        }
        if ($request->has('fabric_id') && $request->filled('fabric_id')) {
            $query->where('purchase_order_items.fabric_id', $request->get('fabric_id'));
        }

        $data = $query->select('purchase_orders.sku as purchase_order_sku','purchase_orders.created_at as po_date','purchase_orders.delivery_date as expected_delivery_date','purchase_order_items.fabric_sku','purchase_order_items.meter','purchase_order_items.remaining_quantity','purchase_order_items.id as purchase_order_item_id','purchase_orders.id as purchase_order_id','purchase_order_items.fabric_id','purchase_orders.vendor_id')->get();
        // dd($data);
        return $data;

    }

    public function adjustmentShipment(Request $request){
        $fabric_id = $request->fabric_id;
        $vendor_id = $request->vendor_id;
        $data = FabricReceiptDetail::join('fabric_receipts','fabric_receipts.id','fabric_receipt_details.fabric_receipt_id')->where('fabric_receipt_details.fabric_id',$fabric_id)->where('fabric_receipts.vendor_id',$vendor_id)->select('fabric_receipts.id','fabric_receipts.time as date_time','fabric_receipts.shipment_id as shipment_number','fabric_receipt_details.meter','fabric_receipt_details.id as fabric_receipt_detail_id')->where('fabric_receipt_details.status',1)->get();

        return $data;
    }

    public function store(Request $request){
        $save_data = new PurchaseOrder;
        $save_data->sku = '';
        $save_data->date = $request->date;
        $save_data->vendor_id = $request->vendor_id;
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

        foreach($request->fabrics as $single_data){
            $fab_data = Fabric::where('id',$single_data['fabric_id'])->first();
            if($fab_data){
                $save_po_item = new PurchaseOrderItem;
                $save_po_item->purchase_order_id = $save_data->id;
                $save_po_item->fabric_sku = $fab_data->sku;
                //$save_po_item->sku = $single_data['sku'];
                $save_po_item->fabric_id = $fab_data->id;
                $save_po_item->meter = $single_data['meter'];
                $save_po_item->remaining_quantity = $single_data['meter'];
                $save_po_item->price = $single_data['price'];
                $save_po_item->total_price = $single_data['meter'] * $single_data['price'];
                $save_po_item->save();

            }
            
        }

        // Reload purchase order with relationships for email
        $data = PurchaseOrder::with('items','vendor')->find($save_data->id);

        // ✅ Send email with same $data you use in view
        // if ($data->vendor && $data->vendor->email) {
        //     Mail::to($data->vendor->email)->send(new PurchaseOrderMail($data));
        // }

        
        return true;
    }
    public function view(Request $request){
        $data = PurchaseOrder::with('items','vendor')->where('id',$request->id)->first();
        return $data;
    }

    public function edit(Request $request){
        $data = PurchaseOrder::with('fabric_warehouse')->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = PurchaseOrder::find($request->id);
        $update_data->sku = $request->sku;
        $update_data->date = $request->date;
        $update_data->vendor_id = $request->vendor_id;
        $update_data->delivery_date = $request->delivery_date;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    public function delete(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function vendors(){
        $data = Vendor::with('fabrics')->where('status',1)->get();
        return $data;
    }
    public function fabrics(){
        $data = Fabric::where('status',1)->get();
        return $data;
    }
    public function vendor($vendor_id){
        $data = Vendor::where('id',$vendor_id)->get();
        return $data;
    }
    public function fabrics_per_vendor($vendor_id){
        $data = Fabric::where('status',1)->where('vendor_id',$vendor_id)->get();
        return $data;
    }
    public function fabric_warehouses(){
        $data = MasterFabricWarehouse::where('status',1)->get();
        return $data;
    }
    public function general_setting(){
        $data = GeneralSettings::where('status',1)->first();
        return $data;
    }
    
    public function products(){
        $products = ProductionGoods::with('mainImage','bill_of_materials.fabric')->where('status',1)->get();
        $data = [];
        foreach($products as $product){
            $bom = $product->bill_of_materials->first(); // safer than [0]
            $fabric_meter = $bom->meter ?? 2;
            $fabric_image = $bom && $bom->fabric ? $bom->fabric->image : null;
            $fabric_id = $bom && $bom->fabric ? $bom->fabric->id : null;
            $fabric_name = $bom && $bom->fabric ? $bom->fabric->name : null;
            $vendor_id = $bom && $bom->fabric ? $bom->fabric->vendor_id : null;

            $data[] = [
                'product_id'      => $product->id,
                'product_sku'     => $product->sku,
                'design_number'   => $product->design_number,
                'name_of_garment' => $product->name_of_garment,
                'product_image'   => $product->mainImage->image ?? null,
                'fabric_meter'    => $fabric_meter,
                'fabric_image'    => $fabric_image,
                'vendor_id'    => $vendor_id,
                'fabric_id'    => $fabric_id,
                'fabric_name'    => $fabric_name,
            ];
        }
        return $data;
    }

    public function resend(Request $request){
        $data = PurchaseOrder::with('items','vendor')->find($request->id);

        // ✅ Send email with same $data you use in view
        // if ($data->vendor && $data->vendor->email) {
        //     Mail::to($data->vendor->email)->send(new PurchaseOrderMail($data));
        // }
        return true;
    }

    public function adjustmentSubmit(Request $request)
    {

        DB::beginTransaction();

        try {

            // 🔒 Lock PO item row to avoid race conditions
            $purchaseOrderItem = PurchaseOrderItem::lockForUpdate()
                ->where('id', $request->purchase_order_item_id)
                ->first();

            if (!$purchaseOrderItem) {
                $result['status'] = 0;
                $result['message'] = 'Purchase order item not found';
                return $result;
               
            }

            $shipmentIds = $request->fabric_receipt_detail_id;

            // Fetch shipment details (only unused)
            $shipments = FabricReceiptDetail::whereIn('id', $shipmentIds)
                ->where('status', '!=', 2) // not already adjusted
                ->get();

            if ($shipments->count() !== count($shipmentIds)) {
                
                $result['status'] = 0;
                $result['message'] = 'One or more shipments are already adjusted';
                return $result;
            }

            // 🔢 Total shipment quantity
            $totalMeterRequest = $shipments->sum('meter');

            $totalRemainingQty = $purchaseOrderItem->remaining_quantity;
            $originalMeterQty  = $purchaseOrderItem->meter;

            // ✅ Update shipment records
            FabricReceiptDetail::whereIn('id', $shipmentIds)->update([
                'status' => 2,
                'purchase_order_id' => $purchaseOrderItem->purchase_order_id,
                'purchase_order_item_id' => $purchaseOrderItem->id,
            ]);

            // ✅ Business logic: over-delivery handling
            if ($totalMeterRequest >= $totalRemainingQty) {

                // Extra quantity supplied by vendor
                $extraReceived = $totalMeterRequest - $totalRemainingQty;

                PurchaseOrderItem::where('id', $purchaseOrderItem->id)->update([
                    'meter' => $originalMeterQty + $extraReceived,
                    'remaining_quantity' => 0,
                    'status' => 2, // closed
                ]);

            } else {

                PurchaseOrderItem::where('id', $purchaseOrderItem->id)->update([
                    'remaining_quantity' => $totalRemainingQty - $totalMeterRequest,
                ]);
            }

            DB::commit();

            $result['status'] = 1;
            $result['message'] = 'Purchase order adjusted successfully';
            return $result;

        } catch (\Exception $e) {

            DB::rollBack();

            $result['status'] = 0;
            $result['message'] = $e->getMessage();
            return $result;
        }
    }

    // public function adjustmentDynamic(){
        
    //     $fabric_receipt_details = FabricReceiptDetail::join('fabric_receipts','fabric_receipts.id','fabric_receipt_details.fabric_receipt_id')->where('fabric_receipt_details.status',1)->select('fabric_receipt_details.id as fabric_receipt_detail_id','fabric_receipts.vendor_id','fabric_receipt_details.fabric_id')->get();
    //     foreach($fabric_receipt_details as $fabric_receipt_detail){
    //         $purchase_order_detail = PurchaseOrderItem::join('purchase_orders','purchase_orders.id','purchase_order_items.purchase_order_id')->where('purchase_orders.vendor_id',$fabric_receipt_detail->vendor_id)->where('purchase_order_items.fabric_id',$fabric_receipt_detail->fabric_id)->get();

            

    //     }
        
    // }

    public function adjustmentDynamic()
    {
        DB::beginTransaction();

        try {

            // 1️⃣ Get unassigned fabric receipt details
            $receiptDetails = FabricReceiptDetail::join(
                    'fabric_receipts',
                    'fabric_receipts.id',
                    '=',
                    'fabric_receipt_details.fabric_receipt_id'
                )
                ->where('fabric_receipt_details.status', 1) // unassigned
                ->select(
                    'fabric_receipt_details.id as fabric_receipt_detail_id',
                    'fabric_receipt_details.fabric_id',
                    'fabric_receipt_details.meter',
                    'fabric_receipts.vendor_id',
                    'fabric_receipts.master_fabric_warehouse_id'
                )
                ->get();

            if ($receiptDetails->isEmpty()) {
                return [
                    'status' => 0,
                    'message' => 'No unassigned fabric receipts found'
                ];
            }

            // 2️⃣ Group by vendor
            $groupedByVendor = $receiptDetails->groupBy('vendor_id');

            foreach ($groupedByVendor as $vendorId => $vendorRows) {

                // 🔹 Group vendor receipts by fabric
                $groupedByFabric = $vendorRows->groupBy('fabric_id');

                foreach ($groupedByFabric as $fabricId => $fabricRows) {

                    // 🔴 IMPORTANT CHECK
                    // If an open PO item exists → skip auto creation
                    $openPoExists = PurchaseOrderItem::join(
                            'purchase_orders',
                            'purchase_orders.id',
                            '=',
                            'purchase_order_items.purchase_order_id'
                        )
                        ->where('purchase_orders.vendor_id', $vendorId)
                        ->where('purchase_order_items.fabric_id', $fabricId)
                        ->where('purchase_order_items.remaining_quantity', '>', 0)
                        ->where('purchase_order_items.status', 1)
                        ->exists();

                    if ($openPoExists) {
                        // ❌ DO NOTHING → normal adjustment flow will handle
                        continue;
                    }

                    // ✅ AUTO-CREATE PO (only when no PO exists)
                    $warehouseId = $fabricRows->first()->master_fabric_warehouse_id;

                    $purchaseOrder = new PurchaseOrder;
                    $purchaseOrder->sku = '';
                    $purchaseOrder->date = now()->format('Y-m-d');
                    $purchaseOrder->vendor_id = $vendorId;
                    $purchaseOrder->delivery_date = now()->format('Y-m-d');
                    $purchaseOrder->is_notify = 1;
                    $purchaseOrder->fabric_warehouse_id = $warehouseId;
                    $purchaseOrder->status = 1;
                    $purchaseOrder->save();

                    // Generate SKU
                    $date = \Carbon\Carbon::parse($purchaseOrder->date)->format('dmY');
                    $sku = "PO/" . $date . "/" . $purchaseOrder->id;
                    $purchaseOrder->update(['sku' => $sku]);

                    // Fetch fabric
                    $fabric = Fabric::find($fabricId);
                    if (!$fabric) {
                        continue;
                    }

                    $totalMeter = $fabricRows->sum('meter');

                    // Create PO item
                    $poItem = new PurchaseOrderItem;
                    $poItem->purchase_order_id = $purchaseOrder->id;
                    $poItem->fabric_sku = $fabric->sku;
                    $poItem->fabric_id = $fabric->id;
                    $poItem->meter = $totalMeter;
                    $poItem->remaining_quantity = 0;
                    $poItem->price = 0;
                    $poItem->total_price = 0;
                    $poItem->status = 2; // closed
                    $poItem->save();

                    // Assign receipts
                    $receiptIds = $fabricRows->pluck('fabric_receipt_detail_id');

                    FabricReceiptDetail::whereIn('id', $receiptIds)->update([
                        'status' => 2,
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_item_id' => $poItem->id,
                    ]);
                }
            }

            DB::commit();

            return [
                'status' => 1,
                'message' => 'Auto adjustment completed successfully'
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'status' => 0,
                'message' => $e->getMessage()
            ];
        }
    }


    

}