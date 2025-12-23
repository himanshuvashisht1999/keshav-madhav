<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\OrderMain;
use App\Models\ProductionSlipDigitizationParts;
use App\Models\OrderProductSet;
use App\Models\MasterSizeMeasurement;
use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;
use App\Models\Fabric;
use App\Models\MasterFabricWarehouse;
use App\Models\PurchaseOrder;
use App\Models\OrderStageTracking;
use App\Models\OrderStageWiseTimeTracking;
use App\Models\MasterProductStage;
use App\Models\OrderDispatchCarton;
use Carbon\Carbon;

class ReportService {

//    public function salesOrder(Request $request){
//         $orders = OrderMain::with('customer')->all();
//         foreach($orders as $order){
//             $all_data = ProductionSlipDigitizationParts::where('from_stage_id',3)->where('to_stage_id',4)->where('order_no',$order->sku)->get();
//             $lot_no = '';
//             $total_pieces = 0;
//             foreach($all_data as $single_data){
//                 $part_data = ProductionSlipDigitizationParts::where('id',$single_data->id)->first();
//                 if($part_data){
//                     $master_size = MasterSize::where('id',$part_data->set_size)->first();
//                     $lot_no = $part_data->lot_no;
//                     $total_pieces+= $part_data->set_quantity;
//                 }
//             }
//             if($data){
//                 $result['order_date'] = $order->created_at;
//                 $result['customer'] = $order->customer->name;
//                 $result['order_no'] = $order->created_at;
//                 $result['lot_no'] = $lot_no;
//                 $result['total_pieces'] = $total_pieces;
//                 $result['pieces_in_lot'] = $order->created_at;
//                 $result['status'] = $order->created_at;
//             }
//         }
        

//         dd($order_ids);
//    }

