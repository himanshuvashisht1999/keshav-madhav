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

}