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
use App\Models\GeneralSettings;
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
            $data_save->dispatch_date = $request->dispatch_date ?? now();
            $data_save->bill_number = $request->bill_number;
            $data_save->company_id = $request->company_id;
            $data_save->total_quantity = count($request->cartons);
            $data_save->gst_percentage = $request->gst_percentage ?? 0.00;
            $data_save->gst_amount = $request->gst_amount ?? 0.00;
            $data_save->discount_percentage = $request->discount_percentage ?? 0.00;
            $data_save->discount_amount = $request->discount_amount ?? 0.00;
            $data_save->total_amount = $request->total_amount ?? 0.00;
            $data_save->other_charges = $request->other_charges ?? 0.00;
            $data_save->remark = $request->remark;
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

        $order_dispatch_model = OrderDispatch::with([
            'dispatchDetails:id,order_dispatch_id,carton_packing_id',
            'orderMain.customer',
        ])->where('id', $request->id)->first();

        if (!$order_dispatch_model) {
            return null;
        }

        $order_dispatch = $order_dispatch_model->toArray();

        // Basic Info
        $order_dispatch_data = [
            'id' => $order_dispatch['id'],
            'order_dispatch_no' => $order_dispatch['sku'],
            'bill_number' => $order_dispatch['bill_number'] ?? '',
            'order_no' => $order_dispatch['order_main']['sku'] ?? '',
            'customer' => $order_dispatch['order_main']['customer']['name'] ?? '',
            'address' => $order_dispatch['order_main']['customer']['address'] ?? '',
            'dispatch_date' => date("d-m-Y h:i A", strtotime($order_dispatch['dispatch_date'])) ?? '',
            'gst_percentage' => $order_dispatch['gst_percentage'],
            'discount_percentage' => $order_dispatch['discount_percentage'],
            'discount_amount' => $order_dispatch['discount_amount'] ?? 0,
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
        $cartons_data_models = PackingCarton::with([
            'items.detail.orderProductSet.colors',
            'items.detail.orderProductSet.size_measurement',
            'rack.storeroom'
        ])->whereIn('id', $dispatch_carton_ids)->get();

        // Disable expensive appends that cause N+1 query storms during serialization
        foreach ($cartons_data_models as $carton) {
            foreach ($carton->items as $item) {
                if ($item->detail && $item->detail->orderProductSet) {
                    $item->detail->orderProductSet->setAppends([]);
                }
            }
        }

        $cartons_data = $cartons_data_models->toArray();

        $total_items_dispatch = 0;
        $total_dispatch_amount = 0;
        $finalCartonData = [];
        $consolidatedGroupedItems = [];

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
                    
                    // Specific design/color identification for CORPORATE orders (via OrderProductSet)
                    $design = $item['detail']['order_product_set']['design_number'] ?? 'N/A';
                    $color = $item['detail']['order_product_set']['colors']['name'] ?? 'N/A';
                    $sizeSet = $item['detail']['order_product_set']['size_measurement']['name'] ?? 'N/A';

                    if (!isset($sets[$setId])) {
                        $sets[$setId] = [
                            'design' => $design,
                            'color' => $color,
                            'size_set' => $sizeSet,
                            'price' => $price,
                            'total_qty' => 0,
                            'sizes_text' => []
                        ];
                    }
                    $sets[$setId]['total_qty'] += $qty;
                    $sets[$setId]['sizes_text'][] = ($item['detail']['size'] ?? 'N/A') . " (" . $qty . ")";

                    // For consolidated grouped items (whole dispatch)
                    $groupId = $design . '_' . $color . '_' . $sizeSet . '_' . $price;
                    if(!isset($consolidatedGroupedItems[$groupId])) {
                        $consolidatedGroupedItems[$groupId] = [
                            'product_name' => $design,
                            'color_name' => $color,
                            'size_set_name' => $sizeSet,
                            'selling_price' => $price,
                            'total_qty' => 0,
                            'box_count' => 0,
                            'carton_ids' => [],
                            'box_ids' => []
                        ];
                    }
                    $consolidatedGroupedItems[$groupId]['total_qty'] += $qty;
                    $consolidatedGroupedItems[$groupId]['carton_ids'][] = $carton['id'];
                }
            }

            $finalCartonData[] = [
                'id' => $carton['id'],
                'carton_no' => $carton['carton_no'] ?? $carton['id'],
                'storeroom' => $carton['rack']['storeroom']['name'] ?? 'N/A',
                'rack' => $carton['rack']['name'] ?? 'N/A',
                'status' => $carton['status'] ?? 1,
                'total_items' => $total_items_in_carton,
                'sets' => array_values($sets), // Grouped by set
            ];
        }

        // Carton count correction for consolidated items
        foreach ($consolidatedGroupedItems as $k => $group) {
             $consolidatedGroupedItems[$k]['carton_count'] = count(array_unique($group['carton_ids']));
             // Keep box_count for backward compatibility if UI uses it
             $consolidatedGroupedItems[$k]['box_count'] = count(array_unique($group['carton_ids']));
        }

        $order_dispatch_data['total_cartons'] = count($cartons_data);
        $order_dispatch_data['total_items_dispatch'] = $total_items_dispatch;
        $order_dispatch_data['total_dispatch_amount'] = $total_dispatch_amount;

        $filteredSubtotal = $total_dispatch_amount;
        $discountAmt = $order_dispatch_data['discount_amount'];
        $gstPercentage = $order_dispatch_model->gst_percentage ?? 5;
        $filteredGst = (($filteredSubtotal - $discountAmt) * $gstPercentage) / 100;
        $filteredGrandTotal = ($filteredSubtotal - $discountAmt) + $filteredGst;

        $data = [
            'dispatch' => $order_dispatch_model,
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $finalCartonData,
            'groupedItems' => array_values($consolidatedGroupedItems),
            'settings' => GeneralSettings::first(),
            'filteredSubtotal' => $filteredSubtotal,
            'discountAmt' => $discountAmt,
            'filteredGst' => $filteredGst,
            'filteredGrandTotal' => $filteredGrandTotal,
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
            },
            'dispatchCartons.items.detail.orderProductSet.colors',
            'dispatchCartons.items.detail.orderProductSet.size_measurement', 
        ])
            ->where('sku', $search_order_no)
            ->where('order_type', 'corporate')
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
                    'boxes_in_carton' => 0,
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
            ->where('order_type', 'corporate')
            ->whereIn('status', [1, 2])
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1);
            })
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);

        return $data;
    }

    public function getOrders()
    {
        $data = OrderMain::whereIn('status', [1, 2])
            ->where('order_type', 'corporate')
            ->whereHas('dispatchCartons', function ($q) {
                $q->where('packing_cartons.status', 1);
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
