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
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

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
            $destinationPath = public_path().'/assets/receipts/shipment-image';
            $image->move($destinationPath, $imgName);
        }
        $imgName2 = '';
        if($request->file('challan_photo')){
            $image = $request->file('challan_photo');
            $extImage = $image->getClientOriginalExtension();
            $imgName2 = "challan-image-".rand()."_".time().".".$extImage;
            $destinationPath = public_path().'/assets/receipts/challan-image';
            $image->move($destinationPath, $imgName2);
        }
        $save_data = new FabricReceipt;
        $save_data->sku = '';
        $save_data->vendor_id = $request->vendor_id;
        $save_data->truck_number = $request->truck_number;
        $save_data->time = $request->time;
        $save_data->roll = $request->roll;
        $save_data->received_by = $request->received_by;
        $save_data->shipment_photo = $imgName;
        $save_data->challan_photo = $imgName2;
        $save_data->status = 0;
        $save_data->save();

        $sku = 'FR-'.$save_data->id;
        $update_data = FabricReceipt::where('id',$save_data->id)->update([
            'sku' => $sku,
        ]);
        
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


    public function storeDetailOld(Request $request){ 
        $fab_data = Fabric::where('id',$request->fabric_sku)->first();
        if($fab_data){ 
            $fabric_sku = $fab_data->sku;
            $fabric_id = $fab_data->id;
            
            $roll = $request->roll;
            $meter = $request->meter;
            $purchase_order_id = $request->purchase_order_id;
            $purchase_order_item_id = 0;
            $purchase_order_data = PurchaseOrder::where('id',$purchase_order_id)->first();
            if($purchase_order_data){
                $purchase_order_item_data = PurchaseOrderItem::where('purchase_order_id',$purchase_order_data->id)->where('fabric_sku',$fabric_sku)->first();
                if($purchase_order_item_data){
                    $purchase_order_item_id = $purchase_order_item_data->id;
                }else{
                    $total_meter = $roll * $meter;
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

            }else{
                $receipt_data = FabricReceipt::where('id',$request->id)->first();
                $save_data_purchase = new PurchaseOrder;
                $save_data_purchase->sku = '';
                $save_data_purchase->date = Carbon::now()->format('Y-m-d'); /////We have no date
                $save_data_purchase->vendor_id = $receipt_data->vendor_id;
                $save_data_purchase->delivery_date = Carbon::now()->format('Y-m-d'); /// we have no delivery date
                $save_data_purchase->status = 1;
                $save_data_purchase->save();
                $sku_update = PurchaseOrder::where('id',$save_data_purchase->id)->update([
                    'sku' => 'PO-'.$save_data_purchase->id,
                ]);

                $total_meter = $roll * $meter;
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

            $purchase_order_item_data = PurchaseOrderItem::where('id',$purchase_order_item_id)->first();
            if($purchase_order_item_data){
                $save_data = new FabricReceiptDetail;
                // $save_data->sku = $fabric_sku;
                $save_data->fabric_receipt_id = $request->id;

                $save_data->purchase_order_id = $purchase_order_item_data->purchase_order_id;
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
                $save_stock->purchase_order_id = $purchase_order_item_data->purchase_order_id;
                $save_stock->save();

                for($i=1;$i<=$roll;$i++){
                    $unique_number = $save_stock->id . '/' . $roll . '/' . $i;
                    $fileName = $save_stock->id . '_' . $roll . '_' . $i . '.png'; // clean filename

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

                    $save_stock_expend = new StockExpend;
                    $save_stock_expend->sku = $fabric_sku;
                    $save_stock_expend->stock_id = $save_stock->id;
                    $save_stock_expend->roll = $roll;
                    $save_stock_expend->roll_no = $i;
                    $save_stock_expend->qrcode = $fileName;
                    $save_stock_expend->unique_number = $unique_number;
                    $save_stock_expend->save();
                }
                $update_purchase_order_item = PurchaseOrderItem::where('id',$purchase_order_item_data->id)->update([
                    'status' => 2,
                ]);

            }

        }
                
        return $save_data->id;
    }
    public function storeDetail(Request $request){ 
        foreach($request->rolls as $single_data){
        
            $fab_data = Fabric::where('id',$single_data['fabric_sku'])->first();
            if($fab_data){ 
                $fabric_sku = $fab_data->sku;
                $fabric_id = $fab_data->id;
                
                
                $meter = $single_data['meter'];
                $purchase_order_id = $request->purchase_order_id;
                $purchase_order_item_id = 0;
                $purchase_order_data = PurchaseOrder::where('id',$purchase_order_id)->first();
                if($purchase_order_data){
                    $purchase_order_item_data = PurchaseOrderItem::where('purchase_order_id',$purchase_order_data->id)->where('fabric_sku',$fabric_sku)->first();
                    if($purchase_order_item_data){
                        $purchase_order_item_id = $purchase_order_item_data->id;
                    }else{
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

                }else{
                    $receipt_data = FabricReceipt::where('id',$request->id)->first();
                    $save_data_purchase = new PurchaseOrder;
                    $save_data_purchase->sku = '';
                    $save_data_purchase->date = Carbon::now()->format('Y-m-d'); /////We have no date
                    $save_data_purchase->vendor_id = $receipt_data->vendor_id;
                    $save_data_purchase->delivery_date = Carbon::now()->format('Y-m-d'); /// we have no delivery date
                    $save_data_purchase->status = 1;
                    $save_data_purchase->save();
                    $sku_update = PurchaseOrder::where('id',$save_data_purchase->id)->update([
                        'sku' => 'PO-'.$save_data_purchase->id,
                    ]);

                    $total_meter =  $meter;
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
                $receipt_data = FabricReceipt::where('id',$request->id)->first();
                $total_roll = $receipt_data->roll;
                $roll_no = $single_data['roll'];

                $purchase_order_item_data = PurchaseOrderItem::where('id',$purchase_order_item_id)->first();
                if($purchase_order_item_data){
                    $save_data = new FabricReceiptDetail;
                    // $save_data->sku = $fabric_sku;
                    $save_data->fabric_receipt_id = $request->id;

                    $save_data->purchase_order_id = $purchase_order_item_data->purchase_order_id;
                    $save_data->purchase_order_item_id = $purchase_order_item_data->id;
                    $save_data->fabric_sku = $fabric_sku;
                    $save_data->roll = 1;
                    $save_data->meter = $meter;
                    $save_data->batch_no = $single_data['batch'];
                    $save_data->status = 1;
                    $save_data->save();

                    $fabric_receipt_status_update = FabricReceipt::where('id',$request->id)->update([
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
                    $save_stock->date = Carbon::now()->format('Y-m-d');
                    $save_stock->goods_entry_number = $save_data->id;
                    $save_stock->meter = $meter;
                    $save_stock->roll = $total_roll;
                    /// new col
                    $save_stock->roll_no = $single_data['roll'];
                    $save_stock->qrcode = $fileName;
                    $save_stock->unique_number = $unique_number;
                    $save_stock->batch_no = $single_data['batch'];

                    $save_stock->purchase_order_id = $purchase_order_item_data->purchase_order_id;
                    $save_stock->save();

                    
                    $update_purchase_order_item = PurchaseOrderItem::where('id',$purchase_order_item_data->id)->update([
                        'status' => 2,
                    ]);

                }

            }
        }
                
        return true;
    }

    public function fabrics(){
        $data = Fabric::where('status',1)->get();
        return $data;
    }

}