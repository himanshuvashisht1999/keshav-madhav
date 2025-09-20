<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrder;
use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;
use App\Models\Fabric;
use App\Models\Vendor;
use App\Models\Stock;
use App\Models\StockExpend;
use App\Models\PurchaseOrderItem;
use App\Http\DataTable\Admin\FabricReceiptDataTable as DataTable;
use Carbon\Carbon;

class FabricReceiptService {
    public function __construct(
        DataTable $datatable,
        FabricReceipt $fabric_receipt
    ) {
        $this->datatable= $datatable;
        $this->fabric_receipt = $fabric_receipt;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
       
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $imgName = '';
        if($request->file('shipment_photo')){
            $image = $request->file('shipment_photo');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "shipment-image-".rand()."_".time().".".$extImage;
            $destinationPath = public_path().'/assets/shipment-image';
            $image->move($destinationPath, $imgName);
        }
        $save_data = new FabricReceipt;
        $save_data->sku = $request->sku;
        $save_data->vendor_id = $request->vendor_id;
        $save_data->truck_number = $request->truck_number;
        $save_data->time = $request->time;
        $save_data->roll = $request->roll;
        $save_data->received_by = $request->received_by;
        $save_data->shipment_photo = $imgName;
        $save_data->status = 0;
        $save_data->save();

        
        return $save_data->id;
    }
    public function view(Request $request){
        $data = FabricReceipt::with('vendor','details.purchase_order','details.purchase_order_item','details.fabric')->where('id',$request->id)->first();
        return $data;
    }

    public function edit(Request $request){
        $data = FabricReceipt::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = FabricReceipt::find($request->id);
        $update_data->sku = $request->sku;
        $update_data->vendor_id = $request->vendor_id;
        $update_data->truck_number = $request->truck_number;
        $update_data->time = $request->time;
        $update_data->roll = $request->roll;
        $update_data->received_by = $request->received_by;
        // $update_data->status = 1;
        $update_data->save();

        return true;
    }

    public function delete(Request $request){
        $data = FabricReceipt::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function vendors(){
        $data = Vendor::where('status',1)->get();
        return $data;
    }
    public function purchase_orders(Request $request){
        $data = PurchaseOrder::where('vendor_id',$request->vendor_id)->orderBy('id','desc')->get();
        return $data;
    }
    public function purchase_order_items($purchase_order_id){
        $data = PurchaseOrderItem::where('purchase_order_id',$purchase_order_id)->where('status',1)->get();
        return $data;
    }


    public function storeDetail(Request $request){
        $purchase_order_item_data = PurchaseOrderItem::where('id',$request->fabric_sku)->first();
        if($purchase_order_item_data){
            $fabric_sku = $purchase_order_item_data->fabric_sku;
            $roll = $request->roll;
            $meter = $request->meter;
            $purchase_order_id = $request->purchase_order_id;

            $save_data = new FabricReceiptDetail;
            $save_data->sku = $fabric_sku;
            $save_data->fabric_receipt_id = $request->id;

            $save_data->purchase_order_id = $purchase_order_id;
            $save_data->purchase_order_item_id = $purchase_order_item_data->id;
            $save_data->fabric_sku = $fabric_sku;
            $save_data->roll = $roll;
            $save_data->meter = $meter;
            $save_data->status = 1;
            $save_data->save();

            $fabric_receipt_status_update = FabricReceipt::where('id',$request->id)->update([
                'status' => 1,
            ]);

            ///// save data in stocks
            $save_stock = new Stock;
            $save_stock->sku = $fabric_sku;
            $save_stock->date = Carbon::now()->format('Y-m-d');
            $save_stock->goods_entry_number = $save_data->id;
            $save_stock->meter = $meter;
            $save_stock->roll = $roll;
            $save_stock->purchase_order_id = $purchase_order_id;
            $save_stock->save();

            for($i=1;$i<=$roll;$i++){
                $barcode = strtoupper($fabric_sku) . '-' . $save_stock->id . '-' . $i;
                $save_stock_expend = new StockExpend;
                $save_stock_expend->sku = $fabric_sku;
                $save_stock_expend->stock_id = $save_stock->id;
                $save_stock_expend->roll = $roll;
                $save_stock_expend->roll_no = $i;
                $save_stock_expend->barcode = $barcode;
                $save_stock_expend->save();
            }
            $update_purchase_order_item = PurchaseOrderItem::where('id',$purchase_order_item_data->id)->update([
                'status' => 2,
            ]);

        }
                
        return $save_data->id;
    }

}