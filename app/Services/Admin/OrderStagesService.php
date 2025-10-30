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
use App\Models\MasterProductStage;

use App\Http\DataTable\Admin\OrderStagesDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderStagesService {
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
    public function stage_data(Request $request){
        $data = MasterProductStage::where('id',$request->stage_id)->first();
        return $data;
    }
    public function product_stage(){
        $data = MasterProductStage::where('status',1)->get();
        return $data;
    }


    

}