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
use App\Models\PackingMain;
use App\Models\PackingItem;
use PDF;


use App\Http\DataTable\Admin\OrderDispatchDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDispatchService
{
    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable = $datatable;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            //    dd($request->all());

            //  Safety check
            if (empty($request->cartons) || !is_array($request->cartons)) {
                return back()->with('error', 'No cartons selected for dispatch');
            }

            // ================= MAIN DISPATCH =================
            $data_save = new OrderDispatch();
            $data_save->customer_id = $request->final_customer_id ?? $request->master_customer_id;
            $data_save->main_order_id = $request->final_order_no ?? $request->order_no;
            $data_save->dispatch_date = now();
            $data_save->total_quantity = count($request->cartons);
            $data_save->status = 1;
            $data_save->save();

            $data_save->sku = date('d/m/Y') . '/' . $data_save->main_order_id . "/" . $data_save->customer_id . "/" . $data_save->id ?? $data_save->id;
            $data_save->save();
            // ================= DETAILS =================
            $detailsData = [];

            foreach ($request->cartons as $cartonId) {
                $detailsData[] = [
                    'order_dispatch_id' => $data_save->id,
                    'carton_packing_id' => $cartonId,
                    'status' => 1,
                ];
            }

            OrderDispatchDetails::insert($detailsData);

            // ================= UPDATE CARTON STATUS =================
            PackingCarton::whereIn('id', $request->cartons)
                ->update([
                    'status' => 2
                ]);

            $pack_data = getOrderDispatchData($data_save->main_order_id);
            if(!empty($pack_data) && $pack_data['remaining'] == 0){
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 3
                    ]);
            } elseif (!empty($pack_data) && ($pack_data['remaining'] != 0 && $pack_data['packed'] > 0 )){
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 2   // partial
                    ]);
            }
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

    public function view(Request $request)
    {

        $order_dispatch = OrderDispatch::with([
            'dispatchDetails:id,order_dispatch_id,carton_packing_id',
            'orderMain.customer',
            // 'orderMain.OrderProductSets.colors', // Not strictly needed if we rely on PackingItem detail
            // 'orderMain.OrderProductSets.sizeMeasurement'
        ])->where('id', $request->id)->first();

        if (!$order_dispatch) {
            return null;
        }

        $order_dispatch = $order_dispatch->toArray();

        // Basic Info
        $order_dispatch_data = [
            'id' => $order_dispatch['id'],
            'order_dispatch_no' => $order_dispatch['sku'],
            'order_no' => $order_dispatch['order_main']['sku'] ?? '',
            'customer' => $order_dispatch['order_main']['customer']['name'] ?? '',
            'address' => $order_dispatch['order_main']['customer']['address'] ?? '',
            'dispatch_date' => date("d-m-Y h:i A", strtotime($order_dispatch['dispatch_date'])) ?? '',
        ];

        $dispatch_carton_ids = [];
        foreach ($order_dispatch['dispatch_details'] as $v) {
            $dispatch_carton_ids[] = $v['carton_packing_id'];
        }

        // Fetch Cartons with Items and Details
        $cartons_data = PackingCarton::with([
            'items.detail',
            'rack.storeroom'
        ])->whereIn('id', $dispatch_carton_ids)->get()->toArray();

        $total_items_dispatch = 0;
        $cartonsDetails = [];

        foreach ($cartons_data as $carton) {
            $total_items_in_carton = 0;

            // detailed summary logic
            $summary = [];
            if (isset($carton['items']) && is_array($carton['items'])) {
                foreach ($carton['items'] as $item) {
                    $qty = $item['quantity'];
                    $total_items_in_carton += $qty;
                    $total_items_dispatch += $qty;

                    $sizeName = $item['detail']['size'] ?? 'ID:' . $item['size_id'];
                    if (!isset($summary[$sizeName]))
                        $summary[$sizeName] = 0;
                    $summary[$sizeName] += $qty;
                }
            }

            $cartonsDetails[] = [
                'id' => $carton['id'],
                'carton_no' => $carton['carton_no'] ?? $carton['id'],
                'storeroom' => $carton['rack']['storeroom']['name'] ?? 'N/A', // Assuming rack_id exists
                'rack' => $carton['rack']['name'] ?? 'N/A', // Assuming rack_id exists
                'status' => $carton['status'] ?? 1,
                'total_items' => $total_items_in_carton,
                'contents' => $summary,
            ];
        }

        $order_dispatch_data['total_cartons'] = count($cartons_data);
        $order_dispatch_data['total_items_dispatch'] = $total_items_dispatch;

        $data = [
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $cartonsDetails,
        ];

        return $data;
    }


    function getOrderPackingData($request)
    {
        $search_order_no = $request->search_order_no ?? "";

        $results = OrderMain::with([
            'customer',
            'dispatchCartons' => function ($q) {
                $q->where('packing_cartons.status', 1)
                    ->where('packing_mains.status', 1);
            },
            'dispatchCartons.items.detail', // Eager load detail for size string
        ])
            ->where('sku', $search_order_no)
            ->whereIn('status', [1,2])
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        // dd($results);
        $data = [];
        foreach ($results as $val) {
            $cartons = [];
            foreach ($val['dispatch_cartons'] as $value) {

                // Aggregate items
                $summary = [];
                $pcs_in_carton = 0;
                if (isset($value['items']) && is_array($value['items'])) {
                    foreach ($value['items'] as $item) {
                        $sizeName = $item['detail']['size'] ?? 'ID:' . $item['size_id'];
                        $qty = $item['quantity'];
                        if (!isset($summary[$sizeName]))
                            $summary[$sizeName] = 0;
                        $summary[$sizeName] += $qty;
                        $pcs_in_carton += $qty;
                    }
                }

                $contents_text = [];
                foreach ($summary as $size => $qty) {
                    $contents_text[] = "$size ($qty)";
                }

                $cartons[] = [
                    'id' => $value['id'] ?? '',
                    'carton_no' => $value['carton_no'] ?? '', // Ensure proper carton no
                    'carton_packing_session_id' => $value['carton_packing_session_id'] ?? '',
                    'boxes_in_carton' => count($value['items']) ?? 0,
                    'contents' => implode(', ', $contents_text),
                    'pcs_in_carton' => $pcs_in_carton ?? 0,
                ];
            }
            $data[] = [
                'id' => $val['id'],
                // 'order_main_id'         => $val['order_main_id'],
                'sku' => $val['sku'] ?? '',
                'master_customer_id' => $val['master_customer_id'],
                'customer' => $val['customer']['name'],
                'slip_file' => $val['corporate_order_file'],
                'address' => $val['customer']['address'] ?? '',
                'total_quantity' => count($val['dispatch_cartons']),
                'cartons' => $cartons
            ];
        }
        return $data;
    }

    function getOrdersByCustomer($request)
    {
        $customer_id = $request->customer_id ?? "";
        $data = OrderMain::where('master_customer_id', $customer_id)
            ->whereIn('status', [1,2])
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1)
                    ->where('packing_mains.status', 1);
            })
            ->where('order_type', 'corporate')
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);


        return $data;
    }

    public function getOrders()
    {
        $data = OrderMain::whereIn('status', [1,2])
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1)
                    ->where('packing_mains.status', 1);
            })
            ->where('order_type', 'corporate')
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);
        return $data;
    }

    public function comppleteOrder()
    {
        // $data =  OrderMain::where('status', 1)->where('id' , 10)
        //         ->orderBy('id', 'DESC')
        //         ->get(['id', 'sku as order_no', 'order_type'])->first();
        //     if ($data->order_type){

        //     }
        //         dd($data->order_type);
        // return $data;
    }
    public function getOrderDispatchData($orderMainId)
    {
        $total = DB::table('order_products_sets')
            ->where('order_main_id', $orderMainId)
            ->sum('total_quantity');

        $pack_items = PackingMain::with([
            'cartons' => function ($q) {
                $q->where('status', 2)
                ->withSum('items', 'quantity');
            }
        ])->where('order_main_id', $orderMainId)
        ->first();

        // safe check
        $packed = $pack_items ? $pack_items->cartons->sum('items_sum_quantity') : 0;

        return [
            'total'     => (int) $total,
            'packed'    => (int) $packed,
            'remaining' => max(0, $total - $packed),
        ];
    }
    
}
