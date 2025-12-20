<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\OrderDispatchCarton;
use App\Models\OrderDispatchCartonsDetails;
use App\Models\OrderProductSet;
use App\Models\OrderMain;
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
        //    dd($request->all());
            foreach ($request->cartons as $carton) {

                // ✅ 1️⃣ Create ONE carton
                $dispatchCarton = new OrderDispatchCarton;
                $dispatchCarton->customer_id   = $request->final_customer_id;
                $dispatchCarton->main_order_id = $request->final_order_no;
                $dispatchCarton->status        = 1;
                $dispatchCarton->save();

                // ✅ 2️⃣ Carton details
                foreach ($carton as $carton_data) {

                    $raw = json_decode($carton_data, true);

                    // save carton detail
                    $detail = new OrderDispatchCartonsDetails;
                    $detail->cartons_id   = $dispatchCarton->id;
                    $detail->bar_code     = $raw['barcode'];
                    $detail->set_quantity = $raw['qty'];
                    $detail->status       = 1;
                    $detail->save();

                    // Step 1: barcode ke base par product dhundo
                    $setData = OrderProductSet::where('bar_code', $raw['barcode'])
                        ->lockForUpdate()
                        ->first();

                    // Step 2: agar barcode se nahi mila
                    if (!$setData) {

                        // Step 2A: koi aisa product dhundo jiska barcode NULL / empty ho
                        $setData = OrderProductSet::whereNull('bar_code')
                            ->lockForUpdate()
                            ->first();

                        if (!$setData) {
                            throw new \Exception('Invalid barcode: ' . $raw['barcode']);
                        }

                        // ✅ Step 2B: barcode INSERT karo (sirf yahin)
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
                'set_size'              => $val['set_size'],
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
                'set_size'              => $val['set_size'],
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
}
