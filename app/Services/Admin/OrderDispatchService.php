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
    protected $datatable;

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
            $data_save->gst_percentage = $request->gst_percentage ?? 0.00;
            $data_save->discount_percentage = $request->discount_percentage ?? 0.00;
            $data_save->total_amount = $request->total_amount ?? 0.00;
            $data_save->status = 1;
            $data_save->save();

            $data_save->sku = date('d/m/Y') . '/' . $data_save->main_order_id . "/" . $data_save->customer_id . "/" . $data_save->id ?? $data_save->id;
            $data_save->save();

            // Update Customer Balance (Subtraction means they owe the admin more)
            $customer = \App\Models\MasterCustomer::find($data_save->customer_id);
            if ($customer) {
                $customer->balance -= $data_save->total_amount;
                $customer->save();
            }

            // ================= UPDATE ITEM PRICES (GLOBAL SET-WISE) =================
            // Pricing update during dispatch removed as per requirement.
            // Items now carry prices assigned during the packing stage.
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
            if (!empty($pack_data) && $pack_data['remaining'] == 0) {
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 3
                    ]);
            } elseif (!empty($pack_data) && ($pack_data['remaining'] != 0 && $pack_data['packed'] > 0)) {
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
            'gst_percentage' => $order_dispatch['gst_percentage'],
            'discount_percentage' => $order_dispatch['discount_percentage'],
            'total_amount' => $order_dispatch['total_amount'],
            'total_cartons' => count($order_dispatch['dispatch_details']),
            'total_items_dispatch' => 0,
            'total_dispatch_amount' => 0,
            'cartons' => []
        ];

        $dispatch_carton_ids = [];
        foreach ($order_dispatch['dispatch_details'] as $v) {
            $dispatch_carton_ids[] = $v['carton_packing_id'];
        }

        // Fetch Cartons with Items and Details
        $cartons_data = PackingCarton::with([
            'items.detail.orderProductSet.colors',
            'items.detail.orderProductSet.size_measurement',
            'items.box.domesticInventory.product',
            'items.box.domesticInventory.color',
            'items.box.domesticInventory.sizeSet',
            'rack.storeroom'
        ])->whereIn('id', $dispatch_carton_ids)->get()->toArray();

        $total_items_dispatch = 0;
        $total_dispatch_amount = 0;
        $cartonsDetails = [];

        foreach ($cartons_data as $carton) {
            $total_items_in_carton = 0;

            // detailed summary logic
            $sets = [];
            if (isset($carton['items']) && is_array($carton['items'])) {
                foreach ($carton['items'] as $item) {
                    $qty = $item['quantity'];
                    
                    // Fallback to order basic price if selling_price is zero
                    $price = $item['selling_price'] ?? 0;
                    if ($price == 0) {
                        $ops = $item['detail']['order_product_set'] ?? null;
                        if ($ops && ($ops['total_quantity'] ?? 0) > 0) {
                            $price = ($ops['basic_amount'] ?? 0) / $ops['total_quantity'];
                        }
                    }

                    $total_items_in_carton += $qty;
                    $total_items_dispatch += $qty;
                    $total_dispatch_amount += ($qty * $price);

                    $setId = $item['detail']['order_products_set_id'] ?? 0;
                    if (!isset($sets[$setId])) {
                        $sets[$setId] = [
                            'design' => $item['box']['domestic_inventory']['product']['design_number'] ?? ($item['detail']['order_product_set']['design_number'] ?? 'N/A'),
                            'color' => $item['box']['domestic_inventory']['color']['name'] ?? ($item['detail']['order_product_set']['colors']['name'] ?? 'N/A'),
                            'size_set' => $item['box']['domestic_inventory']['size_set']['name'] ?? ($item['detail']['order_product_set']['size_measurement']['name'] ?? 'N/A'),
                            'price' => $price,
                            'total_qty' => 0,
                            'sizes_text' => []
                        ];
                    }
                    $sets[$setId]['total_qty'] += $qty;
                    $sets[$setId]['sizes_text'][] = ($item['detail']['size'] ?? 'N/A') . " (" . $qty . ")";
                }
            }

            $cartonsDetails[] = [
                'id' => $carton['id'],
                'carton_no' => $carton['carton_no'] ?? $carton['id'],
                'storeroom' => $carton['rack']['storeroom']['name'] ?? 'N/A',
                'rack' => $carton['rack']['name'] ?? 'N/A',
                'status' => $carton['status'] ?? 1,
                'total_items' => $total_items_in_carton,
                'sets' => array_values($sets), // Grouped by set
            ];
        }

        $order_dispatch_data['total_cartons'] = count($cartons_data);
        $order_dispatch_data['total_items_dispatch'] = $total_items_dispatch;
        $order_dispatch_data['total_dispatch_amount'] = $total_dispatch_amount;

        $data = [
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $cartonsDetails,
        ];

        return $data;
    }


    function getOrderPackingData($request)
    {
        $search_order_no = $request->search_order_no ?? "";
        $all_unique_sets = [];

        $results = OrderMain::with([
            'customer',
            'dispatchCartons' => function ($q) {
                $q->where('packing_cartons.status', 1);
                // Filter to only include cartons that contain corporate boxes
                $q->whereHas('boxes', function($bq) {
                    $bq->whereNotIn('box_type', ['domestic', 'manual', 'corporate_domestic']);
                });
            },
            'dispatchCartons.boxes',
            'dispatchCartons.items.detail.orderProductSet.colors',
            'dispatchCartons.items.detail.orderProductSet.size_measurement', 
            'dispatchCartons.items.box.domesticInventory.product',
            'dispatchCartons.items.box.domesticInventory.color',
            'dispatchCartons.items.box.domesticInventory.sizeSet',
        ])
            ->where('sku', $search_order_no)
            ->whereIn('status', [1, 2])
            ->orderBy('id', 'asc')
            ->get();

        $data = [];
        foreach ($results as $val) {
            $cartons = [];
            foreach ($val->dispatchCartons as $value) {

                // Aggregate items
                $summary = [];
                $pcs_in_carton = 0;
                $sets_list = [];

                if ($value->items) {
                    foreach ($value->items as $item) {
                        $sizeName = $item->detail->size ?? ($item->size->name ?? 'N/A');
                        $qty = $item->quantity;
                        $pcs_in_carton += $qty;

                        // Summary Text
                        if (!isset($summary[$sizeName])) $summary[$sizeName] = 0;
                        $summary[$sizeName] += $qty;

                        // Set info logic 
                        $orderSet = $item->detail->orderProductSet ?? null;
                        $setId = $orderSet->id ?? 0;

                        if ($setId && !isset($all_unique_sets[$setId])) {
                            $fallbackPrice = ($orderSet->total_quantity > 0) ? ($orderSet->basic_amount / $orderSet->total_quantity) : 0;
                            $all_unique_sets[$setId] = [
                                'set_id' => $setId,
                                'design' => $orderSet->design_number ?? 'N/A',
                                'color' => $orderSet->colors->name ?? 'N/A',
                                'size_set' => $orderSet->size_measurement->name ?? 'N/A',
                                'suggested_price' => $item->selling_price ?: $fallbackPrice,
                            ];
                        }

                        if ($setId && !isset($sets_list[$setId])) {
                            $fallbackPrice = ($orderSet->total_quantity > 0) ? ($orderSet->basic_amount / $orderSet->total_quantity) : 0;
                            $sets_list[$setId] = [
                                'set_id' => $setId,
                                'design' => $orderSet->design_number ?? 'N/A',
                                'color' => $orderSet->colors->name ?? 'N/A',
                                'size_set' => $orderSet->size_measurement->name ?? 'N/A',
                                'suggested_price' => $item->selling_price ?: $fallbackPrice,
                                'total_qty' => 0,
                                'sizes_text' => []
                            ];
                        }
                        
                        if($setId) {
                            $sets_list[$setId]['total_qty'] += $qty;
                            $sets_list[$setId]['sizes_text'][] = "$sizeName ($qty)";
                        }
                    }
                }

                $contents_text = [];
                foreach ($summary as $size => $qty) {
                    $contents_text[] = "$size ($qty)";
                }

                $cartons[] = [
                    'id' => $value->id,
                    'carton_no' => $value->carton_no,
                    'boxes_in_carton' => $value->boxes->count(),
                    'contents' => implode(', ', $contents_text),
                    'pcs_in_carton' => $pcs_in_carton,
                    'sets' => array_values($sets_list), 
                ];
            }

            $data[] = [
                'id' => $val->id,
                'sku' => $val->sku ?? '',
                'master_customer_id' => $val->master_customer_id,
                'customer' => $val->customer->name ?? 'N/A',
                'address' => $val->customer->address ?? '',
                'total_quantity' => $val->dispatchCartons->count(),
                'cartons' => $cartons,
                'unique_sets' => array_values($all_unique_sets)
            ];
        }
        return $data;
    }

    function getOrdersByCustomer($request)
    {
        $customer_id = $request->customer_id ?? "";
        $data = OrderMain::where('master_customer_id', $customer_id)
            ->whereIn('status', [1, 2])
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1)
                    ->whereHas('boxes', function ($bq) {
                        $bq->whereNotIn('box_type', ['domestic', 'manual', 'corporate_domestic']);
                    });
            })
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);


        return $data;
    }

    public function getOrders()
    {
        $data = OrderMain::whereIn('status', [1, 2])
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1)
                    ->whereHas('boxes', function ($bq) {
                        $bq->whereNotIn('box_type', ['domestic', 'manual', 'corporate_domestic']);
                    });
            })
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);
        return $data;
    }

    public function comppleteOrder()
    {
        return true;
    }
    public function getOrderDispatchData($orderMainId)
    {
        $total = DB::table('order_products_sets')
            ->where('order_main_id', $orderMainId)
            ->sum('total_quantity');

        $pack_mains = PackingMain::with([
            'cartons' => function ($q) {
                $q->where('status', 2) // Dispatched
                    ->withSum('items', 'quantity');
            }
        ])->where('order_main_id', $orderMainId)->get();

        $packed = 0;
        foreach($pack_mains as $session) {
            $packed += $session->cartons->sum('items_sum_quantity');
        }

        return [
            'total' => (int) $total,
            'packed' => (int) $packed,
            'remaining' => max(0, $total - $packed),
        ];
    }

}