    public function salesOrder(Request $request)
    {
        $result = [];

        $orders = OrderMain::with('customer')
            ->when($request->filled('order_no'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->get();

        foreach ($orders as $order) {

            $lotNos = ProductionSlipDigitizationParts::where('from_stage_id', 3)
                ->where('to_stage_id', 4)
                ->where('order_no', $order->sku)
                ->when($request->filled('lot_no'), function ($q) use ($request) {
                    $q->where('lot_no', 'like', '%' . $request->lot_no . '%');
                })
                ->distinct()
                ->pluck('lot_no');

            $total_pcs_in_order = OrderProductSet::where('order_main_id', $order->id)
                ->sum('total_quantity');

            foreach ($lotNos as $lot_no) {

                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();
                $stage_name = ProductionSlipDigitizationParts::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('to_stage_name');

                $allowed_till_datetime = ProductionSlipDigitizationParts::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('allowed_till_datetime');

                $currentTime = Carbon::now();
                $isDelayed = 'No';

                if ($allowed_till_datetime && $currentTime->greaterThan(Carbon::parse($allowed_till_datetime))) {
                    $isDelayed = 'Yes';
                }

                if ($request->filled('delay_status') && $request->delay_status !== $isDelayed) {
                    continue;
                }

                $pieces_in_lot = 0;
                foreach ($parts_data as $single_part) {
                    $no_of_pcs = MasterSizeMeasurement::where('id', $single_part->set_size)
                        ->value('no_of_pcs');
                    $pieces_in_lot += $no_of_pcs * $single_part->set_quantity;
                }

                $result[] = [
                    'order_date'      => $order->created_at,
                    'customer'        => $order->customer->name ?? '',
                    'order_no'        => $order->sku,
                    'lot_no'          => $lot_no,
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot'   => $pieces_in_lot,
                    'stage_name'      => $stage_name ?? '',
                    'isDelayed'       => $isDelayed,
                    'allowed_till_datetime' => $allowed_till_datetime,
                    'current_datetime' => $currentTime->toDateTimeString(),
                ];
            }
        }

        return collect($result)->groupBy('order_no');
    }


    public function stock(Request $request)
    { 
        $query = FabricReceiptDetail::query()
            ->selectRaw('
                fabric_sku,
                master_fabric_warehouse_id,
                SUM(remaining_quantity) as total_remaining
            ')
            ->with('master_fabric_warehouse:id,cutting_master_name')
            ->groupBy('fabric_sku', 'master_fabric_warehouse_id');

        // Warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('master_fabric_warehouse_id', $request->warehouse_id);
        }

        // Fabric filter
        if ($request->filled('fabric_sku')) {
            $query->where('fabric_sku', $request->fabric_sku);
        }

        // Remaining quantity range
        if ($request->filled('meter_from')) {
            $query->havingRaw('SUM(remaining_quantity) >= ?', [$request->meter_from]);
        }

        if ($request->filled('meter_to')) {
            $query->havingRaw('SUM(remaining_quantity) <= ?', [$request->meter_to]);
        }

        return $query->get()->groupBy('fabric_sku');
    }




    // public function fabricRollDetails($fabricSku, $warehouseId)
    // {
    //     return FabricReceiptDetail::where('fabric_sku', $fabricSku)
    //         ->where('master_fabric_warehouse_id', $warehouseId)
    //         ->where('remaining_quantity', '>', 0)
    //         ->orderBy('roll_number')
    //         ->get([
    //             'roll_number',
    //             'remaining_quantity',
    //             'qrcode_number',
    //             'barcode',      // accessor gives full image URL
    //             'qrcode'        // accessor gives full image URL
    //         ]);
    // }

    public function fabricRollDetails($fabricSku, $warehouseId)
    {
        return FabricReceiptDetail::with([
                'fabric_receipt',
                'purchase_order'
            ])
            ->where('fabric_sku', $fabricSku)
            ->where('master_fabric_warehouse_id', $warehouseId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('shipment_number')
            ->orderBy('roll_number')
            ->get()
            ->groupBy('shipment_number')
            ->map(function ($rows, $shipmentNo) {

                $first = $rows->first();

                return [
                    'shipment_number' => $shipmentNo,
                    'po_number'       => $first->purchase_order?->sku ?? '-', // ✅ PO number
                    'batch_no'        => $first->batch_no,
                    'receipt_date'    => optional($first->fabric_receipt)->created_at?->format('d M Y'),
                    'rolls' => $rows->map(function ($r) {
                        return [
                            'roll_number'        => $r->roll_number,
                            'remaining_quantity' => $r->remaining_quantity,
                            'qrcode_number'      => $r->qrcode_number,
                        ];
                    })->values()
                ];
            })->values();
    }


    public function stockRollDetailsByFilter(Request $request)
    {
        $query = FabricReceiptDetail::query()
            ->where('remaining_quantity', '>', 0);

        if ($request->filled('warehouse_id')) {
            $query->where('master_fabric_warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('fabric_sku')) {
            $query->where('fabric_sku', $request->fabric_sku);
        }

        return $query
            ->orderBy('fabric_sku')
            ->orderBy('roll_number')
            ->get()
            ->groupBy(fn ($row) =>
                $row->fabric_sku . '_' . $row->master_fabric_warehouse_id
            );
    }





    public function warehouses(){
        $warehouses = MasterFabricWarehouse::orderBy('cutting_master_name')->get();
        return $warehouses;
    }
    public function fabrics(){
        $fabrics = Fabric::orderBy('sku')->get();
        return $fabrics;
    }

    public function purchaseOrder(Request $request)
    {
        return PurchaseOrder::with([
                'vendor',
                'items'
            ])
            ->when($request->filled('sku'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->sku . '%');
            })
            ->when($request->filled('fabric_sku'), function ($q) use ($request) {
                $q->whereHas('items', function ($i) use ($request) {
                    $i->where('fabric_sku', $request->fabric_sku);
                });
            })
            ->orderBy('date', 'desc')
            ->get();
    }

    public function purchaseOrderItemReceipts($poItemId)
    {
        return FabricReceiptDetail::with('master_fabric_warehouse')
            ->where('purchase_order_item_id', $poItemId)
            ->where('status', 2) // adjusted
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'roll_number',
                'meter',
                'barcode',
                'qrcode_number',
                'master_fabric_warehouse_id',
                'created_at'
            ]);
    }

    public function orderTrackingSystem(Request $request)
    {
        $result = [];

        $orders = OrderMain::with('customer')
            ->when($request->filled('order_no'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('id','desc')->get();
        // $expected_delivery_date = '';
        foreach ($orders as $order) {
            $expected_delivery_date = $order->expected_delivery_date;
            // 1️⃣ Total pieces in order
            $total_pcs_in_order = OrderProductSet::where('order_main_id', $order->id)
                ->sum('total_quantity');
            

            // 2️⃣ Fetch allocated lots
            $lotNos = ProductionSlipDigitizationParts::where('from_stage_id', 3)
                ->where('to_stage_id', 4)
                ->where('order_no', $order->sku)
                ->when($request->filled('lot_no'), function ($q) use ($request) {
                    $q->where('lot_no', 'like', '%' . $request->lot_no . '%');
                })
                ->distinct()
                ->pluck('lot_no');

            $allocated_pieces = 0;

            foreach ($lotNos as $lot_no) {
                $expected_delivery_date = $this->calculateExpectedDeliveryFromPlan($order->expected_delivery_date,$lot_no);

                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();

                $stage_name = ProductionSlipDigitizationParts::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('to_stage_name');


                $allowed_till_datetime = ProductionSlipDigitizationParts::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('allowed_till_datetime');

                $currentTime = Carbon::now();
                $isDelayed = $this->calculateDelayFromPlan($lot_no);

                if ($request->filled('delay_status') && $request->delay_status !== $isDelayed) {
                    continue;
                }

                // 3️⃣ Calculate pieces in this lot
                $pieces_in_lot = 0;
                foreach ($parts_data as $single_part) {
                    $no_of_pcs = MasterSizeMeasurement::where('id', $single_part->set_size)
                        ->value('no_of_pcs');
                    $pieces_in_lot += $no_of_pcs * $single_part->set_quantity;
                }

                $allocated_pieces += $pieces_in_lot;

                $result[] = [
                    'order_date' => $order->created_at,
                    'customer' => $order->customer->name ?? '',
                    'order_no' => $order->sku,
                    'lot_no' => $lot_no,
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot' => $pieces_in_lot,
                    'stage_name' => $stage_name ?? '',
                    'isDelayed' => $isDelayed,
                    'allowed_till_datetime' => $allowed_till_datetime,
                    'current_datetime' => $currentTime->toDateTimeString(),
                    'expected_delivery_date' => $expected_delivery_date,
                    'expected_time' => $this->calculateFinalExpectedTimeFromPlan($lot_no),
                ];
            }

            // 4️⃣ ADD "NOT ISSUED" ROW IF REQUIRED
            $remaining_pieces = $total_pcs_in_order - $allocated_pieces;

            if ($remaining_pieces > 0) {
                $result[] = [
                    'order_date' => $order->created_at,
                    'customer' => $order->customer->name ?? '',
                    'order_no' => $order->sku,
                    'lot_no' => 'XXX',
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot' => $remaining_pieces,
                    'stage_name' => 'Not Issued',
                    'isDelayed' => 'No',
                    'allowed_till_datetime' => null,
                    'current_datetime' => Carbon::now()->toDateTimeString(),
                    'expected_delivery_date' => $expected_delivery_date,
                    'expected_time' => '-',
                ];
            }
        }


        return collect($result)->groupBy('order_no');
    }

    private function getCurrentStageFromPlan($lotNo)
    {
        $row = OrderStageWiseTimeTracking::where('lot_no', $lotNo)->first();

        if (!$row) return null;

        for ($i = 12; $i >= 1; $i--) {
            $col = 'stage_id_' . $i;
            if (!empty($row->$col)) {
                $stage_name = MasterProductStage::where('id', $i)->value('name');
                return [
                    'stage_id' => $i,
                    'expected_time' => $row->$col,
                    'stage_name' => $stage_name
                ];
            }
        }

        return null;
    }
    private function calculateDelayFromPlan($lotNo)
    {
        $currentStage = $this->getCurrentStageFromPlan($lotNo);

        if (!$currentStage) return 'No';

        return Carbon::now()->greaterThan(
            Carbon::parse($currentStage['expected_time'])
        ) ? 'Yes' : 'No';
    }

    private function calculateExpectedDeliveryFromPlan($orderExpectedDate, $lotNo)
    {
        $row = OrderStageWiseTimeTracking::where('lot_no', $lotNo)->first();

        if (!$row || empty($row->stage_id_12)) {
            return $orderExpectedDate;
        }

        return Carbon::parse($row->stage_id_12)
            ->format('Y-m-d');
    }
    public function lotTrackingDetails(Request $request)
    {
        $row = OrderStageWiseTimeTracking::where('lot_no', $request->lot_no)->first();

        if (!$row) {

            $data = [
                'current_stage' => null,
                'data' => []
            ];
            return $data;
        }

        $stage_numeric_array = [3,2,1,4,5,6,7,8,9,10,11,12];

        $data = [];
        // for ($i = 1; $i <= 12; $i++) {
        foreach ($stage_numeric_array as $i) {
            $col = 'stage_id_' . $i;
            if (!empty($row->$col)) {
                $stage_name = MasterProductStage::where('id', $i)->value('name');
                $data[] = [
                    'stage_id' => $i,
                    'expected_time' => $row->$col,
                    'expected_time' => Carbon::parse($row->$col)->format('d M Y h:i A'),
                    'stage_name' => $stage_name
                ];
            }
        }

        $current = end($data);

        $data = [
            'current_stage' => 'Stage ' . ($current['stage_id'] ?? ''),
            'data' => $data
        ];
        return $data;

    }

    private function calculateFinalExpectedTimeFromPlan($lotNo)
    {
        $row = OrderStageWiseTimeTracking::where('lot_no', $lotNo)->first();

        if (!$row || empty($row->stage_id_12)) {
            return null;
        }

        $maxDelayDays = 0;
        $now = Carbon::now();

        for ($i = 1; $i <= 12; $i++) {

            $col = 'stage_id_' . $i;

            if (empty($row->$col)) {
                continue;
            }

            $plannedTime = Carbon::parse($row->$col);

            // Stage should already be completed by now
            if ($now->greaterThan($plannedTime)) {

                $delayDays = $plannedTime->diffInDays($now);

                $maxDelayDays = max($maxDelayDays, $delayDays);
            }
        }

        return Carbon::parse($row->stage_id_12)
            ->addDays($maxDelayDays)
            ->format('d M Y h:i A');
    }

    public function dispatchOrder(Request $request)
    {
        $orders = OrderMain::with([
                'customer:id,name,address',
                'dispatchCartons.cartonsDetails:id,cartons_id,bar_code,set_quantity'
            ])
            ->when($request->filled('order_no'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('master_customer_id', $request->customer_id);
            })
            ->orderBy('id', 'desc')
            ->get();

        $data = [];

        foreach ($orders as $order) {

            $total_cartons = 0;
            $total_boxes = 0;
            $cartons_data = [];

            foreach ($order->dispatchCartons as $carton) {

                $total_cartons++;
                $cartons_data_details = []; // ✅ RESET HERE

                foreach ($carton->cartonsDetails as $box) {
                    $total_boxes += $box->set_quantity;

                    $cartons_data_details[] = [
                        'box_id'        => $box->id,
                        'bar_code'      => $box->bar_code,
                        'set_quantity'  => $box->set_quantity,
                    ];
                }

                $cartons_data[] = [
                    'carton_id' => $carton->id,
                    'created_at'=> $carton->created_at ? $carton->created_at->format('d-m-Y h:i A') : null,
                    'boxes'     => $cartons_data_details
                ];
            }

            $data[] = [
                'order_main_id' => $order->id,
                'order_no'      => $order->sku,

                'customer_id'   => optional($order->customer)->id,
                'customer_name' => optional($order->customer)->name,
                'address'       => optional($order->customer)->address,

                'total_cartons' => $total_cartons,
                'total_boxes'   => $total_boxes,
                'cartons'       => $cartons_data
            ];
        }

        dd($data);
        return $data;
    }

}