<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrderMaterial;
use App\Models\ItemReceipt;
use App\Models\ItemReceiptDetail;
use App\Models\ItemAttributeValue;
use App\Models\Vendor;
use App\Models\ItemStock;
use App\Models\PurchaseOrderMaterialItem;
use App\Http\DataTable\Admin\ItemReceiptDataTable as DataTable;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class ItemReceiptService {
    public function __construct(
        DataTable $datatable,
        ItemReceipt $item_receipt
    ) {
        $this->datatable= $datatable;
        $this->item_receipt = $item_receipt;
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
        $save_data = new ItemReceipt;
        $save_data->sku = '';
        $save_data->vendor_id = $request->vendor_id;
        $save_data->truck_number = $request->truck_number;
        $save_data->time = $request->time;
        $save_data->box = $request->box;
        $save_data->received_by = $request->received_by;
        $save_data->shipment_photo = $imgName;
        $save_data->challan_photo = $imgName2;
        $save_data->status = 0;
        $save_data->save();

        $sku = 'IR-'.$save_data->id;
        $update_data = ItemReceipt::where('id',$save_data->id)->update([
            'sku' => $sku,
        ]);      
        return $save_data->id;
    }
    public function view(Request $request){
        $data = ItemReceipt::with('vendor','details.purchase_order','details.purchase_order_item','details.item_attribute_value')->where('id',$request->id)->first();
        return $data;
    }
    public function edit(Request $request){
        $data = ItemReceipt::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = ItemReceipt::find($request->id);
        $update_data->sku = $request->sku;
        $update_data->vendor_id = $request->vendor_id;
        $update_data->truck_number = $request->truck_number;
        $update_data->time = $request->time;
        $update_data->box = $request->box;
        $update_data->received_by = $request->received_by;
        $update_data->save();
        return true;
    }
    public function delete(Request $request){
        $data = ItemReceipt::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function vendors(){
        $data = Vendor::where('status',1)->get();
        return $data;
    }
    public function items(){
        $data = ItemAttributeValue::where('status',1)->get();
        return $data;
    }
    public function purchase_orders(Request $request){
        $data = PurchaseOrderMaterial::where('vendor_id',$request->vendor_id)->orderBy('id','desc')->get();
        return $data;
    }
    public function purchase_order_items($purchase_order_id){
        $data = PurchaseOrderMaterialItem::where('purchase_order_id',$purchase_order_id)->where('status',1)->get();
        return $data;
    }
    public function storeDetail(Request $request){ 
        foreach($request->boxes as $single_data){
        
            $item_data = ItemAttributeValue::where('id',$single_data['item_sku'])->first();
            if($item_data){ 
                $item_sku = $item_data->sku;
                $item_id = $item_data->id;
                
                
                $quantity = $single_data['quantity'];
                $purchase_order_id = $request->purchase_order_id;
                $purchase_order_item_id = 0;
                $purchase_order_data = PurchaseOrderMaterial::where('id',$purchase_order_id)->first();
                if($purchase_order_data){
                    $purchase_order_item_data = PurchaseOrderMaterialItem::where('purchase_order_material_id',$purchase_order_data->id)->where('item_attribute_value_sku',$item_sku)->first();
                    if($purchase_order_item_data){
                        $purchase_order_item_id = $purchase_order_item_data->id;
                    }else{
                        $total_quantity = $quantity;
                        $price_static = 100;
                        $total_price = $total_quantity * $price_static;

                        $save_purchase_order_item = new PurchaseOrderMaterialItem;
                        $save_purchase_order_item->purchase_order_material_id = $purchase_order_id;
                        $save_purchase_order_item->item_attribute_value_sku = $item_sku;
                        $save_purchase_order_item->sku = '';
                        $save_purchase_order_item->quantity = $total_quantity;
                        $save_purchase_order_item->price = $price_static;
                        $save_purchase_order_item->total_price = $total_price;
                        $save_purchase_order_item->save();
                        $purchase_order_item_id = $save_purchase_order_item->id;
                    }

                }else{
                    $receipt_data = ItemReceipt::where('id',$request->id)->first();
                    $save_data_purchase = new PurchaseOrderMaterial;
                    $save_data_purchase->sku = '';
                    $save_data_purchase->date = Carbon::now()->format('Y-m-d'); /////We have no date
                    $save_data_purchase->vendor_id = $receipt_data->vendor_id;
                    $save_data_purchase->delivery_date = Carbon::now()->format('Y-m-d'); /// we have no delivery date
                    $save_data_purchase->status = 1;
                    $save_data_purchase->save();
                    $sku_update = PurchaseOrderMaterial::where('id',$save_data_purchase->id)->update([
                        'sku' => 'PO-'.$save_data_purchase->id,
                    ]);

                    $total_quantity =  $quantity;
                    $price_static = 100;
                    $total_price = $total_quantity * $price_static;

                    $save_purchase_order_item = new PurchaseOrderMaterialItem;
                    $save_purchase_order_item->purchase_order_material_id = $save_data_purchase->id;
                    $save_purchase_order_item->item_attribute_value_sku = $item_sku;
                    $save_purchase_order_item->sku = '';
                    $save_purchase_order_item->quantity = $total_quantity;
                    $save_purchase_order_item->price = $price_static;
                    $save_purchase_order_item->total_price = $total_price;
                    $save_purchase_order_item->save();
                    $purchase_order_item_id = $save_purchase_order_item->id;


                }
                $receipt_data = ItemReceipt::where('id',$request->id)->first();
                $total_box = $receipt_data->box;
                $box_no = $single_data['box'];

                $purchase_order_item_data = PurchaseOrderMaterialItem::where('id',$purchase_order_item_id)->first();
                if($purchase_order_item_data){
                    $save_data = new ItemReceiptDetail;
                    // $save_data->sku = $item_sku;
                    $save_data->fabric_receipt_id = $request->id;

                    $save_data->purchase_order_id = $purchase_order_item_data->purchase_order_material_id;
                    $save_data->purchase_order_item_id = $purchase_order_item_data->id;
                    $save_data->fabric_sku = $item_sku;
                    $save_data->box = 1;
                    $save_data->quantity = $quantity;
                    $save_data->batch_no = $single_data['batch'];
                    $save_data->status = 1;
                    $save_data->save();

                    $item_receipt_status_update = ItemReceipt::where('id',$request->id)->update([
                        'status' => 1,
                    ]);

                    ///// save data in stocks
                    $unique_number = $save_data->id . '/' . $total_box . '/' . $box_no;
                    $fileName = $save_data->id . '_' . $total_box . '_' . $box_no . '.png';

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

                    $save_stock = new ItemStock;
                    $save_stock->sku = $item_sku;
                    $save_stock->date = Carbon::now()->format('Y-m-d');
                    $save_stock->goods_entry_number = $save_data->id;
                    $save_stock->quantity = $quantity;
                    $save_stock->box = $total_box;
                    /// new col
                    $save_stock->box_no = $single_data['box'];
                    $save_stock->qrcode = $fileName;
                    $save_stock->unique_number = $unique_number;
                    $save_stock->batch_no = $single_data['batch'];

                    $save_stock->purchase_order_id = $purchase_order_item_data->purchase_order_material_id;
                    $save_stock->save();

                    
                    $update_purchase_order_item = PurchaseOrderMaterialItem::where('id',$purchase_order_item_data->id)->update([
                        'status' => 2,
                    ]);

                }

            }
        }
                
        return true;
    }
    public function new_batch_no(){
        $last_batch = ItemReceiptDetail::orderBy('id','desc')->first();
        if($last_batch && $last_batch->batch_no){
            $batch_number = intval($last_batch->batch_no) + 1;
        }else{
            $batch_number = 1;
        }
        return $batch_number;
    }
}
