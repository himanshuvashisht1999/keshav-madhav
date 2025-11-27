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


use App\Http\DataTable\Admin\ProductOrderDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class ProductOrderService {
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

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $save_data_main = new OrderMain;
            $save_data_main->sku = '';
            $save_data_main->expected_delivery_date = $request->expected_delivery_date;
            $save_data_main->master_customer_id = $request->master_customer_id;
            $save_data_main->status = 1;
            $save_data_main->save();
            $customer_data = MasterCustomer::where('id',$request->master_customer_id)->first();
            $firstThree = strtoupper(substr($customer_data->name, 0, 3));

            $save_data_main->sku = $firstThree . "/". date('m/Y').'/' . $save_data_main->id;
            $save_data_main->save();


            // Create main order
            foreach ($request->product_sku as $key => $single_data) {
                $save_data = new Order;
                $save_data->order_main_id = $save_data_main->id;
                $save_data->sku = '';
                $save_data->expected_delivery_date = $request->expected_delivery_date;
                $save_data->master_customer_id = $request->master_customer_id;
                $save_data->status = 1;
                $save_data->save();
                $customer_data = MasterCustomer::where('id',$request->master_customer_id)->first();
                $firstThree = strtoupper(substr($customer_data->name, 0, 3));
                
                // Update SKU after save
                // $save_data->sku = 'Production-' . $save_data->id;
                $save_data->sku = $firstThree . "/". date('m/Y').'/' . $save_data->id;
                $save_data->save();

                // Default success response
                $return_data['message'] = 'The sales order has been successfully created.';
                $return_data['status_code'] = 1;

                // Loop through ordered products
            
                $product_data = ProductionGoods::where('sku', $single_data)->first();

                if ($product_data) {
                    $order_quantity = $request->product_quantity[$key];

                    // Save order product
                    $save_order_product = new OrderProduct;
                    $save_order_product->order_id = $save_data->id;
                    $save_order_product->product_sku = $single_data;
                    $save_order_product->quantity = $order_quantity;
                    $save_order_product->save();

                    // Loop through BOM for this product
                    foreach ($product_data->bill_of_materials as $single_detail) {
                        $fabric_sku = $single_detail->fabric_sku;
                        $fabric_meter = $single_detail->meter;
                        $total_meter = $fabric_meter * $order_quantity;

                        // Save product detail
                        $save_order_detail = new OrderProductDetail;
                        $save_order_detail->order_product_id = $save_order_product->id;
                        $save_order_detail->product_sku = $product_data->sku;
                        $save_order_detail->fabric_sku = $fabric_sku;
                        $save_order_detail->meter = $fabric_meter;
                        $save_order_detail->order_quantity = $order_quantity;
                        $save_order_detail->total_meter = $total_meter;
                        $save_order_detail->save();


                    }

                    //// save order product stages
                    
                    $product_stages = ProductStage::where('master_product_id',$product_data->id)->orderBy('id','asc')->where('status',1)->get();
                    $sequence_value = 1;
                    foreach($product_stages as $key=>$single_stage){
                        $save_order_product_stage = new OrderProductStage;
                        $save_order_product_stage->order_product_id = $save_order_product->id;
                        $save_order_product_stage->stage_id = $single_stage->master_stage_id;
                        $save_order_product_stage->sequence = $sequence_value;
                        if($key == 0){
                            $save_order_product_stage->total_qty = $order_quantity;
                            $save_order_product_stage->completed_qty = 0;
                            $save_order_product_stage->pending_qty = $order_quantity;
                            $save_order_product_stage->status = 1;  // 1: In progress
                        }else{
                            $save_order_product_stage->total_qty = 0;
                            $save_order_product_stage->completed_qty = 0;
                            $save_order_product_stage->pending_qty = 0;
                            $save_order_product_stage->status = 0;  // 0-pending
                        }
                        $save_order_product_stage->save();  
                        $sequence_value++;

                        // if($single_stage->master_stage_id == $product_data->printing_stage_after){
                        //     $save_order_product_stage = new OrderProductStage;
                        //     $save_order_product_stage->order_product_id = $save_order_product->id;
                        //     $save_order_product_stage->stage_id = 1;
                        //     $save_order_product_stage->sequence = $sequence_value;
                        //     $save_order_product_stage->total_qty = 0;
                        //     $save_order_product_stage->completed_qty = 0;
                        //     $save_order_product_stage->pending_qty = 0;
                        //     $save_order_product_stage->status = 0;
                        //     $save_order_product_stage->save();
                        //     $sequence_value++;
                        // }
                        // if($single_stage->master_stage_id == $product_data->embroidery_stage_after){
                        //     $save_order_product_stage = new OrderProductStage;
                        //     $save_order_product_stage->order_product_id = $save_order_product->id;
                        //     $save_order_product_stage->stage_id = 2;
                        //     $save_order_product_stage->sequence = $sequence_value;
                        //     $save_order_product_stage->total_qty = 0;
                        //     $save_order_product_stage->completed_qty = 0;
                        //     $save_order_product_stage->pending_qty = 0;
                        //     $save_order_product_stage->status = 0;
                        //     $save_order_product_stage->save();
                        //     $sequence_value++;
                        // }

                    }

                    $product_items = ProductionGoodsItem::where('product_id',$product_data->id)->orderBy('id','asc')->where('status',1)->get();
                    foreach($product_items as $key=>$single_item){
                        $total_qty = $single_item->quantity * $order_quantity;
                        $save_order_product_items = new OrderProductItem;
                        $save_order_product_items->order_product_id = $save_order_product->id;
                        $save_order_product_items->item_sku = $single_item->item_attribute_value_sku;
                        $save_order_product_items->quantity = $single_item->quantity;
                        $save_order_product_items->order_quantity = $order_quantity;
                        $save_order_product_items->total_item_quantity = $total_qty;
                        $save_order_product_items->pending_quantity = $total_qty;
                        $save_order_product_items->status = 0;
                        $save_order_product_items->save();

                    }

                }
            }

            // Commit everything if all successful
            DB::commit();
            return $return_data;

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function view(Request $request){
        $data = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        return $data;
    }
    public function produce(Request $request){
        $data = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        return $data;
    }
    public function issueFabric(Request $request){
        $data = OrderProduct::with('product_details.fabric_stocks','order_stages','first_stage')->where('id',$request->id)->first();
        return $data;
    }

    public function edit(Request $request){
        $data = Order::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = Order::find($request->id);
        $update_data->order_type = $request->order_type;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    public function delete(Request $request){
        $data = Order::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function products(){
        $data = ProductionGoods::where('status',1)->get();
        return $data;
    }
    public function transfer($request)
    {
        DB::beginTransaction();

        try {

            $order_product_id = $request->order_product_id;
            $from_stage_id = $request->from_stage_id;
            $quantity = $request->quantity;
            $remarks = $request->remarks ?? null;
            $user_id = auth()->id() ?? 0;

            $order_stage_transction_data_update = OrderStageTransaction::where('id', $request->order_transaction_id)->first();
            if($order_stage_transction_data_update->remaining_quantity < $quantity){
                throw new \Exception("Quantity exceeds pending quantity of this stage.");
            }

            $currentStage = getCurrentStage($order_product_id,$from_stage_id);
            if ($quantity > $currentStage->pending_qty) {
                throw new \Exception("Quantity exceeds pending quantity of this stage.");
            }

            // Get next stage
            // if($from_stage_id == 0 || $from_stage_id == 1 || $from_stage_id == 2){
            if( $from_stage_id == 1 || $from_stage_id == 2){
                $nextStage = '';
            }else{
                $nextStage = getNextStage($order_product_id,$currentStage->sequence);
            }



            // 1️⃣ Update current stage
            $currentStage->completed_qty += $quantity;
            $currentStage->pending_qty -= $quantity;
            $currentStage->status = $currentStage->pending_qty == 0 ? 2 : 1; // Complete or In Progress
            $currentStage->save();

            // 2️⃣ Update next stage (if exists)
            if ($nextStage) {
                $nextStage->total_qty += $quantity;
                $nextStage->pending_qty += $quantity;
                $nextStage->status = $nextStage->status == 0 ? 1 : $nextStage->status; // Pending → In Progress
                $nextStage->save();
            

                $orderProduct = OrderProduct::with('order')->where('id',$order_product_id)->first();
                $stage_sku = $nextStage->stage->sku;
                $orderProducts = OrderProduct::where('order_id', $orderProduct->order->id)
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();
                $order_product_number = array_search($orderProduct->id, $orderProducts) + 1;
                $stageCount = OrderStageTransaction::where('order_product_id', $orderProduct->id)
                    ->where('to_stage_id', $nextStage->stage_id)
                    ->count() + 1;
                $sku_for_trans = "{$orderProduct->order->sku}/{$order_product_number}/{$stage_sku}/{$stageCount}";
                
                $cuttingStage = getFirstStage($order_product_id);
                $cuttingStageId = $cuttingStage->master_stage_id ?? 1;

                $isExistLotNO = OrderStageTransaction::where('order_product_id', $order_product_id)
                    ->where('lot_no', $request->lot_no)
                    ->exists();

                if ($cuttingStageId == $from_stage_id && $isExistLotNO) {
                    throw new \Exception(" This Lot no {$request->lot_no} is already exist");
                }
                // 3️⃣ Record transaction
                $OrderStageTransaction = OrderStageTransaction::create([
                    'sku' => $sku_for_trans,
                    'order_product_id' => $order_product_id,
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => $nextStage->stage_id ?? null,
                    'quantity' => $quantity,
                    'processed_by' => $user_id,
                    'remarks' => $remarks,
                    'status' => 1, // Transaction completed
                    'remaining_quantity' => $quantity,
                    'lot_no' => $request->lot_no,
                    'sub_stage_id' => $request->sub_stage,
                ]);
                $OrderStageTransaction_id = $OrderStageTransaction->id;

                // entry order_product_item_transactions  
                if (!empty($request->items)){
                    foreach($request->items as $item_sku => $item_qty){
                        $save_item_data = new OrderProductItemTransaction;
                        $save_item_data->order_stage_transaction_id = $OrderStageTransaction_id;
                        $save_item_data->item_sku = $item_sku;
                        $save_item_data->quantiy = $item_qty;
                        $save_item_data->save();

                        /// update OrderProductItem 
                        $orderProductItem = OrderProductItem::where('item_sku', $item_sku)
                            ->where('order_product_id', $order_product_id)
                            ->first();  

                        if ($orderProductItem) {
                            $orderProductItem->pending_quantity -= $item_qty;
                            $orderProductItem->status = $orderProductItem->pending_quantity == 0 ? 1 : 0;
                            $orderProductItem->save();
                            // // update stock items
                            $useItems = $item_qty;
                            $totalQty = ItemStock::where('sku', $item_sku)->where('status', 1)->sum('quantity');
                            if ($totalQty < $useItems ) {
                                throw new \Exception("Insufficient stock available for this item.");
                            }
                            $rows = ItemStock::where('sku', $item_sku)->where('status', 1)->orderBy('id', 'ASC')->get();

                            foreach ($rows as $row) {

                                $currentQty = $row->quantity;

                                if ($currentQty <= $useItems) {
                                    $row->quantity = 0;
                                    $row->save();
                                    $useItems -= $currentQty;

                                } else {
                                    // Reduce only required amount
                                    $row->quantity = $currentQty - $useItems;
                                    $row->save();
                                    // All done
                                    $useItems = 0;
                                }
                            }
                        }
                    }
                }

            }else if($from_stage_id == 1 || $from_stage_id == 2){

                
            }else{
                $orderProduct = OrderProduct::where('id',$order_product_id)->first();
                if($currentStage->completed_qty == $orderProduct->quantity){
                    $orderProduct->status = 3;
                    $orderProduct->save();
                    $order_data = Order::where('id',$orderProduct->id)->update([
                        'status' => 3,
                    ]);

                }
                
            }

            // if($from_stage_id != 1 && $from_stage_id != 2){
                $total_quantity = $quantity;

                $save_order_transaction = OrderStageTransaction::where('id', $request->order_transaction_id)->first();
                $save_order_transaction->remaining_quantity = $save_order_transaction->remaining_quantity - $total_quantity;
                $save_order_transaction->save();

            // }

            

            //// code for printing and emproidary

            $orderProduct = OrderProduct::where('id',$order_product_id)->first();
            $product_data = ProductionGoods::where('sku',$orderProduct->product_sku)->first();
            if($product_data && $from_stage_id == $product_data->printing_stage_after){

                $sku_for_printing = "{$orderProduct->order->sku}/{$order_product_number}/PRINTING/{$stageCount}";
                $OrderStageTransaction = OrderStageTransaction::create([
                    'sku' => $sku_for_printing,
                    'order_product_id' => $order_product_id,
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => 1,
                    'quantity' => $quantity,
                    'processed_by' => $user_id,
                    'remarks' => $remarks,
                    'status' => 1, // Transaction completed
                    'remaining_quantity' => $quantity,
                    'lot_no' => $request->lot_no,
                    'sub_stage_id' => 0,
                ]);

                $order_product_stage_update = OrderProductStage::where('order_product_id',$order_product_id)->where('stage_id',1)->update([
                    'total_qty' => $quantity,
                    'pending_qty' => $quantity
                ]);
            }

            if($product_data && $from_stage_id == $product_data->embroidery_stage_after){

                $sku_for_embroidery= "{$orderProduct->order->sku}/{$order_product_number}/EMBROIDERY/{$stageCount}";
                $OrderStageTransaction = OrderStageTransaction::create([
                    'sku' => $sku_for_embroidery,
                    'order_product_id' => $order_product_id,
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => 2,
                    'quantity' => $quantity,
                    'processed_by' => $user_id,
                    'remarks' => $remarks,
                    'status' => 1, // Transaction completed
                    'remaining_quantity' => $quantity,
                    'lot_no' => $request->lot_no,
                    'sub_stage_id' => 0,
                ]);

                $order_product_stage_update = OrderProductStage::where('order_product_id',$order_product_id)->where('stage_id',2)->update([
                    'total_qty' => $quantity,
                    'pending_qty' => $quantity
                ]);
            }

            ///// end code 

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function customers(){
        $data = MasterCustomer::where('status',1)->get();
        return $data;
    }

    public function issueFabricPost(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Fetch main order product
            $orderProduct = OrderProduct::find($request->order_product_id);
            if (!$orderProduct) {
                return back()->with('error', 'Order not found.');
            }

            // 2️⃣ Loop through each product detail (fabric type)
            foreach ($request->order_product_detail_ids as $index => $detailId) {
                $orderProductDetail = OrderProductDetail::find($detailId);
                if (!$orderProductDetail) {
                    
                    throw new \Exception("Order product detail not found (ID: {$detailId})");
                }

                // Get corresponding roll & meter arrays for this detail
                $rolls = $request->fabric_roll[$index] ?? [];
                $meters = $request->meter[$index] ?? [];

                if (count($rolls) !== count($meters)) {
                    throw new \Exception("Mismatch between roll and meter inputs for fabric detail ID: {$detailId}");
                }
                

                $totalIssued = 0;

                // 3️⃣ Loop through each roll issued for this fabric
                foreach ($rolls as $key => $fabricStockId) {
                    $usedMeter = floatval($meters[$key]);

                    if (!$fabricStockId || $usedMeter <= 0) continue;

                    // Fetch stock record
                    $stock = Stock::find($fabricStockId);
                    if (!$stock) {
                        throw new \Exception("Stock not found for Fabric Stock ID: {$fabricStockId}");
                    }

                    if ($usedMeter > $stock->meter) {
                        throw new \Exception("Not enough stock for Roll #{$stock->unique_number}. Trying to issue {$usedMeter}m but only {$stock->meter}m available.");
                    }
                    

                    // 🔹 Save issue record
                    $issuedStock = new OrderProductDetailStock;
                    $issuedStock->order_product_id = $orderProduct->id;
                    $issuedStock->order_product_detail_id = $orderProductDetail->id;
                    $issuedStock->fabric_stock_id = $stock->id;
                    $issuedStock->meter = $usedMeter;
                    $issuedStock->save();

                    // 🔹 Update stock balance
                    $stock->meter -= $usedMeter;
                    $stock->save();

                    $totalIssued += $usedMeter;
                }
                

                // 4️⃣ Validate that issued = required
                if (round($totalIssued, 2) != round($orderProductDetail->total_meter, 2)) {
                    throw new \Exception("Issued total meter ({$totalIssued}) does not match required ({$orderProductDetail->total_meter}) for fabric {$orderProductDetail->fabric_sku}");
                }
                OrderProduct::where('id',$orderProduct->id)->update([
                    'status' => 2
                ]);
                $order_data = Order::where('id',$orderProduct->id)->update([
                    'status' => 2,
                ]);
            }
            $currentStage = OrderProductStage::where('order_product_id', $orderProduct->id)->orderBy('id','asc')->first();

            $orderProduct = OrderProduct::with('order')->where('id',$request->order_product_id)->first();
            $stage_sku = $currentStage->stage->sku;

           $orderProducts = OrderProduct::where('order_id', $orderProduct->order->id)
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();
            $order_product_number = array_search($orderProduct->id, $orderProducts) + 1;

            $stageCount = OrderStageTransaction::where('order_product_id', $orderProduct->id)
                ->where('to_stage_id', $currentStage->stage_id)
                ->count() + 1;

            $sku_for_trans = "{$orderProduct->order->sku}/{$order_product_number}/{$stage_sku}/{$stageCount}";
            
            $dataaa = OrderStageTransaction::create([
                'sku' => $sku_for_trans,
                'order_product_id' => $orderProduct->id,
                'from_stage_id' => 0,
                'to_stage_id' => $currentStage->stage_id ?? null,
                'sub_stage_id' => $request->sub_stage_id ?? null,
                'quantity' => $orderProduct->quantity,
                'processed_by' => '0',
                'remarks' =>'first stage',
                'remaining_quantity' => $orderProduct->quantity,
                'status' => 1, // Transaction completed
            ]);
            DB::commit();
            $data['status'] = 1;
            $data['message'] = 'Fabric issued successfully.';
            $data['order_id'] = $orderProduct->order_id;
            return $data;

        } catch (\Exception $e) {
            DB::rollBack();
            $data['status'] = 0;
            $data['message'] = 'Error issuing fabric: ' . $e->getMessage();
            $data['order_id'] = $orderProduct->order_id;
            return $data;
        }
    }

    public function productStatusHoverData(Request $request){
        $data_obj = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        // $data = json_decode(json_encode($data)); 
        $data_obj = $data_obj ? $data_obj->toArray() : [];
        $products = $data_obj['products'] ?? [];
        $data = [];
        
        foreach ($products as $product_data) {
            foreach ($product_data['order_stages'] as $order_stages) {
                $stage_name = $order_stages['stage']['name'] ?? '';
                $data[$order_stages['stage']['id']] = [
                    'name' => $stage_name,
                    'total_qty' => $order_stages['total_qty'],
                    'completed_qty' => $order_stages['completed_qty'],
                    'pending_qty' => $order_stages['pending_qty'],
                    'status' => $order_stages['status'],
                ];
            }
        }  
        return response()->json($data);
    }

    public function sub_stages_cutting(){
        $data = getCuttingSubStages();
        return $data;
    }
    

}