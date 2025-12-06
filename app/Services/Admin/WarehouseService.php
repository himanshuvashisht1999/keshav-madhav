<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ProductionGoods;
use App\Models\OrderProductDetail;
use App\Models\Stock;
use App\Models\OrderProductDetailStock;
use App\Models\OrderProductStage;
use App\Models\OrderStageTransaction;
use App\Models\ProductStage;
use App\Models\MasterCustomer;
use App\Models\MasterProductSubStage;
use App\Models\OrderMain;
use App\Models\ProductionGoodsItem;
use App\Models\OrderProductItem;
use App\Models\OrderProductItemTransaction;
use App\Models\ItemStock;
use App\Models\WarehouseDetail;
use App\Models\MasterProductStage;
use App\Models\MasterWarehouse;
use App\Models\MasterWarehouseBlock;
use App\Models\PackageBox;
use App\Models\PackageBoxItem;
use App\Models\Package;
use PDF; // Barryvdh DomPDF


use App\Http\DataTable\Admin\WarehouseDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class WarehouseService {
    public function __construct(
        DataTable $datatable,
        Order $order
    ) {
        $this->datatable= $datatable;
        $this->order = $order;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
       
        return $this->datatable->indexList($request);
    }
    public function indexListOrder(Request $request){
       
        return $this->datatable->indexListOrder($request);
    }
    public function indexListListing(Request $request){
       
        return $this->datatable->indexListListing($request);
    }

    

    public function view(Request $request){
        $data = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        return $data;
    }
    public function produce(Request $request){
        $data = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        return $data;
    }
    
    public function products(){
        $data = ProductionGoods::where('status',1)->get();
        return $data;
    }
    


    public function customers(){
        $data = MasterCustomer::where('status',1)->get();
        return $data;
    }


    public function productStatusHoverData(Request $request){
        $data_obj = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        // $data = json_decode(json_encode($data)); 
        $data_obj = $data_obj ? $data_obj->toArray() : [];
        $products = $data_obj['products'] ?? [];
        $data = [];
        
        foreach ($products as $product_data) {
            foreach ($product_data['order_stages'] as $key=>$order_stages) {
                $stage_name = $order_stages['stage']['name'] ?? '';
                $data[$key+1] = [
                    'name' => $stage_name,
                    'total_qty' => $order_stages['total_qty'],
                    'completed_qty' => $order_stages['completed_qty'],
                    'pending_qty' => $order_stages['pending_qty'],
                    'status' => $order_stages['status'], 
                    'stage_id' => $order_stages['stage_id'],
                ];
            }
        }  
        // dd($data);
        return response()->json($data);
    }

    public function sub_stages_cutting(){
        $data = getCuttingSubStages();
        return $data;
    }
    // public function stage_data(Request $request){
    //     $data = MasterProductStage::with('sub_stages')->where('id',$request->stage_id)->first();
    //     return $data;
    // }
    
    public function product_stage(){
        $data = MasterProductStage::where('status',1)->get();
        return $data;
    }
    public function master_blocks(){
        $data = MasterWarehouseBlock::where('status',1)->get();
        return $data;
    }
    public function order_data($order_id){
        $data = OrderMain::with('order_products')->where('id',$order_id)->first();
        return $data;
    }
    public function product_types($order_id)
    {
        $data = OrderMain::with('orders.product')->find($order_id);

        if (!$data) {
            return collect(); // or return []; if you prefer array
        }

        $productTypeSku = $data->orders
            ->map(function ($order) {
                return $order->product->product_data->type_of_garment
                    ?? null; // full null safety
            })
            ->filter()  // remove null values
            ->unique()  // keep only unique SKU values
            ->values(); // reset index
        return $productTypeSku;
    }

    public function packagingStore(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $order_main_id = $request->order_id;
            $quantity   = (int) $request->quantity; // how many items per box

            $order_main_data = OrderMain::with('order_products')->where('id',$order_main_id)->first();
            $order_product_ids = OrderProduct::where('order_main_id',$order_main_id)->pluck('id');

            if ($quantity <= 0) {
                return [
                    'status'  => 0,
                    'message' => 'Quantity per box must be greater than 0.',
                ];
            }
            
            
            $package_save = Package::where('order_main_id',$order_main_id)->first();
            if($package_save){}else{
                $package_save = new Package;
            }
            $package_save->order_main_id = $order_main_id;
            $package_save->save();

            $save_package_box = new PackageBox;
            $save_package_box->package_id = $package_save->id;
            $save_package_box->order_main_id = $order_main_id;
            $save_package_box->quantity = $quantity;
            $save_package_box->description = $request->description;
            $save_package_box->save();


            foreach($request->product_skus as $order_product_id){
                $order_product_data = OrderProduct::where('id',$order_product_id)->first();

                if (!$order_product_data) {
                    continue; // skip if not valid / doesn't belong to this order
                }

                if($order_product_data){
                    $save_box_item = new PackageBoxItem;
                    $save_box_item->package_box_id = $save_package_box->id;
                    $save_box_item->product_sku = $order_product_data->product_sku;
                    $save_box_item->save();
                    $update_ware_house_detail = WarehouseDetail::where('order_product_id',$order_product_data->id)->where('remaining_qty','>',0)->first();
                    if($update_ware_house_detail){

                        $old_remaining_quantity = $update_ware_house_detail->remaining_qty;
                        $update_ware_house_detail->remaining_qty = $old_remaining_quantity - 1;
                        $update_ware_house_detail->save();

                    }
                }
                
                
            }
                        
            ///////////// order main status update
            $total_quantity = 0;
            $order_product_data = OrderProduct::where('order_main_id',$order_main_id)->select('quantity')->get();
            foreach($order_product_data as $single_data){
                $total_quantity = $total_quantity + $single_data->quantity;
            }
            $packaged_items = PackageBox::where('order_main_id',$order_main_id)->select('quantity')->get();
            $total_packed_quantity = 0;
            foreach($packaged_items as $single_data){
                $total_packed_quantity = $total_packed_quantity + $single_data->quantity;
            }
            if($total_packed_quantity == $total_quantity){
                $status_update = OrderMain::where('id', $order_main_id)->update([
                    'status' => 2,
                ]);
            }
            

            return [
                'status'        => 1,
                'message'       => 'successfully packed products.',
            ];
        });
    }

    public function package_data($order_id){
        $data = Package::with('package_boxes.package_boxes_items')->where('order_main_id',$order_id)->get();
        return $data;
    }
    public function packagingShow($package_id){
        $data = Package::with('package_boxes.package_boxes_items')->where('id',$package_id)->first();
        return $data;
    }
    public function barcodeDownload($boxId){
        $box = PackageBox::findOrFail($boxId);

        // $barcodeValue = 'BOX-' . $box->id;
        $barcodeValue = sprintf('%08d', $box->id);
        // Generate Barcode as PNG base64
        $barcode = \DNS1D::getBarcodePNG($barcodeValue, 'C128', 3, 120); // 3 = width scale, 120px height

        $data = [
            'box' => $box,
            'barcodeValue' => $barcodeValue,
            'barcodeImage' => $barcode
        ];

        $pdf = PDF::loadView('admin.warehouse.barcode_pdf', $data)
            ->setPaper('A6', 'portrait'); // Small Sticker Format

        return $pdf->download('barcode-' . $barcodeValue . '.pdf');
    }

    public function warehouse_data(){
        $data = MasterWarehouse::with('blocks')->where('status',1)->get();
        return $data;
    }
    public function getBlocks($warehouseId)
    {
        $warehouse = MasterWarehouse::with('blocks')
            ->where('status', 1)
            ->findOrFail($warehouseId);

        return $warehouse;
    }

}