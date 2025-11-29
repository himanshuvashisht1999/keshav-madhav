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
        $data = OrderMain::with('orders.product')->where('id',$order_id)->first();
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
            $boxCapacity   = (int) $request->quantity; // how many items per box

            if ($boxCapacity <= 0) {
                return [
                    'status'  => 0,
                    'message' => 'Quantity per box must be greater than 0.',
                ];
            }

            // 1. Get order product IDs
            $order_product_ids = OrderProduct::where('product_type_sku', $request->product_type_sku)
                ->where('order_main_id', $order_main_id)
                ->pluck('id');
            
            if ($order_product_ids->count() == 0) {
                return [
                    'status'  => 0,
                    'message' => 'No Product Found.',
                ];
            }

            // 2. Get warehouse rows (available stock)
            $warehouse_data = WarehouseDetail::whereIn('order_product_id', $order_product_ids)
                ->where('status', 1)
                ->orderBy('id')
                ->lockForUpdate() // lock rows so quantity is safe in concurrent requests
                ->get();

            if ($warehouse_data->isEmpty()) {
                return [
                    'status'  => 0,
                    'message' => 'No Warehouse Product Found.',
                ];
            }

            // 3. Total quantity in warehouse
            $totalQty = $warehouse_data->sum('quantity'); // e.g. 100

            if ($totalQty <= 0) {
                return [
                    'status'  => 0,
                    'message' => 'Total warehouse quantity is zero.',
                ];
            }

            // 4. How many boxes?
            $numBoxes = (int) ceil($totalQty / $boxCapacity);


            ///////// save dta in package
            $save_package = new Package;
            $save_package->order_main_id    = $order_main_id;
            $save_package->product_type_sku = $request->product_type_sku;
            $save_package->quantity         = $request->quantity;
            $save_package->description      = $request->description;
            $save_package->save();

            // Track per-warehouse remaining qty (in memory)
            $warehouseItems = $warehouse_data->map(function ($item) {
                $item->original_qty  = $item->quantity;
                $item->remaining_qty = $item->quantity;
                return $item;
            });

            $remainingTotal = $totalQty;
            $createdBoxes   = 0;

            // 5. Create boxes + box items
            while ($remainingTotal > 0) {

                $qtyInThisBox = min($boxCapacity, $remainingTotal);

                // Create PackageBox
                $packageBox = new PackageBox;
                $packageBox->package_id    = $save_package->id;
                $packageBox->order_main_id    = $order_main_id;
                $packageBox->product_type_sku = $request->product_type_sku;
                $packageBox->quantity         = $qtyInThisBox; // how many items inside this box
                $packageBox->description      = $request->description;
                $packageBox->save();

                $createdBoxes++;

                // Fill this box with items
                for ($i = 0; $i < $qtyInThisBox; $i++) {

                    // Get next warehouse row with remaining stock
                    $usedWarehouse = null;

                    foreach ($warehouseItems as $wItem) {
                        if ($wItem->remaining_qty > 0) {
                            $usedWarehouse = $wItem;
                            break;
                        }
                    }

                    if (!$usedWarehouse) {
                        // Safety: should not happen if totalQty was correct
                        break 2; // break outer while loop too
                    }

                    // Create PackageBoxItem
                    $boxItem = new PackageBoxItem;
                    $boxItem->package_box_id      = $packageBox->id;
                    $boxItem->product_sku         = $usedWarehouse->orderProduct->product_sku;
                    $boxItem->save();

                    $orderProduct = OrderProduct::find($usedWarehouse->order_product_id);
                    if ($orderProduct) {
                        $orderProduct->completed_quantity += 1;  // increase by 1 per packaging item
                        $orderProduct->save();
                    }

                    $usedWarehouse->remaining_qty--;
                    $remainingTotal--;
                }
            }

            // 6. Update WarehouseDetail rows with new quantities
            foreach ($warehouseItems as $wItem) {
                $consumed = $wItem->original_qty - $wItem->remaining_qty;

                if ($consumed > 0) {
                    // Set new quantity
                    $wItem->quantity = $wItem->remaining_qty;

                    // If quantity goes to 0, mark as inactive
                    if ($wItem->quantity <= 0) {
                        $wItem->status = 0;
                    }

                    $wItem->save();
                }
            }
            ///////////// order main status update
            $status_update = OrderMain::where('id', $order_main_id)->update([
                'status' => 2,
            ]);

            return [
                'status'        => 1,
                'message'       => 'Packages created and warehouse updated successfully.',
                'total_boxes'   => $createdBoxes,
                'total_items'   => $totalQty,
                'box_capacity'  => $boxCapacity,
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

}