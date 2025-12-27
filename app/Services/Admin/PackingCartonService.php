<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\CartonPackingSession;
use App\Models\PackingCarton;
use App\Models\PackingCartonsDetails;
use App\Models\OrderProductSet;
use App\Models\OrderMain;
use PDF;


use App\Http\DataTable\Admin\PackingCartonDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class PackingCartonService {
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
        //    dd($request->all());
            $sessionCarton = new CartonPackingSession();
            $sessionCarton->customer_id   = $request->final_customer_id;
            $sessionCarton->main_order_id = $request->final_order_no;
            $sessionCarton->total_quantity = count($request->cartons);
            $sessionCarton->status        = 1;
            $sessionCarton->save();
            $sessionCarton->carton_packing_session_no =
                    date('d/m/Y') . '/' .
                    $request->final_customer_id . '/' .
                    $request->final_order_no . '/' .
                    $sessionCarton->id;

            $sessionCarton->save();

            foreach ($request->cartons as $carton) {

                // ✅ 1️⃣ Create ONE carton
                
                $dispatchCarton = new PackingCarton();
                $dispatchCarton->carton_packing_session_id   = $sessionCarton->id;
                $dispatchCarton->customer_id   = $request->final_customer_id;
                $dispatchCarton->main_order_id = $request->final_order_no;
                $dispatchCarton->status        = 1;
                $dispatchCarton->save(); //

                // ✅ 2️⃣ Carton details
                foreach ($carton as $carton_data) {

                    $raw = json_decode($carton_data, true);

                    // save carton detail
                    $detail = new PackingCartonsDetails;
                    $detail->cartons_id   = $dispatchCarton->id;
                    $detail->bar_code     = $raw['barcode'];
                    $detail->set_quantity = $raw['qty'];
                    $detail->status       = 1;
                    $detail->save();

                    // Step 2: agar barcode se nahi mila
                    $setData = OrderProductSet::where('bar_code', $raw['barcode'])
                        ->where('remain_set_quantity', '>', 0)   // 🔑 IMPORTANT
                        ->lockForUpdate()
                        ->first();

                    if (!$setData) {

                        // NULL barcode wala available product lo
                        $setData = OrderProductSet::whereNull('bar_code')
                            ->where('remain_set_quantity', '>', 0) // IMPORTANT
                            ->lockForUpdate()
                            ->first();

                        if (!$setData) {
                            throw new \Exception('Invalid or exhausted barcode: ' . $raw['barcode']);
                        }

                        // barcode assign karo
                        $setData->bar_code = $raw['barcode'];
                        $setData->save();
                    }
                    // Step 3: quantity validation
                    if ($setData->remain_set_quantity < $raw['qty']) {
                        throw new \Exception(
                            'Insufficient quantity for barcode ' . $raw['barcode']
                        );
                    }

                    // Step 4: quantity update
                    $setData->remain_set_quantity -= $raw['qty'];
                    $setData->remain_total_quantity -= ($raw['qty'] * $setData->no_of_pcs);
                    $setData->save();
                }
            }
            

            // Commit everything if all successful
            DB::commit();

            return [
                'id' => $sessionCarton->id,
                'status_code' => 1,
                'message' => 'Carton successfully Packed.'
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

        
        $cartons_session = CartonPackingSession::with([
            'orderMain.customer',
            'orderMain.OrderProductSets.colors',
            'orderMain.OrderProductSets.sizeMeasurement'
        ])->where('id',$request->id)->first()->toArray();
        if($cartons_session){
            $cartons_session_data = [
                'id' =>  $cartons_session['id'],
                'carton_packing_session_no' =>  $cartons_session['carton_packing_session_no'],
                'order_no' => $cartons_session['order_main']['sku'] ?? '',
                'customer' => $cartons_session['order_main']['customer']['name'] ?? '',
            ];
        }

        $cartons_data = PackingCarton::with([
            'cartonsDetails',
            'orderMain.OrderProductSets.colors',
            'orderMain.OrderProductSets.sizeMeasurement'
        ])->where('carton_packing_session_id',$request->id)->get()->toArray();
        // dd($cartons_data);
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
        $cartons_session_data['total_cartons'] = count($cartons_data);
        $cartons_session_data['total_boxes_session'] = $total_boxes_session;   
        $data = [
            'cartons_session_data' => $cartons_session_data,
            'cartonsDetails' => $cartonsDetails,
        ];
        // dd($data);
        return $data;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
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
        
        $results = OrderProductSet::with([
                'colors:id,name',
                'sizeMeasurement',
                'orderMain:id,master_customer_id,sku'
            ])->where('bar_code', $search_barcode)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $data = [];
        foreach($results as $val){
            $data[] = [
                'id'                    => $val['id'],
                'order_main_id'         => $val['order_main_id'],
                'bar_code'              => $val['bar_code'] ?? '',
                'design_number'         => $val['design_number'],
                'set_size'              => $val['size_measurement']['set_size'],
                'size_group'            => $val['size_measurement']['size_group'],
                'color'                 => $val['colors']['name'] ?? '',
                'no_of_pcs'             => $val['no_of_pcs'],
                'set_quantity'          => $val['set_quantity'],
                'total_quantity'        => $val['total_quantity'],
                'remain_set_quantity'   => $val['remain_set_quantity'],
                'remain_total_quantity' => $val['remain_total_quantity'],
                'created_at'            => $val['created_at'],
                'slip_file'             => $val['corporate_order_file'],
                'master_customer_id'    => $val['order_main']['master_customer_id'] ?? '',
                'order_id'              => $val['order_main']['id'] ?? '',
                'order_name'            => $val['order_main']['sku'] ?? '',
            ];
        }
        return $data;
    }

    function getOrdersDetails($request){
        // $customer_id = $request->customer_id;
        $order_main_id = $request->order_main_id;
        $results = OrderProductSet::with([
                'colors:id,name',
                'sizeMeasurement',
                'orderMain:id,master_customer_id,sku'
            ])->where('order_main_id', $order_main_id)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $data = [];
        foreach($results as $val){
            $data[] = [
                'id'                    => $val['id'],
                'order_main_id'         => $val['order_main_id'],
                'bar_code'              => $val['bar_code'] ?? '',
                'design_number'         => $val['design_number'],
                'set_size'              => $val['size_measurement']['set_size'],
                'size_group'            => $val['size_measurement']['size_group'],
                'color'                 => $val['colors']['name'] ?? '',
                'no_of_pcs'             => $val['no_of_pcs'],
                'set_quantity'          => $val['set_quantity'],
                'total_quantity'        => $val['total_quantity'],
                'remain_set_quantity'   => $val['remain_set_quantity'],
                'remain_total_quantity' => $val['remain_total_quantity'],
                'created_at'            => $val['created_at'],
                'slip_file'             => $val['corporate_order_file'],
                'master_customer_id'    => $val['order_main']['master_customer_id'] ?? '',
                'order_id'              => $val['order_main']['id'] ?? '',
                'order_name'            => $val['order_main']['sku'] ?? '',
            ];
        }
        return $data;
    }
    function getOrders(){
        $data = OrderMain::where('status', 1)
            ->orderBy('sku', 'asc')
            ->get();
        
        return $data;
    }
}
