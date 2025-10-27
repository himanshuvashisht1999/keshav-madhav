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

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create main order
            $save_data = new Order;
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
            foreach ($request->product_sku as $key => $single_data) {
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

                        // Handle stock deduction
                        // $remaining_meter = $total_meter;
                        // $available_stocks = Stock::where('sku', $fabric_sku)
                        //     ->where('meter', '>', 0)
                        //     ->orderBy('id', 'asc')
                        //     ->get();

                        // foreach ($available_stocks as $stock_item) {
                        //     if ($remaining_meter <= 0) break;

                        //     $used_meter = min($remaining_meter, $stock_item->meter);

                        //     // Save detail stock mapping
                        //     $save_order_product_detail_stock = new OrderProductDetailStock;
                        //     $save_order_product_detail_stock->order_product_id = $save_order_product->id;
                        //     $save_order_product_detail_stock->order_product_detail_id = $save_order_detail->id;
                        //     $save_order_product_detail_stock->fabric_stock_id = $stock_item->id;
                        //     $save_order_product_detail_stock->meter = $used_meter;
                        //     $save_order_product_detail_stock->save();

                        //     // Update stock roll
                        //     $stock_item->meter -= $used_meter;
                        //     $stock_item->save();

                        //     $remaining_meter -= $used_meter;
                        // }

                        // If still not enough stock
                        // if ($remaining_meter > 0) {
                        //     throw new \Exception("Insufficient stock for fabric: {$fabric_sku}. Short by {$remaining_meter} meters.");
                        // }
                    }

                    //// save order product stages
                    
                    $product_stages = ProductStage::where('master_product_id',$product_data->id)->orderBy('id','asc')->where('status',1)->get();
                    foreach($product_stages as $key=>$single_stage){
                        $save_order_product_stage = new OrderProductStage;
                        $save_order_product_stage->order_product_id = $save_order_product->id;
                        $save_order_product_stage->stage_id = $single_stage->master_stage_id;
                        $save_order_product_stage->sequence = $key+1;
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


                    }
                    

                }
            }

            // ✅ Commit everything if all successful
            DB::commit();
            return $return_data;

        } catch (\Exception $e) {
            // ❌ Rollback everything on any error
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

            // Get current stage
            $currentStage = OrderProductStage::where('order_product_id', $order_product_id)
                                ->where('stage_id', $from_stage_id)
                                ->firstOrFail();

            if ($quantity > $currentStage->pending_qty) {
                throw new \Exception("Quantity exceeds pending quantity of this stage.");
            }

            // Get next stage
            $nextStage = OrderProductStage::where('order_product_id', $order_product_id)
                            ->where('sequence', '>', $currentStage->sequence)
                            ->orderBy('sequence', 'asc')
                            ->first();

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
            }

            // 3️⃣ Record transaction
            OrderStageTransaction::create([
                'order_product_id' => $order_product_id,
                'from_stage_id' => $from_stage_id,
                'to_stage_id' => $nextStage->stage_id ?? null,
                'quantity' => $quantity,
                'processed_by' => $user_id,
                'remarks' => $remarks,
                'status' => 1, // Transaction completed
                'remaining_quantity' => $quantity,
            ]);

            $total_quantity = $quantity;

            $order_stage_transction_data_update = OrderStageTransaction::where('order_product_id', $order_product_id)
            ->where('to_stage_id', $from_stage_id)->where('remaining_quantity','>',0)->get();
            foreach($order_stage_transction_data_update as $single_data){
                if($total_quantity >= $single_data->remaining_quantity){
                    $save_order_transaction = OrderStageTransaction::where('id', $single_data->id)->first();
                    $save_order_transaction->remaining_quantity = 0;
                    $save_order_transaction->save();
                }else{
                    $save_order_transaction = OrderStageTransaction::where('id', $single_data->id)->first();
                    $save_order_transaction->remaining_quantity = $single_data->remaining_quantity - $total_quantity;
                    $save_order_transaction->save();

                    break;
                }
            }
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
            }
            $currentStage = OrderProductStage::where('order_product_id', $orderProduct->id)->orderBy('id','asc')->first();
            
            $dataaa = OrderStageTransaction::create([
                'order_product_id' => $orderProduct->id,
                'from_stage_id' => 0,
                'to_stage_id' => $currentStage->stage_id ?? null,
                'quantity' => $orderProduct->quantity,
                'processed_by' => '0',
                'remarks' =>'first stage',
                'remaining_quantity' => $orderProduct->quantity,
                'status' => 1, // Transaction completed
            ]);
            DB::commit();
            $data['status'] = 1;
            $data['message'] = 'Fabric issued successfully.';
            return $data;

        } catch (\Exception $e) {
            DB::rollBack();
            $data['status'] = 0;
            $data['message'] = 'Error issuing fabric: ' . $e->getMessage();
            return $data;
        }
    }


    

}