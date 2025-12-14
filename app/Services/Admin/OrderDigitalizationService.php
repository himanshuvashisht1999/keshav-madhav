<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ProductionGoods;
use App\Models\MasterCustomer;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use PDF;


use App\Http\DataTable\Admin\OrderDigitalizationDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDigitalizationService {
    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable= $datatable;
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
        // dd($request->all());
        try {
           
            ////// corporate order photo upload
            if($request->file('corporate_order_file')){
                $image = $request->file('corporate_order_file');
                $extImage = $image->getClientOriginalExtension();
                $imgName = "corporate_order_file-".rand()."_".time().".".$extImage;
                $destinationPath = public_path().'/assets/products';
                $image->move($destinationPath, $imgName);
            }

            $save_data_main = new OrderMain;
            $save_data_main->sku = '';
            $save_data_main->expected_delivery_date = $request->expected_delivery_date;
            $save_data_main->master_customer_id = $request->master_customer_id;
            $save_data_main->corporate_order_file = $imgName ?? null;
            $save_data_main->status = 1;
            $save_data_main->save();
            $customer_data = MasterCustomer::where('id',$request->master_customer_id)->first();
            $firstThree = strtoupper(substr($customer_data->name, 0, 3));

            $save_data_main->sku = $firstThree . "/". date('d/m/Y').'/' . $save_data_main->id;
            $save_data_main->save();

            
            
            // Create main order
            $i = 0;
            foreach ($request->designList as $key => $design_id) {
                $i++;
                $order_quantity = $request->product_quantity[$key];

                $size_data = $this->getSizeDetails($request->master_customer_id, $design_id);
                $size_explode = explode(',',$size_data->size_group);
                $product_data = ProductionGoods::where('design_number', $design_id)->first();
                if ($product_data) {
                    $save_orderProductSet = new OrderProductSet;
                    $save_orderProductSet->order_main_id = $save_data_main->id;
                    $save_orderProductSet->sku = $save_data_main->sku. '/'. $i;
                    $save_orderProductSet->design_number = $design_id ?? null;
                    $save_orderProductSet->set_size = $request->sizeList[$key];
                    $save_orderProductSet->color_id = $request->colourList[$key];
                    $save_orderProductSet->set_quantity = $order_quantity;
                    $save_orderProductSet->no_of_pcs = $size_data->no_of_pcs;
                    $save_orderProductSet->total_quantity = $order_quantity * $size_data->no_of_pcs;
                    $save_orderProductSet->status = 1;
                    $save_orderProductSet->save();
                }     
                if(count($size_explode) == $size_data->no_of_pcs){
                      
                    foreach($size_explode as $single_size){
                     
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
                        $save_data->sku = $firstThree . "/". date('d/m/Y').'/' . $save_data->id;
                        $save_data->save();

                        // Default success response
                        $return_data['message'] = 'The sales order has been successfully created.';
                        $return_data['status_code'] = 1;

                        // Loop through ordered products
                        $product_data = ProductionGoods::where('design_number', $design_id)->first();
                        // dd($product_data);
                        if ($product_data) {
            
                            // Save order product
                            $save_order_product = new OrderProduct;
                            $save_order_product->order_id = $save_data->id;
                            $save_order_product->order_main_id = $save_data_main->id;
                            $save_order_product->product_type_sku = $product_data->type_of_garment;
                            $save_order_product->product_sku = $product_data->name_of_garment;
                            $save_order_product->design_number = $design_id ?? null;
                            $save_order_product->size = $single_size;
                            $save_order_product->color_id = $request->colourList[$key];
                            $save_order_product->quantity = $order_quantity;
                            $save_order_product->save();
                            // dd($request->all());
                           
                            //// save order product stages
                            
                            // $product_stages = ProductStage::where('master_product_id',$product_data->id)->orderBy('id','asc')->where('status',1)->get();
                            // $sequence_value = 1;
                            // foreach($product_stages as $key=>$single_stage){
                            //     $save_order_product_stage = new OrderProductStage;
                            //     $save_order_product_stage->order_product_id = $save_order_product->id;
                            //     $save_order_product_stage->stage_id = $single_stage->master_stage_id;
                            //     $save_order_product_stage->sequence = $sequence_value;
                            //     if($key == 0){
                            //         $save_order_product_stage->total_qty = $order_quantity;
                            //         $save_order_product_stage->completed_qty = 0;
                            //         $save_order_product_stage->pending_qty = $order_quantity;
                            //         $save_order_product_stage->status = 1;  // 1: In progress
                            //     }else{
                            //         $save_order_product_stage->total_qty = 0;
                            //         $save_order_product_stage->completed_qty = 0;
                            //         $save_order_product_stage->pending_qty = 0;
                            //         $save_order_product_stage->status = 0;  // 0-pending
                            //     }
                            //     $save_order_product_stage->save();  
                            //     $sequence_value++;

                            // }

                            // $product_items = ProductionGoodsItem::where('product_id',$product_data->id)->orderBy('id','asc')->where('status',1)->get();
                            // foreach($product_items as $key=>$single_item){
                            //     $total_qty = $single_item->quantity * $order_quantity;
                            //     $save_order_product_items = new OrderProductItem;
                            //     $save_order_product_items->order_product_id = $save_order_product->id;
                            //     $save_order_product_items->item_sku = $single_item->item_attribute_value_sku;
                            //     $save_order_product_items->quantity = $single_item->quantity;
                            //     $save_order_product_items->order_quantity = $order_quantity;
                            //     $save_order_product_items->total_item_quantity = $total_qty;
                            //     $save_order_product_items->pending_quantity = $total_qty;
                            //     $save_order_product_items->status = 0;
                            //     $save_order_product_items->save();

                            // }

                        }
                    }
                } else{
                    throw new \Exception("Size measurement not matched for design id ".$design_id);
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

}
