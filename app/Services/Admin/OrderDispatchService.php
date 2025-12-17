<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use PDF;


use App\Http\DataTable\Admin\OrderDigitalizationDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDispatchService {
    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable= $datatable;
    }

    public function index(Request $request){
        return true;
    }

    function getCustomerOrders($request){
        $customer_id = $request->customer_id;
        $results = OrderMain::where('master_customer_id',$customer_id)->where('status',1)->orderBy('id','asc')->get()->toArray();
        $data = [];
        foreach($results as $val){
            $data[] = [
                'id'                    => $val['id'],
                'sku'                   => $val['sku'],
                'master_customer_id'    => $val['master_customer_id'],
                'corporate_order_file'  => $val['corporate_order_file'],
                'created_at'            => $val['created_at'],
            ];
        }
        return $data;
    }

    function getOrdersDetails($request){
        // $customer_id = $request->customer_id;
        $order_main_id = $request->order_main_id;
        $results = OrderProductSet::with('colors')->where('order_main_id',$order_main_id)->where('status',1)->orderBy('id','asc')->get()->toArray();
        $data = [];
        foreach($results as $val){
            $data[] = [
                'id'                    => $val['id'],
                'order_main_id'         => $val['order_main_id'],
                'bar_code'              => $val['bar_code'] ?? '',
                'design_number'         => $val['design_number'],
                'set_size'              => $val['set_size'],
                'color'                 => $val['colors']['name'] ?? '',
                'no_of_pcs'             => $val['no_of_pcs'],
                'set_quantity'          => $val['set_quantity'],
                'total_quantity'        => $val['total_quantity'],
                'created_at'            => $val['created_at'],
            ];
        }
        return $data;
    }
}
