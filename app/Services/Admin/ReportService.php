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
use App\Models\PackingCarton;
use App\Models\MasterCustomer;
use App\Models\OrderDispatch;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;

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
            })->orderBy('id', 'desc')
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

            if ($lotNos->isEmpty()) {
                 $result[] = [
                    'order_date'      => $order->created_at,
                    'order_id'        => $order->id, // Added ID
                    'customer'        => $order->customer->name ?? '',
                    'order_no'        => $order->sku,
                    'lot_no'          => '-',
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot'   => 0,
                    'stage_name'      => 'Pending',
                    'isDelayed'       => 'No',
                    'allowed_till_datetime' => null,
                    'current_datetime' => now()->toDateTimeString(),
                ];
                continue;
            }

            foreach ($lotNos as $lot_no) {

                //$parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();
                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->where('from_stage_id',3)->where('to_stage_id',4)->get();
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
                    'order_id'        => $order->id, // Added ID
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

    // public function getSalesOrderDetails($id)
    // {
    //     // 1. Fetch Order and Product Sets
    //     $order = OrderMain::where('id', $id) // Switch to ID
    //         ->with([
    //             'customer',
    //             'OrderProductSets.colors',
    //             'OrderProductSets.sizeMeasurement'
    //         ])
    //         ->firstOrFail();

    //     // 2. Process hierarchy
    //     // Structure: Order -> Sets -> Lots -> Transactions
        
    //     $order->OrderProductSets->each(function ($set) {
            
    //         // Collect Unique Lots for this set
    //         // Lots can be found via product_set_details -> fabric_roll_assigning
    //         // Or if design level, maybe we need to fetch lots by design_id?
    //         // Let's stick to what we know: fabric_roll_assigning links to order_products_set_id
            
    //         $lots = \App\Models\FabricRollAssigning::where('order_products_set_id', $set->id)
    //             ->with(['stageMasterUnit.masterStage'])
    //             ->get();
    //         $allStages = \App\Models\MasterProductStage::where('status', 1)->get()
    //             ->sortBy(function ($stage) {
    //                 $order = [3, 2, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    //                 $pos = array_search($stage->id, $order);
    //                 return $pos === false ? 999 : $pos;
    //             });
            

    //         $lots->each(function ($lot) use ($allStages, $set) {
    //             // Fetch transaction history
    //             $transactions = \App\Models\OrderStageTransaction::where('lot_no', $lot->lot_no)->get();
                
    //             // Calculate Initial Pieces from Roll Assigning (Input to Cutting - Stage 3)
    //             // We need to calculate pieces from meter or if it's stored.
    //             // Looking at earlier code index: $pieces_in_lot += $no_of_pcs * $single_part->set_quantity;
    //             // But FabricRollAssigning doesn't have set_quantity. It has meter.
    //             // Actually, the lot is created IN Cutting. 
    //             // Let's assume the "Total Pcs" for the lot is the sum of quantities of the first transaction? 
    //             // OR we can sum up everything that ENTERED a stage.
                
    //             $summary = [];
                
    //             foreach($allStages as $stage) {
                    
    //                 // IN: Transactions arriving at this stage
    //                 // If multiple sources send to this stage (e.g. Stitching receives from Cutting AND Printing),
    //                 // they are likely parallel parts of the same lot. We should take the MAX flow, not SUM.
    //                 // Group by 'from_stage_id' and sum, then take max.
                    
    //                 $inFlows = $transactions->where('to_stage_id', $stage->id)
    //                     ->groupBy('from_stage_id')
    //                     ->map(function ($rows) {
    //                         return $rows->sum('quantity');
    //                     });
                    
    //                 $in = $inFlows->isEmpty() ? 0 : $inFlows->max();
                    
    //                 // OUT: Transactions leaving this stage
    //                 // If sending to multiple targets (e.g. Cutting sends to Stitching AND Printing),
    //                 // we should take the MAX flow to determine how much "Lot Quantity" has moved on.
                    
    //                 $outFlows = $transactions->where('from_stage_id', $stage->id)
    //                     ->groupBy('to_stage_id')
    //                     ->map(function ($rows) {
    //                         return $rows->sum('quantity');
    //                     });
                        
    //                 $out = $outFlows->isEmpty() ? 0 : $outFlows->max();
                    
    //                 // Special Case: Cutting (Stage 3) - Initial In
    //                 if ($stage->id == 3 && $in == 0) {
    //                      $parts = \App\Models\ProductionSlipDigitizationParts::where('lot_no', $lot->lot_no)->get();
    //                      $initial_pcs = 0;
    //                      foreach($parts as $part){
    //                          $pcs_per_set = \App\Models\MasterSizeMeasurement::where('id', $part->set_size)->value('no_of_pcs') ?? 0;
    //                          $initial_pcs += ($part->set_quantity * $pcs_per_set);
    //                      }
    //                      // If we found initial pcs, that's our IN.
    //                      if($initial_pcs > 0) {
    //                          $in = $initial_pcs;
    //                      }
    //                 }


    //                 $summary[] = [
    //                     'stage_name' => $stage->name,
    //                     'in' => $in,
    //                     'out' => $out,
    //                     'balance' => $in - $out
    //                 ];

    //             }
                
    //             $lot->stage_summary = $summary;
                
    //             // Keep history for detailed view if needed
    //             $lot->history = $transactions->load(['from_stage', 'to_stage']);
    //         });

    //         $set->lots = $lots;
    //     });

    //     return $order;
    // }

    // optimize code rrr 
    public function getSalesOrderDetails($id)
    {
        $order = OrderMain::where('id', $id)
            ->with([
                'customer',
                'OrderProductSets.colors',
                'OrderProductSets.sizeMeasurement'
            ])
            ->firstOrFail();

        $allStages = \App\Models\MasterProductStage::where('status', 1)->get()
            ->sortBy(function ($stage) {
                $order = [3, 2, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                return array_search($stage->id, $order) ?? 999;
            });

        $sizePcsMap = \App\Models\MasterSizeMeasurement::pluck('no_of_pcs', 'id');

        $order->OrderProductSets->each(function ($set) use ($allStages, $sizePcsMap) {

            $lots = \App\Models\FabricRollAssigning::select('lot_no', 'order_products_set_id')
                ->where('order_products_set_id', $set->id)
                ->groupBy('lot_no', 'order_products_set_id')
                ->get();

            if ($lots->isEmpty()) {
                $set->lots = collect();
                return;
            }

            $lotNos = $lots->pluck('lot_no');

            $transactionsByLot = \App\Models\OrderStageTransaction::whereIn('lot_no', $lotNos)
                ->with(['from_stage', 'to_stage'])
                ->get()
                ->groupBy('lot_no');

            $partsByLot = \App\Models\ProductionSlipDigitizationParts::whereIn('lot_no', $lotNos)
                ->get()
                ->groupBy('lot_no');

            $lots->each(function ($lot) use (
                $allStages,
                $transactionsByLot,
                $partsByLot,
                $sizePcsMap
            ) {

                $transactions = $transactionsByLot[$lot->lot_no] ?? collect();
                $parts = $partsByLot[$lot->lot_no] ?? collect();

                $initialPcs = 0;
                foreach ($parts as $part) {
                    $pcsPerSet = $sizePcsMap[$part->set_size] ?? 0;
                    $initialPcs += ($part->set_quantity * $pcsPerSet);
                }

                $summary = [];

                foreach ($allStages as $stage) {

                    // IN flow
                    $inFlows = $transactions->where('to_stage_id', $stage->id)
                        ->groupBy('from_stage_id')
                        ->map(fn ($rows) => $rows->sum('quantity'));

                    $in = $inFlows->isEmpty() ? 0 : $inFlows->max();

                    // OUT flow
                    $outFlows = $transactions->where('from_stage_id', $stage->id)
                        ->groupBy('to_stage_id')
                        ->map(fn ($rows) => $rows->sum('quantity'));

                    $out = $outFlows->isEmpty() ? 0 : $outFlows->max();

                    // Cutting stage special case
                    if ($stage->id == 3 && $in == 0 && $initialPcs > 0) {
                        $in = $initialPcs;
                    }

                    $summary[] = [
                        'stage_id'   => $stage->id,
                        'stage_name' => $stage->name,
                        'in'         => $in,
                        'out'        => $out,
                        'balance'    => $in - $out,
                    ];
                }

                $lot->stage_summary = $summary;
                $lot->history = $transactions;
            });

            $set->lots = $lots;
        });
        return $order;
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
                'fabric_receipt.vendor',
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
                    'supplier'        => $first->fabric_receipt->vendor->name,
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
                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->where('from_stage_id',3)->where('to_stage_id',4)->get();
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

    public function dispatchOrder1(Request $request)
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
            if (empty($cartons_data)) {
                continue; // Skip orders with no cartons
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
            // dd($order);
        }

        // dd($data);
        return $data;
    }

    public function dispatchOrder(Request $request)
    {   
        $results = OrderDispatch::with([
            'dispatchDetails:id,order_dispatch_id,carton_packing_id',
            'orderMain.customer',
            'orderMain.OrderProductSets.colors',
            'orderMain.OrderProductSets.sizeMeasurement'
        ])        
        ->when($request->filled('order_no'), function ($q) use ($request) {
            $q->where('sku', 'like', '%' . $request->order_no . '%');
        })
        ->when($request->filled('customer_id'), function ($q) use ($request) {
            $q->where('customer_id', $request->customer_id);
        })
        ->orderBy('id', 'desc')
        ->get()->toArray();
        $data = [];
        foreach($results as $order_dispatch){
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
            $data[] = [
                'order_dispatch_data' => $order_dispatch_data,
                'cartonsDetails' => $cartonsDetails,
            ];
        }
        // dd($data);
        return $data;
    }

    public function lots(Request $request)
    {
        $searchLot   = $request->lot_no;
        $searchOrder = $request->order_id;

        $lots = \App\Models\FabricRollAssigning::query()

            ->selectRaw('
                lot_no,
                MIN(id) as id,
                MIN(order_products_set_id) as order_products_set_id
            ')

            ->withSum('fabricRollAssigningsDetail as lot_quantity', 'quantity')

            ->with([
                'orderProductSet.orderMain.customer'
            ])

            ->when($searchLot, function ($q) use ($searchLot) {
                $q->where('lot_no', 'like', "%{$searchLot}%");
            })

            ->when($searchOrder, function ($q) use ($searchOrder) {
                $q->whereHas('orderProductSet.orderMain', function ($qq) use ($searchOrder) {
                    $qq->where('id', 'like', "%{$searchOrder}%");
                });
            })

            ->groupBy('lot_no')

            ->paginate(10)
            ->withQueryString();
        // dd($lots);
        $result = $lots->through(function ($lot) {

            $orderMain = $lot->orderProductSet?->orderMain;

            return [
                'order_id'      => $orderMain->id ?? null,
                'order_no'      => $orderMain->sku ?? '',
                'customer_name' => $orderMain->customer->name ?? '',
                'lot_no'        => $lot->lot_no,
                'lot_quantity'  => $lot->lot_quantity ?? 0,
            ];
        });

        return $result;
    }


    // public function lotDetails(Request $request){
    //     $lot_no = $request->lot_no;
        
        
        
    //     // $lots = FabricRollAssigning::with('fabricRollAssigningsDetail',
    //     //             'orderProductSet.orderMain.customer',
    //     //             'orderProductSet.size_measurement',
    //     //             'orderProductSet.colors',
    //     //             'orderProductSet.master_product_fitting',
    //     //             'orderProductSet.master_design_pattern',
    //     //             'orderProductSet.fabric'
    //     //         )   
    //     //             ->where('lot_no', $lot_no)
    //     //             ->select('id', 'lot_no', 'order_products_set_id')
    //     //             ->distinct()
    //     //             ->get();

    //     $lots_data = FabricRollAssigning::with('fabricRollAssigningsDetail',
    //                 'orderProductSet.orderMain.customer',
    //                 'orderProductSet.size_measurement',
    //                 'orderProductSet.colors',
    //                 'orderProductSet.master_product_fitting',
    //                 'orderProductSet.master_design_pattern',
    //                 'orderProductSet.fabric'
    //             )   
    //             ->where('lot_no', $lot_no)
    //             ->select( 'lot_no', 'order_products_set_id')
    //             ->distinct()
    //             ->get();
    //     $rolls = FabricRollAssigning::where('lot_no', $lot_no)
    //                 ->select('id', 'lot_no', 'roll_no', 'meter')
    //                 ->get();

    //     $rolls_data = [];
    //     if (!$rolls->isEmpty()) {
    //         foreach ($rolls as $roll) {
    //             $rolls_data[] = [
    //                 'roll_no' => $roll->roll_no,
    //                 'meter'   => $roll->meter,
    //             ];
    //         }
    //     }
        
    //     $data = [
    //         'lots_data' => $lots_data,
    //         'rolls_data' => $rolls_data,
    //     ];

        
    //     $order = $lots_data->first()->orderProductSet->orderMain;
        
    //     $allStages = \App\Models\MasterProductStage::where('status', 1)->get()
    //         ->sortBy(function ($stage) {
    //             $order = [3, 2, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    //             return array_search($stage->id, $order) ?? 999;
    //         });
    //     // $sizePcsMap = $lots_data->first()->orderProductSet->size_measurement->pluck('no_of_pcs', 'id')->toArray();
    //     $sizePcsMap = $lots_data->first()->orderProductSet->size_measurement->no_of_pcs;
   
    //     $order->orderProductSet->each(function ($set) use ($lots_data, $allStages, $sizePcsMap) {

    //         $lots = $lots_data;

    //         if ($lots->isEmpty()) {
    //             $set->lots = collect();
    //             return;
    //         }

    //         $lotNos = $lots->pluck('lot_no');

    //         $transactionsByLot = \App\Models\OrderStageTransaction::whereIn('lot_no', $lotNos)
    //             ->with(['from_stage', 'to_stage'])
    //             ->get()
    //             ->groupBy('lot_no');

    //         $partsByLot = \App\Models\ProductionSlipDigitizationParts::whereIn('lot_no', $lotNos)
    //             ->get()
    //             ->groupBy('lot_no');

    //         $lots->each(function ($lot) use (
    //             $allStages,
    //             $transactionsByLot,
    //             $partsByLot,
    //             $sizePcsMap
    //         ) {

    //             $transactions = $transactionsByLot[$lot->lot_no] ?? collect();
    //             $parts = $partsByLot[$lot->lot_no] ?? collect();

    //             $initialPcs = 0;
    //             foreach ($parts as $part) {
    //                 $pcsPerSet = $sizePcsMap ?? 0;
    //                 $initialPcs += ($part->set_quantity * $pcsPerSet);
    //             }

    //             $summary = [];

    //             foreach ($allStages as $stage) {

    //                 // IN flow
    //                 $inFlows = $transactions->where('to_stage_id', $stage->id)
    //                     ->groupBy('from_stage_id')
    //                     ->map(fn ($rows) => $rows->sum('quantity'));

    //                 $in = $inFlows->isEmpty() ? 0 : $inFlows->max();

    //                 // OUT flow
    //                 $outFlows = $transactions->where('from_stage_id', $stage->id)
    //                     ->groupBy('to_stage_id')
    //                     ->map(fn ($rows) => $rows->sum('quantity'));

    //                 $out = $outFlows->isEmpty() ? 0 : $outFlows->max();

    //                 // Cutting stage special case
    //                 if ($stage->id == 3 && $in == 0 && $initialPcs > 0) {
    //                     $in = $initialPcs;
    //                 }

    //                 $summary[] = [
    //                     'stage_id'   => $stage->id,
    //                     'stage_name' => $stage->name,
    //                     'in'         => $in,
    //                     'out'        => $out,
    //                     'balance'    => $in - $out,
    //                 ];
    //             }

    //             $lot->stage_summary = $summary;
    //             $lot->history = $transactions;
    //         });

    //         $set->lots = $lots;
    //     });

    //     dd($order);
    //     return $order;
       
    //     return $orderMain;
    // }

    public function lotDetails(Request $request)
    {
        $lot_no = $request->lot_no;

        if (!$lot_no) {
            return response()->json(['message' => 'Lot number required'], 422);
        }

        /* ---------------- LOTS DATA ---------------- */
        $lots_data = FabricRollAssigning::with([
                'fabricRollAssigningsDetail',
                'orderProductSet.orderMain.customer',
                'orderProductSet.size_measurement',
                'orderProductSet.colors',
                'orderProductSet.master_product_fitting',
                'orderProductSet.master_design_pattern',
                'orderProductSet.fabric'
            ])
            ->where('lot_no', $lot_no)
            ->select('lot_no', 'order_products_set_id')
            ->distinct()
            ->get();

        if ($lots_data->isEmpty()) {
            return response()->json(['message' => 'No data found'], 404);
        }

        /* ---------------- ROLLS DATA ---------------- */
        $rolls_data = FabricRollAssigning::where('lot_no', $lot_no)
            ->select('roll_no', 'meter')
            ->get()
            ->toArray();

        /* ---------------- ORDER ---------------- */
        $order = $lots_data->first()->orderProductSet->orderMain;

        /* ---------------- STAGES ---------------- */
        $allStages = \App\Models\MasterProductStage::where('status', 1)->get()
            ->sortBy(function ($stage) {
                $order = [3, 2, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                return array_search($stage->id, $order) ?? 999;
            });

        /* ---------------- SIZE → PCS MAP ---------------- */
        $sizePcsMap = \App\Models\MasterSizeMeasurement::pluck('no_of_pcs', 'id')->toArray();

        /* ---------------- PROCESS SETS ---------------- */
        $order->OrderProductSets->each(function ($set) use (
            $lots_data,
            $allStages,
            $sizePcsMap
        ) {

            $lots = $lots_data->where('order_products_set_id', $set->id);

            if ($lots->isEmpty()) {
                $set->lots = collect();
                return;
            }

            $lotNos = $lots->pluck('lot_no');

            $transactionsByLot = \App\Models\OrderStageTransaction::whereIn('lot_no', $lotNos)
                ->with(['from_stage', 'to_stage'])
                ->get()
                ->groupBy('lot_no');

            $partsByLot = \App\Models\ProductionSlipDigitizationParts::whereIn('lot_no', $lotNos)
                ->get()
                ->groupBy('lot_no');

            $lots->each(function ($lot) use (
                $allStages,
                $transactionsByLot,
                $partsByLot,
                $sizePcsMap
            ) {

                $transactions = $transactionsByLot[$lot->lot_no] ?? collect();
                $parts = $partsByLot[$lot->lot_no] ?? collect();

                /* -------- INITIAL PCS -------- */
                $initialPcs = 0;
                foreach ($parts as $part) {
                    $pcsPerSet = $sizePcsMap[$part->set_size] ?? 0;
                    $initialPcs += ($part->set_quantity * $pcsPerSet);
                }

                /* -------- STAGE SUMMARY -------- */
                $summary = [];

                foreach ($allStages as $stage) {

                    $in = $transactions->where('to_stage_id', $stage->id)
                        ->groupBy('from_stage_id')
                        ->map(fn ($r) => $r->sum('quantity'))
                        ->max() ?? 0;

                    $out = $transactions->where('from_stage_id', $stage->id)
                        ->groupBy('to_stage_id')
                        ->map(fn ($r) => $r->sum('quantity'))
                        ->max() ?? 0;

                    if ($stage->id == 3 && $in == 0 && $initialPcs > 0) {
                        $in = $initialPcs;
                    }

                    $summary[] = [
                        'stage_id'   => $stage->id,
                        'stage_name' => $stage->name,
                        'in'         => $in,
                        'out'        => $out,
                        'balance'    => $in - $out,
                    ];
                }

                $lot->stage_summary = $summary;
                $lot->history = $transactions;
            });

            $set->lots = $lots->values();
        });
        dd($order);
        return response()->json([
            'order'      => $order,
            'lots_data'  => $lots_data,
            'rolls_data' => $rolls_data,
        ]);
    }

    public function lot_numbers()
    {
        $lots = \App\Models\FabricRollAssigning::query()
            ->selectRaw('
                lot_no,
                MIN(order_products_set_id) as order_products_set_id
            ')
            ->with([
                'orderProductSet.orderMain'
            ])
            ->groupBy('lot_no')
            ->orderBy('lot_no', 'asc')
            ->get();

        $result = $lots->map(function ($lot) {

            $orderMain = $lot->orderProductSet?->orderMain;

            return [
                'order_id' => $orderMain->id ?? null,
                'order_no' => $orderMain->sku ?? '',
                'lot_no'   => $lot->lot_no,
            ];
        });
        return $result; 
    }
    
    public function customers(){
        $data = MasterCustomer::where('status',1)->orderBy('name','asc')->get();
        return $data;
    }

}