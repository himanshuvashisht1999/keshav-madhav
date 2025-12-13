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

        $data = $query->select('purchase_orders.sku as purchase_order_sku','purchase_orders.created_at as po_date','purchase_orders.delivery_date as expected_delivery_date','purchase_order_items.fabric_sku','purchase_order_items.meter','purchase_order_items.id as purchase_order_item_id','purchase_orders.id as purchase_order_id','purchase_order_items.fabric_id','purchase_orders.vendor_id')->get();
        // dd($data);
        return $data;

    }

    public function adjustmentShipment(Request $request){
        $fabric_id = $request->fabric_id;
        $vendor_id = $request->vendor_id;
        $data = FabricReceiptDetail::join('fabric_receipts','fabric_receipts.id','fabric_receipt_details.fabric_receipt_id')->where('fabric_receipt_details.fabric_id',$fabric_id)->where('fabric_receipts.vendor_id',$vendor_id)->select('fabric_receipts.id','fabric_receipts.time as date_time','fabric_receipts.shipment_id as shipment_number','fabric_receipt_details.meter','fabric_receipt_details.id as fabric_receipt_detail_id')->get();

        return $data;
    }

    public function store(Request $request){
        $save_data = new PurchaseOrder;
        $save_data->sku = '';
        $save_data->date = $request->date;
        $save_data->vendor_id = $request->vendor_id;
        $save_data->delivery_date = $request->delivery_date;
        $save_data->is_notify = $request->is_notify;
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
    

}