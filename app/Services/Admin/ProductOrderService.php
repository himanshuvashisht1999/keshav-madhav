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
use App\Models\OrderProductDesign;
use App\Models\WarehouseDetail;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterColor;
use App\Models\MasterFabricWarehouse;
use App\Models\OrderProductSet;
use App\Models\OrderCuttingStage;
use App\Models\MasterProductFitting;
use App\Models\StageMasterUnit;
use App\Models\MasterDesignPattern;
use App\Models\Fabric;
use App\Models\OrderProductSetDetail;
use App\Models\OrderLot;

use PDF;


use App\Http\DataTable\Admin\ProductOrderDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class ProductOrderService
{
    public function __construct(
        DataTable $datatable,
        Order $order
        )
    {
        $this->datatable = $datatable;
        $this->order = $order;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {

        return $this->datatable->indexList($request);
    }
    public function indexListOrder(Request $request)
    {

        return $this->datatable->indexListOrder($request);
    }
    public function indexListOrderSet(Request $request)
    {

        return $this->datatable->indexListOrderSet($request);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        try {

            ////// corporate order photo upload
            if ($request->file('corporate_order_file')) {
                $image = $request->file('corporate_order_file');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "corporate_order_file-" . rand() . "_" . time() . "." . $extImage;
                $destinationPath = public_path() . '/assets/products';
                $image->move($destinationPath, $imgName);
            }

            $save_data_main = new OrderMain;
            $save_data_main->sku = '';
            $save_data_main->order_type = $request->order_type ?? 'domestic';
            $save_data_main->expected_delivery_date = $request->expected_delivery_date;
            $save_data_main->po_number = $request->po_number;
            $save_data_main->po_date = $request->po_date;
            $save_data_main->master_customer_id = $request->master_customer_id;
            $save_data_main->corporate_order_file = $imgName ?? null;
            $save_data_main->status = 1;
            $save_data_main->save();
            $customer_data = MasterCustomer::where('id', $request->master_customer_id)->first();
            $firstThree = strtoupper(substr($customer_data->name, 0, 3));

            $save_data_main->sku = $firstThree . "/" . date('d/m/Y') . '/' . $save_data_main->id;
            $save_data_main->save();



            // Create main order
            $i = 0;
            foreach ($request->designList as $key => $design_id) {
                $i++;
                $order_quantity = $request->product_quantity[$key];

                $size_data = $this->getSizeDetails($request->sizeList[$key]);
                $size_explode = explode(',', $size_data->size_group);
                $product_data = ProductionGoods::where('id', $design_id)->first();
                if ($product_data) {
                    $save_orderProductSet = new OrderProductSet;
                    $save_orderProductSet->order_main_id = $save_data_main->id;
                    $save_orderProductSet->sku = $save_data_main->sku . '/' . $i;
                    $save_orderProductSet->bar_code = $request->bar_codeList[$key] ?? null;
                    $save_orderProductSet->design_number = $product_data->design_number;
                    $save_orderProductSet->production_goods_id = $product_data->id;
                    $save_orderProductSet->set_size = $request->sizeList[$key];
                    $save_orderProductSet->color_id = $request->colourList[$key];
                    $save_orderProductSet->set_quantity = $order_quantity;
                    $save_orderProductSet->no_of_pcs = $size_data->no_of_pcs;
                    $save_orderProductSet->total_quantity = $order_quantity * $size_data->no_of_pcs;
                    $save_orderProductSet->remain_set_quantity = $order_quantity;
                    $save_orderProductSet->remain_total_quantity = $order_quantity * $size_data->no_of_pcs;
                    $save_orderProductSet->status = 1;
                    $save_orderProductSet->save();

                    $sizeCounts = array_count_values($size_explode);
                    foreach ($sizeCounts as $size => $count) {

                        $totalQty = $count * $order_quantity;

                        $save_orderProductSetDetail = new OrderProductSetDetail();
                        $save_orderProductSetDetail->order_products_set_id = $save_orderProductSet->id;
                        $save_orderProductSetDetail->sku = '';
                        $save_orderProductSetDetail->size = $size;
                        $save_orderProductSetDetail->total_quantity = $totalQty;
                        $save_orderProductSetDetail->remaining_quantity = $totalQty;
                        $save_orderProductSetDetail->remaining_lot_allocated = $totalQty;
                        $save_orderProductSetDetail->status = 1;
                        $save_orderProductSetDetail->save();
                    }
                }

            }

            // Commit everything if all successful
            DB::commit();
            $return_data['message'] = 'The sales order has been successfully created.';
            $return_data['status_code'] = 1;
            return $return_data;

        }
        catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();
            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }



    public function view(Request $request)
    {
        $data = Order::with('products.product_details.product_detail_stocks', 'products.order_stages.stage', 'products.order_stage_trnsactions')->where('id', $request->id)->first();
        return $data;
    }
    public function produce(Request $request)
    {
        $data = Order::with('products.product_details.product_detail_stocks', 'products.order_stages.stage', 'products.order_stage_trnsactions')->where('id', $request->id)->first();
        return $data;
    }
    public function issueFabric(Request $request)
    {
        $data = OrderProduct::with('product_details.fabric_stocks', 'order_stages', 'first_stage')->where('id', $request->id)->first();
        return $data;
    }

    public function edit(Request $request)
    {
        $data = Order::where('id', $request->id)->first();
        return $data;
    }
    public function update(Request $request)
    {
        $update_data = Order::find($request->id);
        $update_data->order_type = $request->order_type;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    public function delete(Request $request)
    {
        $data = Order::where('id', $request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function deleteOrderMain(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id;

            // Check if any lots exist for this order
            $lotExists = OrderLot::where('order_main_id', $id)->exists();
            if ($lotExists) {
                return [
                    'status_code' => 0,
                    'message' => 'Cannot delete order because it has associated production lots.'
                ];
            }

            $setIds = OrderProductSet::where('order_main_id', $id)->pluck('id');
            
            // Delete related records
            OrderProductSetDetail::whereIn('order_products_set_id', $setIds)->delete();
            OrderProductSet::where('order_main_id', $id)->delete();
            OrderCuttingStage::where('order_main_id', $id)->delete();
            OrderProduct::where('order_main_id', $id)->delete();
            Order::where('order_main_id', $id)->delete();
            OrderMain::where('id', $id)->delete();

            DB::commit();
            return [
                'status_code' => 1,
                'message' => 'The sales order has been successfully deleted.'
            ];
        }
        catch (\Exception $e) {
            DB::rollBack();
            return [
                'status_code' => 0,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ];
        }
    }
    public function products()
    {
        $data = ProductionGoods::with('series')->where('status', 1)->orderBy('id', 'desc')->get();
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
            if ($order_stage_transction_data_update->remaining_quantity < $quantity) {
                throw new \Exception("Quantity exceeds pending quantity of this stage.");
            }

            $currentStage = getCurrentStage($order_product_id, $from_stage_id);
            if ($quantity > $currentStage->pending_qty) {
                throw new \Exception("Quantity exceeds pending quantity of this stage.");
            }

            // Get next stage
            // if($from_stage_id == 0 || $from_stage_id == 1 || $from_stage_id == 2){
            if ($from_stage_id == 1 || $from_stage_id == 2) {
                $nextStage = '';
            }
            else {
                $nextStage = getNextStage($order_product_id, $currentStage->sequence);
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


                $orderProduct = OrderProduct::with('order')->where('id', $order_product_id)->first();
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

                // if ($cuttingStageId == $from_stage_id && $isExistLotNO) {
                //     throw new \Exception(" This Lot no {$request->lot_no} is already exist");
                // }
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
                if (!empty($request->items)) {
                    foreach ($request->items as $item_sku => $item_qty) {
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
                            if ($totalQty < $useItems) {
                                throw new \Exception("Insufficient stock available for this item.");
                            }
                            $rows = ItemStock::where('sku', $item_sku)->where('status', 1)->orderBy('id', 'ASC')->get();

                            foreach ($rows as $row) {

                                $currentQty = $row->quantity;

                                if ($currentQty <= $useItems) {
                                    $row->quantity = 0;
                                    $row->save();
                                    $useItems -= $currentQty;

                                }
                                else {
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

            }
            else if ($from_stage_id == 1 || $from_stage_id == 2) {


            }
            else {
                $orderProduct = OrderProduct::where('id', $order_product_id)->first();
                if ($currentStage->completed_qty == $orderProduct->quantity) {
                    $orderProduct->status = 3;
                    $orderProduct->save();
                    $order_data = Order::where('id', $orderProduct->id)->update([
                        'status' => 3,
                    ]);

                }

                $save_warehouse_data = new WarehouseDetail;
                $save_warehouse_data->sku = $orderProduct->order->sku;
                $save_warehouse_data->order_product_id = $order_product_id;
                $save_warehouse_data->from_stage_id = $from_stage_id;
                $save_warehouse_data->master_warehouse_block_id = $request->sub_stage;
                $save_warehouse_data->lot_no = $request->lot_no;
                $save_warehouse_data->original_qty = $quantity;
                $save_warehouse_data->remaining_qty = $quantity;
                $save_warehouse_data->remarks = $remarks;
                $save_warehouse_data->status = 1;
                $save_warehouse_data->save();

            }

            // if($from_stage_id != 1 && $from_stage_id != 2){
            $total_quantity = $quantity;

            $save_order_transaction = OrderStageTransaction::where('id', $request->order_transaction_id)->first();
            $save_order_transaction->remaining_quantity = $save_order_transaction->remaining_quantity - $total_quantity;
            $save_order_transaction->save();

            // }



            //// code for printing and emproidary

            $orderProduct = OrderProduct::where('id', $order_product_id)->first();
            $product_data = ProductionGoods::where('sku', $orderProduct->product_sku)->first();
            if ($product_data && $from_stage_id == $product_data->printing_stage_after) {

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

                $order_product_stage_update = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', 1)->update([
                    'total_qty' => $quantity,
                    'pending_qty' => $quantity
                ]);
            }

            if ($product_data && $from_stage_id == $product_data->embroidery_stage_after) {

                $sku_for_embroidery = "{$orderProduct->order->sku}/{$order_product_number}/EMBROIDERY/{$stageCount}";
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

                $order_product_stage_update = OrderProductStage::where('order_product_id', $order_product_id)->where('stage_id', 2)->update([
                    'total_qty' => $quantity,
                    'pending_qty' => $quantity
                ]);
            }

            ///// end code 

            DB::commit();
            return true;

        }
        catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function customers()
    {
        $data = MasterCustomer::where('status', 1)
            ->where('type', 'corporate')
            ->orWhere('name', 'SnapKid DM')
            ->orderBy('name', 'asc')
            ->get();
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

                    if (!$fabricStockId || $usedMeter <= 0)
                        continue;

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
                OrderProduct::where('id', $orderProduct->id)->update([
                    'status' => 2
                ]);
                $order_data = Order::where('id', $orderProduct->id)->update([
                    'status' => 2,
                ]);
            }
            $currentStage = OrderProductStage::where('order_product_id', $orderProduct->id)->orderBy('id', 'asc')->first();

            $orderProduct = OrderProduct::with('order')->where('id', $request->order_product_id)->first();
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
                'remarks' => 'first stage',
                'remaining_quantity' => $orderProduct->quantity,
                'status' => 1, // Transaction completed
            ]);
            DB::commit();
            $data['status'] = 1;
            $data['message'] = 'Fabric issued successfully.';
            $data['order_id'] = $orderProduct->order_id;
            return $data;

        }
        catch (\Exception $e) {
            DB::rollBack();
            $data['status'] = 0;
            $data['message'] = 'Error issuing fabric: ' . $e->getMessage();
            $data['order_id'] = $orderProduct->order_id;
            return $data;
        }
    }

    public function productStatusHoverData(Request $request)
    {
        $data_obj = Order::with('products.product_details.product_detail_stocks', 'products.order_stages.stage', 'products.order_stage_trnsactions')->where('id', $request->id)->first();
        // $data = json_decode(json_encode($data)); 
        $data_obj = $data_obj ? $data_obj->toArray() : [];
        $products = $data_obj['products'] ?? [];
        $data = [];

        foreach ($products as $product_data) {
            foreach ($product_data['order_stages'] as $key => $order_stages) {
                $stage_name = $order_stages['stage']['name'] ?? '';
                $data[$key + 1] = [
                    'name' => $stage_name,
                    'total_qty' => $order_stages['total_qty'],
                    'completed_qty' => $order_stages['completed_qty'],
                    'pending_qty' => $order_stages['pending_qty'],
                    'status' => $order_stages['status'],
                    'stage_id' => $order_stages['stage_id'],
                ];
            }
        }
        return response()->json($data);
    }

    public function sub_stages_cutting()
    {
        $data = getCuttingSubStages();
        return $data;
    }

    function product_sizes()
    {
        $data = MasterSizeMeasurement::where('status', 1)->orderBy('id', 'asc')->get();
        return $data;
    }

    function getColours()
    {
        $data = MasterColor::where('status', 1)->orderBy('id', 'asc')->get();
        return $data;
    }

    function getCustomerSizes($request)
    {
        $design_id = $request->design_id;
        // $customer_id = $request->customer_id;
        $customer_id = 1;
        $data = MasterSizeMeasurement::where('design_number', $design_id)->where('status', 1)->orderBy('id', 'asc')->get();
        $data_res = [];
        foreach ($data as $size) {
            $data_res[$size->id] = $size->name . "&&" . $size->no_of_pcs . "&&" . $size->size_group;
        }
        return $data_res;
    }
    function getCustomerDesign($request)
    {
        // $customer_id = $request->customer_id;
        $customer_id = 1;
        // $data = MasterSizeMeasurement::where('corporate_company_id',$customer_id)->where('status',1)->orderBy('id','asc')->get();
        $data = ProductionGoods::where('status', 1)->orderBy('id', 'asc')->get();
        // dd($data);
        $data_res = [];
        foreach ($data as $design) {
            $data_res[$design->design_number] = $design->design_number;
        }
        return $data_res;

    }
    function getSizeDetails($size_set_id)
    {
        $customer_id = 1;
        $data = MasterSizeMeasurement::where('id', $size_set_id)->whereIn('status', [1, 2])->orderBy('id', 'asc')->first();
        return $data;
    }

    function orderMainDetails(Request $request)
    {
        $data = OrderMain::with('customer', 'OrderProductSets.product', 'OrderProductSets.colors', 'OrderProductSets.size_measurement')->where('id', $request->id)->first();
        return $data;
    }

    public function editOrderMain($id)
    {
        $data = OrderMain::with('OrderProductSets.product', 'OrderProductSets.colors', 'OrderProductSets.size_measurement')->where('id', $id)->first();
        return $data;
    }

    public function updateOrderMain(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order_main = OrderMain::find($id);
            if (!$order_main) {
                throw new \Exception("Order not found.");
            }

            ////// corporate order photo upload
            $imgName = $order_main->corporate_order_file;
            if ($request->file('corporate_order_file')) {
                $image = $request->file('corporate_order_file');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "corporate_order_file-" . rand() . "_" . time() . "." . $extImage;
                $destinationPath = public_path() . '/assets/products';
                $image->move($destinationPath, $imgName);
            }

            $order_main->order_type = $request->order_type ?? 'domestic';
            $order_main->expected_delivery_date = $request->expected_delivery_date;
            $order_main->po_number = $request->po_number;
            $order_main->po_date = $request->po_date;
            $order_main->master_customer_id = $request->master_customer_id;
            $order_main->corporate_order_file = $imgName;
            $order_main->save();

            // Check if any product has been assigned to cutting. If so, maybe restrict delete?
            // For now, let's just delete and recreate sets IF not assigned

            $existing_sets = OrderProductSet::where('order_main_id', $id)->get();
            foreach ($existing_sets as $set) {
                if (OrderCuttingStage::where('set_product_id', $set->id)->exists()) {
                // If assigned, we should probably keep it or update it?
                // This is complex. Let's assume user only edits before assignment for now.
                }
            }

            // Simple approach: Delete old sets (only if they aren't assigned/processed) 
            // and add new ones. 
            // OR: smarter sync.
            // Let's go with deleting unassigned sets.

            // Delete old sets and details
            OrderProductSetDetail::whereIn('order_products_set_id', $existing_sets->pluck('id'))->delete();
            OrderProductSet::where('order_main_id', $id)->delete();

            // Create new sets
            $i = 0;
            foreach ($request->designList as $key => $design_id) {
                $i++;
                $order_quantity = $request->product_quantity[$key];

                $size_data = $this->getSizeDetails($request->sizeList[$key]);
                $size_explode = explode(',', $size_data->size_group);
                $product_data = ProductionGoods::where('id', $design_id)->first();
                if ($product_data) {
                    $save_orderProductSet = new OrderProductSet;
                    $save_orderProductSet->order_main_id = $order_main->id;
                    $save_orderProductSet->sku = $order_main->sku . '/' . $i;
                    $save_orderProductSet->bar_code = $request->bar_codeList[$key] ?? null;
                    $save_orderProductSet->design_number = $product_data->design_number;
                    $save_orderProductSet->production_goods_id = $product_data->id;
                    $save_orderProductSet->set_size = $request->sizeList[$key];
                    $save_orderProductSet->color_id = $request->colourList[$key];
                    $save_orderProductSet->set_quantity = $order_quantity;
                    $save_orderProductSet->no_of_pcs = $size_data->no_of_pcs;
                    $save_orderProductSet->total_quantity = $order_quantity * $size_data->no_of_pcs;
                    $save_orderProductSet->remain_set_quantity = $order_quantity;
                    $save_orderProductSet->remain_total_quantity = $order_quantity * $size_data->no_of_pcs;
                    $save_orderProductSet->status = 1;
                    $save_orderProductSet->save();

                    $sizeCounts = array_count_values($size_explode);
                    foreach ($sizeCounts as $size => $count) {

                        $totalQty = $count * $order_quantity;

                        $save_orderProductSetDetail = new OrderProductSetDetail();
                        $save_orderProductSetDetail->order_products_set_id = $save_orderProductSet->id;
                        $save_orderProductSetDetail->sku = '';
                        $save_orderProductSetDetail->size = $size;
                        $save_orderProductSetDetail->total_quantity = $totalQty;
                        $save_orderProductSetDetail->remaining_quantity = $totalQty;
                        $save_orderProductSetDetail->remaining_lot_allocated = $totalQty;
                        $save_orderProductSetDetail->status = 1;
                        $save_orderProductSetDetail->save();
                    }
                }
            }

            DB::commit();
            return ['status_code' => 1, 'message' => 'Order updated successfully.'];
        }
        catch (\Exception $e) {
            DB::rollBack();
            return ['status_code' => 0, 'message' => $e->getMessage()];
        }
    }

    public function assign_to(Request $request)
    {
        try {
            DB::beginTransaction();
            // Support both single-id and multi-id assignment
            $ids = [];
            if ($request->filled('order_product_set_ids')) {
                $ids = array_filter(explode(',', $request->order_product_set_ids));
            }
            elseif ($request->order_product_set_id) {
                $ids = [$request->order_product_set_id];
            }

            foreach ($ids as $setId) {
                $data = OrderProductSet::where('id', $setId)->first();
                if ($data) {

                    // Quantity to assign
                    $assignQty = $request->assign_quantity ? (int)$request->assign_quantity : $data->remain_total_quantity;

                    if ($assignQty > $data->remain_total_quantity) {
                        throw new \Exception("Assigned quantity ($assignQty) exceeds remaining quantity ({$data->remain_total_quantity}) for Design: {$data->design_number}");
                    }

                    // Create Cutting Stage Record
                    $cuttingCount = OrderCuttingStage::where('order_main_id', $data->order_main_id)->count() + 1;
                    $cuttingSku = $data->sku . "/C" . $cuttingCount;

                    $cuttingStage = new OrderCuttingStage();
                    $cuttingStage->sku = $cuttingSku;
                    $cuttingStage->order_main_id = $data->order_main_id;
                    $cuttingStage->set_product_id = $data->id;
                    $cuttingStage->to_assign_id = $request->master_cutting_id; // StageMasterUnit ID
                    $cuttingStage->fabric_id = $request->fabric_id ?? null;
                    $cuttingStage->master_fitting_id = $request->master_fitting_id;
                    $cuttingStage->master_pattern_id = $request->master_pattern_id;
                    $cuttingStage->quantity = $assignQty;
                    $cuttingStage->remaining_quantity = $assignQty;
                    $cuttingStage->remarks = $request->remark ?? null;
                    $cuttingStage->processed_by = auth()->id();
                    $cuttingStage->status = 1; // Pending at cutting
                    $cuttingStage->save();

                    // Update Order Product Set
                    $data->remain_total_quantity -= $assignQty;

                    // Update set quantity (approximate if not perfectly divisible)
                    if ($data->no_of_pcs > 0) {
                        $setsMoved = $assignQty / $data->no_of_pcs;
                        $data->remain_set_quantity = max(0, $data->remain_set_quantity - $setsMoved);
                    }
                    else {
                        $data->remain_set_quantity = 0;
                    }

                    // If NO remaining quantity, mark as fully assigned (status 2)
                    if ($data->remain_total_quantity <= 0) {
                        $data->status = 2;
                        $data->stage_master_unit_id = $request->master_cutting_id;
                        $data->fabric_id = $request->fabric_id ?? null;
                        $data->master_product_fitting_id = $request->master_fitting_id;
                        $data->master_design_pattern_id = $request->master_pattern_id;
                        $data->remark = $request->remark ?? null;
                    }
                    else {
                        $data->status = 1; // Still partially pending
                    }

                    $data->save();
                }
            }

            DB::commit();

            return [
                'status' => true,
                'message' => "Quantity assigned to Cutting Master successfully"
            ];

        }
        catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function fittings()
    {
        $data = MasterProductFitting::where('status', 1)->get();
        return $data;
    }



    function checkAssign(Request $request)
    {
        return $exists = OrderCuttingStage::where('order_main_id', $request->id)->exists();
    }

    function saveCustomSetSize($request)
    {
        $size_group = $request->finalGroup ?? '';
        $set_size = $request->set_size_name ?? '';
        $no_of_pcs = count(explode(',', $request->finalGroup)) ?? 0;

        $exists = MasterSizeMeasurement::where('set_size', $set_size)
            ->where('status', 2)
            ->exists();
        $new_size_set_id = '';
        if (!$exists) {
            $save_data = new MasterSizeMeasurement;
            $save_data->sku = '';
            $save_data->corporate_company_id = 1;
            $save_data->set_size = $set_size;
            $save_data->name = $set_size;
            $save_data->size_group = $size_group;
            $save_data->no_of_pcs = $no_of_pcs;
            $save_data->status = 2;
            $save_data->save();
            $new_size_set_id = $save_data->id;
        }
        else {
            $size_data = MasterSizeMeasurement::where('set_size', $set_size)
                ->where('status', 2)->first();
            $size_data->size_group = $size_group;
            $size_data->no_of_pcs = $no_of_pcs;
            $size_data->save();
            $new_size_set_id = $size_data->id;
        }


        $return_data['status_code'] = 1;
        $return_data['new_size_group'] = $size_group;
        $return_data['new_size_set_id'] = $new_size_set_id;
        $return_data['no_of_pcs'] = $no_of_pcs;
        $return_data['new_size_name'] = $set_size;
        return $return_data;
    }

    public function cutting_units()
    {
        $data = MasterFabricWarehouse::with(['cuttingUnits' => function ($q) {
            $q->where('master_stage_id', 3)
                ->where('status', 1);
        }])
            ->where('status', 1)
            ->get()->toArray();

        $result = [];
        foreach ($data as $warehouse) {
            $cutting_units = [];
            
            // Check both snake_case (default toArray) and camelCase
            $raw_units = $warehouse['cutting_units'] ?? $warehouse['cuttingUnits'] ?? [];
            
            foreach ($raw_units as $unit) {
                $cutting_units[] = [
                    'id' => $unit['id'],
                    'name' => $unit['name'],
                ];
            }

            $result[$warehouse['id']] = [
                'id' => $warehouse['id'],
                'warehouse_name' => $warehouse['cutting_master_name'],
                'cutting_units' => $cutting_units,
            ];
        }

        return $result;
    }

    public function getPatterns()
    {
        $data = MasterDesignPattern::where('status', 1)->orderBy('id', 'asc')->get();
        return $data;
    }

    public function fabrics()
    {
        $data = Fabric::where('status', 1)
            ->withSum('receiptDetails', 'remaining_quantity')
            ->having('receipt_details_sum_remaining_quantity', '>', 0)
            ->get(['id', 'name']);
        return $data;
    }

    public function storeDomestic(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Find or Create Customer "SnapKid DM"
            $customer = MasterCustomer::where('name', 'SnapKid DM')->first();
            if (!$customer) {
                $customer = new MasterCustomer();
                $customer->name = 'SnapKid DM';
                $customer->type = 'domestic';
                $customer->subtype = 'direct';
                $customer->status = 1;
                $customer->save();
            }

            // 2. Find or Create Design
            if ($request->filled('production_goods_id')) {
                $product = ProductionGoods::find($request->production_goods_id);
            } else {
                $product = ProductionGoods::where('design_number', 'Domestic Design')->first();
                if (!$product) {
                    $product = new ProductionGoods();
                    $product->design_number = 'Domestic Design';
                    $product->name_of_garment = 'Domestic Garment';
                    $product->sku = 'DM-DESIGN';
                    $product->status = 1;
                    $product->save();
                }
            }

            // 2.5 Find or Create Color "No Color"
            $noColor = \App\Models\MasterColor::where('name', 'No Color')->first();
            if (!$noColor) {
                $noColor = new \App\Models\MasterColor();
                $noColor->name = 'No Color';
                $noColor->status = 1;
                $noColor->save();
            }

            // 3. Create Order
            $save_data_main = new OrderMain;
            $save_data_main->sku = '';
            $save_data_main->order_type = 'domestic';
            $save_data_main->expected_delivery_date = date('Y-m-d');
            $save_data_main->master_customer_id = $customer->id;
            $save_data_main->status = 1;
            $save_data_main->save();

            $firstThree = strtoupper(substr($customer->name, 0, 3));
            $save_data_main->sku = $firstThree . "/" . date('d/m/Y') . '/' . $save_data_main->id;
            $save_data_main->save();

            // 4. Create Order Product Set
            $size_data = $this->getSizeDetails($request->set_size_id);
            $size_explode = explode(',', $size_data->size_group);
            $order_quantity = $request->product_quantity;

            $save_orderProductSet = new OrderProductSet;
            $save_orderProductSet->order_main_id = $save_data_main->id;
            $save_orderProductSet->sku = $save_data_main->sku . '/1';
            $save_orderProductSet->design_number = $product->design_number;
            $save_orderProductSet->production_goods_id = $product->id;
            $save_orderProductSet->set_size = $request->set_size_id;
            $save_orderProductSet->color_id = $noColor->id;
            $save_orderProductSet->set_quantity = $order_quantity;
            $save_orderProductSet->no_of_pcs = $size_data->no_of_pcs;
            $save_orderProductSet->total_quantity = $order_quantity * $size_data->no_of_pcs;
            $save_orderProductSet->remain_total_quantity = $order_quantity * $size_data->no_of_pcs;
            $save_orderProductSet->remain_set_quantity = $order_quantity;
            $save_orderProductSet->status = 1;
            $save_orderProductSet->save();

            $sizeCounts = array_count_values($size_explode);
            foreach ($sizeCounts as $size => $count) {
                $totalQty = $count * $order_quantity;
                $save_orderProductSetDetail = new OrderProductSetDetail();
                $save_orderProductSetDetail->order_products_set_id = $save_orderProductSet->id;
                $save_orderProductSetDetail->sku = '';
                $save_orderProductSetDetail->size = $size;
                $save_orderProductSetDetail->total_quantity = $totalQty;
                $save_orderProductSetDetail->remaining_quantity = $totalQty;
                $save_orderProductSetDetail->remaining_lot_allocated = $totalQty;
                $save_orderProductSetDetail->status = 1;
                $save_orderProductSetDetail->save();
            }

            // 5. Immediate Assign to Cutting Master
            if ($request->filled('master_cutting_id')) {
                $assignQty = $save_orderProductSet->total_quantity;

                $cuttingSku = $save_orderProductSet->sku . "/C1";

                $cuttingStage = new OrderCuttingStage();
                $cuttingStage->sku = $cuttingSku;
                $cuttingStage->order_main_id = $save_data_main->id;
                $cuttingStage->set_product_id = $save_orderProductSet->id;
                $cuttingStage->to_assign_id = $request->master_cutting_id;
                $cuttingStage->fabric_id = $request->fabric_id ?? null;
                $cuttingStage->master_fitting_id = $request->master_fitting_id;
                $cuttingStage->master_pattern_id = $request->master_pattern_id;
                $cuttingStage->quantity = $assignQty;
                $cuttingStage->remaining_quantity = $assignQty;
                $cuttingStage->remarks = $request->remark ?? null;
                $cuttingStage->processed_by = auth()->id();
                $cuttingStage->status = 1;
                $cuttingStage->save();

                // Update Order Product Set status
                $save_orderProductSet->remain_total_quantity = 0;
                $save_orderProductSet->remain_set_quantity = 0;
                $save_orderProductSet->status = 2; // Fully assigned
                $save_orderProductSet->stage_master_unit_id = $request->master_cutting_id;
                $save_orderProductSet->fabric_id = $request->fabric_id ?? null;
                $save_orderProductSet->master_product_fitting_id = $request->master_fitting_id;
                $save_orderProductSet->master_design_pattern_id = $request->master_pattern_id;
                $save_orderProductSet->remark = $request->remark ?? null;
                $save_orderProductSet->save();
            }

            DB::commit();
            return ['status_code' => 1, 'message' => 'The domestic order and assignment have been successfully created.'];
        }
        catch (\Exception $e) {
            DB::rollBack();
            return ['status_code' => 0, 'message' => $e->getMessage()];
        }
    }

}
