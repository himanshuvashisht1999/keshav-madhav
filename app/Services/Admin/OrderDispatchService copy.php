<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use App\Models\ProductionSlipDigitizationParts;
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

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
           dd($request->all());
            

            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Production Slip Digitization successfully completed.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
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

    function getCustomersBybarcode($request){
        $search_barcode = $request->search_barcode;
        $orderProductSet = OrderProductSet::with('colors:id,name',
        'orderMain.customer:id,name')
            ->where('bar_code', $search_barcode)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();

        $orderProductSetData = [];
        
        $results = [];
        foreach ($orderProductSet as $value) {
            $lot_no = ProductionSlipDigitizationParts::where('order_no', $value['sku'])
                ->where('status', 1)
                ->value('lot_no'); 
            $productionSlipDigitizationParts = ProductionSlipDigitizationParts::with('masterSizes')->where('lot_no',$lot_no)->where('to_stage_id',12)->where('status',1)->orderBy('id','asc')->get()->toArray();
            // dd($productionSlipDigitizationParts,$orderProductSet);
            $orderProductSetData[] = [
                'order_no'        => $value['sku'],
                'order_slip'      => $value['order_main']['corporate_order_file'] ?? '',
                'customer_id'      => $value['customer']['id'] ?? '',
                'customer'      => $value['customer']['name'] ?? '',
            ];
            // dd($productionSlipDigitizationParts,$orderProductSet);
            foreach ($productionSlipDigitizationParts as $parts_data) {
                $total_quantity = $parts_data['set_quantity'] * $parts_data['master_sizes']['no_of_pcs'];
                $results[] = [
                    'id'                    => $parts_data['id'],
                    'bar_code'              => $value['bar_code'],
                    'order_no'              => $value['sku'],
                    'design_number'         => $parts_data['design_number'],
                    'set_size'              => $parts_data['master_sizes']['set_size'],
                    'color'                 => $value['colors']['name'] ?? '',
                    'no_of_pcs'             => $parts_data['master_sizes']['no_of_pcs'],
                    'set_quantity'          => $parts_data['set_quantity'],
                    'total_quantity'        => $total_quantity,
                    'created_at'            => $parts_data['created_at'],
                ];
            }
        }
        $data = [
            'order_details' => $orderProductSetData,
            'order_products' => $results,
        ];
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
