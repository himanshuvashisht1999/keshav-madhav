<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\OrderDispatch;
use App\Models\PackingCarton;
use App\Models\PackingCartonsDetails;
use App\Models\OrderDispatchDetails;
use App\Models\OrderProductSet;
use App\Models\OrderMain;
use PDF;


use App\Http\DataTable\Admin\OrderDispatchDataTable as DataTable;
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

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
        //    dd($request->all());

            // ✅ Safety check
            if (empty($request->cartons) || !is_array($request->cartons)) {
                return back()->with('error', 'No cartons selected for dispatch');
            }

            // ================= MAIN DISPATCH =================
            $data_save = new OrderDispatch();
            $data_save->customer_id     = $request->master_customer_id ?? null;
            $data_save->main_order_id   = $request->order_no ?? $request->final_order_no;
            $data_save->dispatch_date   = now();
            $data_save->total_quantity  = count($request->cartons);
            $data_save->status          = 1;
            $data_save->save();

            $data_save->sku     = date('d/m/Y') . '/'. $data_save->main_order_id."/" . $data_save->customer_id ."/" .$data_save->id ?? $data_save->id;
            $data_save->save();
            // ================= DETAILS =================
            $detailsData = [];

            foreach ($request->cartons as $cartonId) {
                $detailsData[] = [
                    'order_dispatch_id' => $data_save->id,
                    'carton_packing_id' => $cartonId,
                    'status'            => 1,
                ];
            }

            OrderDispatchDetails::insert($detailsData);

            // ================= UPDATE CARTON STATUS =================
            PackingCarton::whereIn('id', $request->cartons)
                ->update([
                    'status'     => 2
                ]);

            // Commit everything if all successful
            DB::commit();

            return [
                'id' => 1,
                'status_code' => 1,
                'message' => 'Order successfully Dispatched.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function view(Request $request){

        
        $order_dispatch = OrderDispatch::with([
            'dispatchDetails:id,order_dispatch_id,carton_packing_id',
            'orderMain.customer',
            'orderMain.OrderProductSets.colors',
            'orderMain.OrderProductSets.sizeMeasurement'
        ])->where('id',$request->id)->first()->toArray();
        // dd($order_dispatch);
        if($order_dispatch){
            $order_dispatch_data = [
                'id' =>  $order_dispatch['id'],
                'order_dispatch_no' =>  $order_dispatch['sku'],
                'order_no' => $order_dispatch['order_main']['sku'] ?? '',
                'customer' => $order_dispatch['order_main']['customer']['name'] ?? '',
                'dispatch_date' => date("d-m-Y h:i A", strtotime($order_dispatch['dispatch_date'])) ?? '',
            ];
        }
        $dispatch_carton_ids = [];
        foreach ($order_dispatch['dispatch_details'] as $v) {
           $dispatch_carton_ids[] = $v['carton_packing_id'];
        }

        $cartons_data = PackingCarton::with([
            'cartonsDetails',
            'orderMain.OrderProductSets.colors',
            'orderMain.OrderProductSets.sizeMeasurement'
        ])->whereIn('id',$dispatch_carton_ids)->get()->toArray();
        
        $total_boxes_session = 0;
        $cartonsDetails = []; 
        foreach($cartons_data as $carton){
            $total_boxes = 0;
            $car_data = [];
            foreach($carton['cartons_details'] as $val){
                foreach($carton['order_main']['order_product_sets'] as $order_product_sets){
                    if($val['bar_code'] == $order_product_sets['bar_code']){
                        $car_data[$val['bar_code']] = [
                            'bar_code'      => $order_product_sets['bar_code'],
                            'design_number' => $order_product_sets['design_number'],
                            'set_size'      => $order_product_sets['size_measurement']['set_size'],
                            'size_group'    => $order_product_sets['size_measurement']['size_group'],
                            'color'         => $order_product_sets['colors']['name'],
                            'no_of_pcs'     => $order_product_sets['no_of_pcs'],
                            'set_quantity'  => $val['set_quantity'],
                        ];
                    }
                }
                $total_boxes += $val['set_quantity'];
                $total_boxes_session += $val['set_quantity'];
            } 
            
            $cartonsDetails[$carton['id']] = [
                'id' => $carton['id'],
                'total_boxes' => $total_boxes,
                'car_data' => $car_data,
            ];
        }
        $order_dispatch_data['total_cartons'] = count($cartons_data);
        $order_dispatch_data['total_boxes_dispatch'] = $total_boxes_session;   
        $data = [
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $cartonsDetails,
        ];
        // dd($data);
        return $data;
    }


    function getOrderPackingData($request){
        $search_order_no = $request->search_order_no ?? "";
        
        $results = OrderMain::with([
                'customer',
                'dispatchCartons' => function ($q) {
                    $q->where('status', 1);
                },
                'dispatchCartons.cartonsDetails',
            ])
            ->where('sku', $search_order_no)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
            // dd($results);
        $data = [];
        foreach($results as $val){
            $cartons = [];
            foreach ($val['dispatch_cartons'] as $value) {

                $cartons[] = [
                    'id'                            => $value['id'] ?? '',
                    'carton_packing_session_id'     => $value['carton_packing_session_id'] ?? '',
                    'boxes_in_carton'               => count($value['cartons_details']) ?? 0,
                ];
            }
            $data[] = [
                'id'                    => $val['id'],
                // 'order_main_id'         => $val['order_main_id'],
                'sku'                   => $val['sku'] ?? '',
                'master_customer_id'    => $val['master_customer_id'],
                'customer'              => $val['customer']['name'],
                'slip_file'             => $val['corporate_order_file'],
                'address'               => $val['customer']['address'] ?? '',
                'total_quantity'        => count($val['dispatch_cartons']),
                'cartons'               => $cartons
            ];
        }
        return $data;
    }

    function getOrdersByCustomer($request){
        $customer_id = $request->customer_id ?? "";
        $data =  OrderMain::where('master_customer_id', $customer_id)
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->get(['id', 'sku as order_no']);
        

        return $data;
    }
}
