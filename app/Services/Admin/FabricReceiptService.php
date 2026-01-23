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
use App\Models\MasterProductSubStage;
use App\Models\PurchaseOrderItem;
use App\Models\MasterFabricWarehouse;
use App\Http\DataTable\Admin\FabricReceiptDataTable as DataTable;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

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

        // dd($request->all());
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
        $save_data->truck_number = $request->truck_number ?? '';
        $save_data->time = $request->time;
        $save_data->roll = count($request->roll_details);
        $save_data->received_by = $request->received_by ?? '';
        $save_data->amount = $request->amount ?? 0.00;
        $save_data->gst_amount = $request->gst_amount ?? 0.00;
        $save_data->gst_percentage = $request->gst_percentage ?? 1;
        $save_data->total_amount = $request->total_amount ?? 0.00;
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

        foreach($request->roll_details as $single_data){
            $fab_data = Fabric::where('id',$single_data['fabric_id'])->first();
            if($fab_data){
                $fabric_sku = $fab_data->sku;
                $fabric_id = $fab_data->id;
                
                $meter = $single_data['meter'];
                $roll_number = $single_data['roll_no'];
                $price = $single_data['price'];
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
                    'fabric_id'   => $fabric_id,
                    'shipment_id' => $shipment_id,
                    'roll_number' => $roll_number,
                    'price'       => $price
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

                $save_data_detail->purchase_order_id = 0;
                $save_data_detail->purchase_order_item_id = 0;
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

                

                // $save_stock = new Stock;
                // $save_stock->sku = $fabric_sku;
                // $save_stock->fabric_id = $fabric_id;
                // $save_stock->master_fabric_warehouse_id = $request->master_fabric_warehouse_id;
                // $save_stock->date = Carbon::now()->format('Y-m-d');
                // $save_stock->goods_entry_number = $save_data_detail->id;
                // $save_stock->meter = $meter;
                // $save_stock->roll = count($request->rolls);
                // /// new col
                // $save_stock->roll_no = $single_data['roll'];
                // $save_stock->qrcode = $fileName;
                // $save_stock->unique_number = $unique_number;
                // $save_stock->batch_no = '';

                // $save_stock->purchase_order_id = null;
                // $save_stock->save();

            }
            

        }
        
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
        $fab_receipt_update = FabricReceipt::where('id',$request->id)->update([
            'roll' => count($request->rolls),
        ]);
        $fab_rec_data = FabricReceipt::where('id',$request->id)->first();
        $master_fabric_warehouse_id = $fab_rec_data->master_fabric_warehouse_id;

        foreach($request->rolls as $single_data){
        
            $fab_data = Fabric::where('id',$single_data['fabric_sku'])->first();
            if($fab_data){ 
                

                $fabric_sku = $fab_data->sku;
                $fabric_id = $fab_data->id;
                
                
                $meter = $single_data['meter'];
                $roll_number = $single_data['roll'];
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
                    $save_data->fabric_id = $fabric_id;
                    $save_data->roll = 1;
                    $save_data->roll_number = $roll_number;
                    $save_data->meter = $meter;
                    $save_data->batch_no = '';
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
    public function fabric_list_by_vendor($vendor_id){
        $data = Fabric::where('status',1)->where('vendor_id',$vendor_id)->get();
        return $data;
    }
    public function new_batch_no(){
        // $data = FabricReceiptDetail::orderBy('id','desc')->first();
        // if($data){
        //     $new_batch_no = $data->batch_no + 1;
        // }else{
        //     $new_batch_no = 1;
        // }
        $new_batch_no = 1;

        return $new_batch_no;
    }

    public function cutting_units(){
        $data = MasterFabricWarehouse::where('status',1)->get();
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
        $exists = FabricReceiptDetail::where('roll_number', $request->roll_no)->exists();
        return $exists;
    }
}