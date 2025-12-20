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




    public function fabricRollDetails($fabricSku, $warehouseId)
    {
        return FabricReceiptDetail::where('fabric_sku', $fabricSku)
            ->where('master_fabric_warehouse_id', $warehouseId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('roll_number')
            ->get([
                'roll_number',
                'remaining_quantity',
                'qrcode_number',
                'barcode',      // accessor gives full image URL
                'qrcode'        // accessor gives full image URL
            ]);
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
                $expected_delivery_date = $this->calculateDynamicExpectedDelivery($order->expected_delivery_date,$lot_no);
                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();

                $stage_name = ProductionSlipDigitizationParts::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('to_stage_name');

                $expected_time = OrderStageTracking::where('lot_no', $lot_no)
                    ->orderBy('id', 'desc')->value('expected_time');

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
                    'expected_time' => $expected_time,
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

    public function lotTrackingDetails(Request $request)
    {
        $lot_no = $request->lot_no;

        $data = OrderStageTracking::where('lot_no', $lot_no)
            ->orderBy('id', 'asc')
            ->get();
        return $data;
    }

    private function calculateDynamicExpectedDelivery($orderExpectedDate, $lotNo)
    {
        $stages = OrderStageTracking::where('lot_no', $lotNo)
            ->orderBy('id', 'asc')
            ->get();

        if ($stages->count() < 2) {
            return $orderExpectedDate;
        }

        $adjustmentDays = 0;

        for ($i = 0; $i < $stages->count() - 1; $i++) {

            $current = $stages[$i];
            $next = $stages[$i + 1];

            if (!$current->expected_time) {
                continue;
            }

            $actualHours = Carbon::parse($current->created_at)
                ->diffInHours(Carbon::parse($next->created_at));

            $expectedHours = (int) $current->expected_time;

            $differenceHours = $actualHours - $expectedHours;

            // Convert to days (rounded)
            $adjustmentDays += round($differenceHours / 24);
        }

        return Carbon::parse($orderExpectedDate)
            ->addDays($adjustmentDays)
            ->format('Y-m-d');
    }




}