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
use App\Models\OrderPrintingStageTransaction;

use Carbon\Carbon;

class ReportService
{

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
                    'order_date' => $order->created_at,
                    'order_id' => $order->id, // Added ID
                    'customer' => $order->customer->name ?? '',
                    'order_no' => $order->sku,
                    'lot_no' => '-',
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot' => 0,
                    'stage_name' => 'Pending',
                    'isDelayed' => 'No',
                    'allowed_till_datetime' => null,
                    'current_datetime' => now()->toDateTimeString(),
                ];
                continue;
            }

            foreach ($lotNos as $lot_no) {

                //$parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();
                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->where('from_stage_id', 3)->where('to_stage_id', 4)->get();
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
                    'order_date' => $order->created_at,
                    'order_id' => $order->id, // Added ID
                    'customer' => $order->customer->name ?? '',
                    'order_no' => $order->sku,
                    'lot_no' => $lot_no,
                    'total_pcs_in_order' => $total_pcs_in_order,
                    'pieces_in_lot' => $pieces_in_lot,
                    'stage_name' => $stage_name ?? '',
                    'isDelayed' => $isDelayed,
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

            $lots->each(function ($lot) use ($allStages, $transactionsByLot, $partsByLot, $sizePcsMap) {

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
                        ->map(fn($rows) => $rows->sum('quantity'));

                    $in = $inFlows->isEmpty() ? 0 : $inFlows->max();

                    // OUT flow
                    $outFlows = $transactions->where('from_stage_id', $stage->id)
                        ->groupBy('to_stage_id')
                        ->map(fn($rows) => $rows->sum('quantity'));

                    $out = $outFlows->isEmpty() ? 0 : $outFlows->max();

                    // Cutting stage special case
                    if ($stage->id == 3 && $in == 0 && $initialPcs > 0) {
                        $in = $initialPcs;
                    }

                    $summary[] = [
                        'stage_id' => $stage->id,
                        'stage_name' => $stage->name,
                        'in' => $in,
                        'out' => $out,
                        'balance' => $in - $out,
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
        $level = 'fabrics';

        if ($request->filled('fabric_id') && $request->filled('type')) {
            $level = $request->type; // 'receipts' or 'usages'
        } elseif ($request->filled('fabric_id')) {
            $level = 'warehouses';
        }

        if ($level === 'fabrics') {
            // Level 1: All Fabrics Paginated manually joined for efficiency
            $query = Fabric::query()
                ->select([
                    'fabrics.id',
                    'fabrics.name',
                    'vendors.name as vendor_name'
                ])
                ->leftJoin('vendors', 'fabrics.vendor_id', '=', 'vendors.id');

            if ($request->filled('warehouse_id')) {
                $whIds = array_map('intval', (array) $request->warehouse_id);
                $whIdsString = implode(',', $whIds);
                $query->selectRaw("
                    (
                        COALESCE((SELECT SUM(frd.meter) FROM fabric_receipt_details frd JOIN fabric_receipts fr ON frd.fabric_receipt_id = fr.id WHERE frd.fabric_id = fabrics.id AND fr.master_fabric_warehouse_id IN ({$whIdsString})), 0)
                        +
                        COALESCE((SELECT SUM(fti.meter) FROM fabric_transfer_items fti JOIN fabric_transfers ft ON fti.fabric_transfer_id = ft.id WHERE fti.fabric_id = fabrics.id AND ft.to_warehouse_id IN ({$whIdsString})), 0)
                    ) as total_received")
                ->selectRaw("COALESCE((SELECT SUM(frd.remaining_quantity) FROM fabric_receipt_details frd WHERE frd.fabric_id = fabrics.id AND frd.master_fabric_warehouse_id IN ({$whIdsString})), 0) as total_remaining")
                ->selectRaw("(
                    (
                        COALESCE((SELECT SUM(frd.meter) FROM fabric_receipt_details frd JOIN fabric_receipts fr ON frd.fabric_receipt_id = fr.id WHERE frd.fabric_id = fabrics.id AND fr.master_fabric_warehouse_id IN ({$whIdsString})), 0)
                        +
                        COALESCE((SELECT SUM(fti.meter) FROM fabric_transfer_items fti JOIN fabric_transfers ft ON fti.fabric_transfer_id = ft.id WHERE fti.fabric_id = fabrics.id AND ft.to_warehouse_id IN ({$whIdsString})), 0)
                    ) 
                    - 
                    COALESCE((SELECT SUM(frd.remaining_quantity) FROM fabric_receipt_details frd WHERE frd.fabric_id = fabrics.id AND frd.master_fabric_warehouse_id IN ({$whIdsString})), 0)
                ) as total_issued")
                ->groupBy('fabrics.id', 'fabrics.name', 'vendors.name');
            } else {
                $query->selectRaw('COALESCE(SUM(fabric_receipt_details.meter), 0) as total_received')
                    ->selectRaw('COALESCE(SUM(fabric_receipt_details.remaining_quantity), 0) as total_remaining')
                    ->selectRaw('COALESCE(SUM(fabric_receipt_details.meter - fabric_receipt_details.remaining_quantity), 0) as total_issued')
                    ->leftJoin('fabric_receipt_details', 'fabrics.id', '=', 'fabric_receipt_details.fabric_id')
                    ->groupBy('fabrics.id', 'fabrics.name', 'vendors.name');
            }

            $query->having('total_received', '>', 0)
                ->when($request->filled('qty_from'), function ($q) use ($request) {
                    $q->having('total_remaining', '>=', $request->qty_from);
                })
                ->when($request->filled('qty_to'), function ($q) use ($request) {
                    $q->having('total_remaining', '<=', $request->qty_to);
                })
                ->when($request->filled('search'), function ($q) use ($request) {
                    $q->where('fabrics.name', 'LIKE', '%' . $request->search . '%');
                })
                ->orderBy('fabrics.name');

            $totalsQuery = clone $query;
            $totals = \DB::query()->fromSub($totalsQuery, 'sub')->selectRaw('
                SUM(total_received) as sum_received,
                SUM(total_issued) as sum_issued,
                SUM(total_remaining) as sum_remaining
            ')->first();

            if ($request->has('is_export')) {
                return ['level' => 'fabrics', 'data' => $query->get(), 'totals' => $totals];
            }
            return [
                'level' => 'fabrics', 
                'data' => $query->paginate(20)->withQueryString(),
                'totals' => $totals
            ];
        }

        if ($level === 'warehouses') {
            // Level 2: Warehouses for a specific Fabric
            $fabricId = $request->fabric_id;
            $fabric = Fabric::find($fabricId);

            $query = \App\Models\MasterFabricWarehouse::query()
                ->select([
                    'master_fabric_warehouse.id as master_fabric_warehouse_id',
                    'master_fabric_warehouse.cutting_master_name',
                    \DB::raw("(
                        COALESCE((SELECT SUM(frd.meter) FROM fabric_receipt_details frd JOIN fabric_receipts fr ON frd.fabric_receipt_id = fr.id WHERE frd.fabric_id = {$fabricId} AND fr.master_fabric_warehouse_id = master_fabric_warehouse.id), 0)
                        +
                        COALESCE((SELECT SUM(fti.meter) FROM fabric_transfer_items fti JOIN fabric_transfers ft ON fti.fabric_transfer_id = ft.id WHERE fti.fabric_id = {$fabricId} AND ft.to_warehouse_id = master_fabric_warehouse.id), 0)
                    ) as total_received"),
                    \DB::raw("(SELECT COALESCE(SUM(frd.remaining_quantity), 0) FROM fabric_receipt_details frd WHERE frd.fabric_id = {$fabricId} AND frd.master_fabric_warehouse_id = master_fabric_warehouse.id) as total_remaining"),
                    \DB::raw("(
                        (
                            COALESCE((SELECT SUM(frd.meter) FROM fabric_receipt_details frd JOIN fabric_receipts fr ON frd.fabric_receipt_id = fr.id WHERE frd.fabric_id = {$fabricId} AND fr.master_fabric_warehouse_id = master_fabric_warehouse.id), 0)
                            +
                            COALESCE((SELECT SUM(fti.meter) FROM fabric_transfer_items fti JOIN fabric_transfers ft ON fti.fabric_transfer_id = ft.id WHERE fti.fabric_id = {$fabricId} AND ft.to_warehouse_id = master_fabric_warehouse.id), 0)
                        ) 
                        - 
                        (SELECT COALESCE(SUM(frd.remaining_quantity), 0) FROM fabric_receipt_details frd WHERE frd.fabric_id = {$fabricId} AND frd.master_fabric_warehouse_id = master_fabric_warehouse.id)
                    ) as total_issued")
                ])
                ->where('master_fabric_warehouse.status', 1)
                ->having('total_received', '>', 0)
                ->when($request->filled('qty_from'), function ($q) use ($request) {
                    $q->having('total_remaining', '>=', $request->qty_from);
                })
                ->when($request->filled('qty_to'), function ($q) use ($request) {
                    $q->having('total_remaining', '<=', $request->qty_to);
                });

            $data = $query->get();
            $data->each(function($item) {
                $item->master_fabric_warehouse = (object)['cutting_master_name' => $item->cutting_master_name];
            });
            $totals = (object) [
                'sum_received' => $data->sum('total_received'),
                'sum_issued' => $data->sum('total_issued'),
                'sum_remaining' => $data->sum('total_remaining'),
            ];

            return [
                'level' => 'warehouses',
                'fabric' => $fabric,
                'data' => $data,
                'totals' => $totals
            ];
        }

        if ($level === 'receipts') {
            $fabricId = $request->fabric_id;
            $warehouseId = $request->warehouse_id;

            $query = FabricReceiptDetail::with(['fabric_receipt.vendor', 'purchase_order', 'master_fabric_warehouse', 'returns'])
                ->where('fabric_id', $fabricId)
                ->when($warehouseId, function ($q) use ($warehouseId) {
                    $q->whereIn('master_fabric_warehouse_id', (array) $warehouseId);
                })
                ->when($request->filled('qty_from'), function ($q) use ($request) {
                    $q->where('remaining_quantity', '>=', $request->qty_from);
                })
                ->when($request->filled('qty_to'), function ($q) use ($request) {
                    $q->where('remaining_quantity', '<=', $request->qty_to);
                })
                ->orderBy('created_at', 'desc');

            $totalsQuery = clone $query;
            $totals = (object) [
                'sum_received' => $totalsQuery->sum('meter'),
                'sum_remaining' => $totalsQuery->sum('remaining_quantity'),
                'sum_issued' => $totalsQuery->sum(\DB::raw('meter - remaining_quantity'))
            ];

            if ($request->has('is_export')) {
                return [
                    'level' => 'receipts',
                    'fabric' => Fabric::find($fabricId),
                    'data' => $query->get(),
                    'totals' => $totals
                ];
            }
            return [
                'level' => 'receipts',
                'fabric' => Fabric::find($fabricId),
                'data' => $query->paginate(20)->withQueryString(),
                'totals' => $totals
            ];
        }

        if ($level === 'usages') {
            $fabricId = $request->fabric_id;
            $warehouseId = $request->warehouse_id;

            // Find all roll numbers matching this fabric & warehouse
            $rollQuery = FabricReceiptDetail::where('fabric_id', $fabricId);
            if ($warehouseId) {
                $rollQuery->whereIn('master_fabric_warehouse_id', (array) $warehouseId);
            }
            $rollIds = $rollQuery->pluck('id')->filter()->unique();

            $internalUsages = \App\Models\FabricRollAssigning::with(['orderProductSet.colors', 'stageMasterUnit', 'fabricReceiptDetail'])
                ->whereIn('fabric_receipt_detail_id', $rollIds->isEmpty() ? [0] : $rollIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $agentUsagesQuery = \App\Models\AgentOrderFabricItem::with(['order.vendor', 'order.shop', 'roll'])
                ->where('fabric_id', $fabricId);

            if ($warehouseId) {
                $agentUsagesQuery->whereHas('roll', function ($q) use ($warehouseId) {
                    $q->whereIn('master_fabric_warehouse_id', (array) $warehouseId);
                });
            }

            $agentUsages = $agentUsagesQuery->orderBy('created_at', 'desc')->get();

            $unifiedUsages = collect();

            foreach ($internalUsages as $u) {
                $unifiedUsages->push((object) [
                    'id' => $u->id,
                    'created_at' => $u->created_at,
                    'roll_no' => $u->roll_no,
                    'lot_no' => $u->lot_no,
                    'order_no' => $u->order_no,
                    'meter' => $u->meter,
                    'orderProductSet' => $u->orderProductSet,
                    'stageMasterUnit' => $u->stageMasterUnit
                ]);
            }

            foreach ($agentUsages as $a) {
                $partyName = $a->order?->shop_name ?? 'Unknown';
                $unifiedUsages->push((object) [
                    'id' => $a->id,
                    'created_at' => $a->created_at,
                    'roll_no' => $a->roll?->roll_number ?? '-',
                    'lot_no' => 'Agent Order',
                    'order_no' => $a->order?->sku ?? ('PO-' . $a->agent_order_id),
                    'meter' => $a->meter,
                    'orderProductSet' => (object) [
                        'design_number' => 'Selling Price: ' . number_format($a->selling_price, 2),
                        'colors' => (object) ['name' => 'Party: ' . $partyName]
                    ],
                    'stageMasterUnit' => (object) ['name' => 'Direct Sale']
                ]);
            }

            // Sort by created_at desc
            $unifiedUsages = $unifiedUsages->sortByDesc('created_at')->values();

            $totalsQuery = clone $rollQuery;
            $totals = (object) [
                'sum_received' => $totalsQuery->sum('meter'),
                'sum_remaining' => $totalsQuery->sum('remaining_quantity'),
                'sum_issued' => $totalsQuery->sum(\DB::raw('meter - remaining_quantity'))
            ];

            if ($request->has('is_export')) {
                return [
                    'level' => 'usages',
                    'fabric' => Fabric::find($fabricId),
                    'data' => $unifiedUsages,
                    'totals' => $totals
                ];
            }

            // Paginate manually
            $perPage = 20;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $currentPageItems = $unifiedUsages->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $unifiedUsages->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );

            return [
                'level' => 'usages',
                'fabric' => Fabric::find($fabricId),
                'data' => $paginatedData->withQueryString(),
                'totals' => $totals
            ];
        }

        return ['level' => 'fabrics', 'data' => collect([])];
    }

    public function fabricReturn(Request $request)
    {
        $query = \App\Models\FabricReturn::with(['receipt.vendor'])
            ->orderBy('date', 'desc');

        if ($request->filled('vendor_id')) {
            $query->whereHas('receipt', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->whereHas('receipt', function ($q) use ($request) {
                $q->where('sku', 'LIKE', '%' . $request->search . '%');
            });
        }

        return $query->paginate(20)->withQueryString();
    }

    public function getFabricReturnDetails($id)
    {
        return \App\Models\FabricReturn::with(['receipt.vendor', 'details.fabric', 'details.receipt_detail'])
            ->findOrFail($id);
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
            // Removed remaining_quantity > 0 to show the full ledger of rolls received, consumed, etc
            ->orderBy('shipment_number')
            ->orderBy('roll_number')
            ->get()
            ->groupBy('shipment_number')
            ->map(function ($rows, $shipmentNo) {

                $first = $rows->first();

                return [
                    'shipment_number' => $shipmentNo,
                    'po_number' => $first->purchase_order?->sku ?? '-', // ✅ PO number
                    'batch_no' => $first->batch_no,
                    'supplier' => $first->fabric_receipt->vendor->name ?? '-',
                    'receipt_date' => optional($first->fabric_receipt)->created_at?->format('d M Y'),
                    'rolls' => $rows->map(function ($r) {
                        return [
                            'roll_number' => $r->roll_number,
                            'original_quantity' => $r->meter,
                            'issued_quantity' => $r->meter - $r->remaining_quantity,
                            'remaining_quantity' => $r->remaining_quantity,
                            'qrcode_number' => $r->qrcode_number,
                        ];
                    })->values()
                ];
            })->values();
    }


    public function fabricLedger($fabricId, $warehouseId = null)
    {
        $query = FabricReceiptDetail::with([
            'fabric_receipt.vendor',
            'purchase_order'
        ])->where('fabric_id', $fabricId);

        if (!empty($warehouseId)) {
            $query->whereIn('master_fabric_warehouse_id', (array) $warehouseId);
        }

        $receipts = $query->get();

        $rollIds = $receipts->pluck('id')->filter()->unique();

        if ($rollIds->isEmpty()) {
            return [];
        }

        $usages = \App\Models\FabricRollAssigning::with(['orderProductSet.colors', 'stageMasterUnit', 'fabricReceiptDetail'])
            ->whereIn('fabric_receipt_detail_id', $rollIds)
            ->get();

        $ledger = [];

        foreach ($receipts as $r) {
            $ledger[] = [
                'date' => optional($r->fabric_receipt)->created_at ? $r->fabric_receipt->created_at->format('Y-m-d H:i:s') : $r->created_at->format('Y-m-d H:i:s'),
                'sort_date' => optional($r->fabric_receipt)->created_at ? $r->fabric_receipt->created_at->timestamp : $r->created_at->timestamp,
                'type' => 'Receipt',
                'shipment_no' => $r->shipment_number ?? '-',
                'po_number' => $r->purchase_order?->sku ?? '-',
                'roll_number' => $r->roll_number,
                'reference' => 'Supplier: ' . ($r->fabric_receipt->vendor->name ?? '-'),
                'in' => $r->meter,
                'out' => 0,
            ];
        }

        foreach ($usages as $u) {
            $designNo = $u->orderProductSet?->design_number ?? '-';
            $colorName = $u->orderProductSet?->colors?->name ?? '-';

            $refList = [
                'Lot No: ' . $u->lot_no,
                'Order: ' . $u->order_no,
                'Design: ' . $designNo,
                'Color: ' . $colorName,
                'Stage Unit: ' . ($u->stageMasterUnit?->name ?? '-')
            ];

            $ledger[] = [
                'date' => $u->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $u->created_at->timestamp,
                'type' => 'Usage',
                'shipment_no' => '-',
                'po_number' => '-',
                'roll_number' => $u->roll_no,
                'reference' => implode("\n", $refList),
                'in' => 0,
                'out' => $u->meter,
            ];
        }

        $agentUsagesQuery = \App\Models\AgentOrderFabricItem::with(['order.vendor', 'order.shop', 'roll'])
            ->where('fabric_id', $fabricId)
            ->whereNotNull('agent_order_dispatch_id');

        if (!empty($warehouseId)) {
            $agentUsagesQuery->whereHas('roll', function ($q) use ($warehouseId) {
                $q->whereIn('master_fabric_warehouse_id', (array) $warehouseId);
            });
        }

        $agentUsages = $agentUsagesQuery->get();

        foreach ($agentUsages as $a) {
            $partyName = $a->order?->shop_name ?? '-';
            $refList = [
                'Agent Order ID: ' . $a->agent_order_id,
                'Party: ' . $partyName,
                'Selling Price: ' . number_format($a->selling_price, 2),
            ];

            $ledger[] = [
                'date' => $a->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $a->created_at->timestamp,
                'type' => 'Usage (Agent Order)',
                'shipment_no' => '-',
                'po_number' => '-',
                'roll_number' => $a->roll?->roll_number ?? '-',
                'reference' => implode("\n", $refList),
                'in' => 0,
                'out' => $a->meter,
            ];
        }

        usort($ledger, function ($a, $b) {
            return $a['sort_date'] <=> $b['sort_date'];
        });

        $balance = 0;
        foreach ($ledger as &$trans) {
            $balance += $trans['in'];
            $balance -= $trans['out'];
            $trans['balance'] = $balance;
        }

        return array_reverse($ledger); // Most recent first, but balance calculated from chronological order
    }

    public function stockRollDetailsByFilter(Request $request)
    {
        $query = FabricReceiptDetail::query()
            ->where('remaining_quantity', '>', 0);

        if ($request->filled('warehouse_id')) {
            $query->whereIn('master_fabric_warehouse_id', (array) $request->warehouse_id);
        }

        if ($request->filled('fabric_id')) {
            $query->where('fabric_id', $request->fabric_id);
        }

        return $query
            ->orderBy('fabric_id')
            ->orderBy('roll_number')
            ->get()
            ->groupBy(
                fn($row) =>
                $row->fabric_id . '_' . $row->master_fabric_warehouse_id
            );
    }





    public function purchaseOrderFabricWise(Request $request)
    {
        $fabricId = $request->fabric_id;

        if ($fabricId) {
            // Case 2: History for a specific fabric
            return \App\Models\PurchaseOrderItem::with(['purchaseOrder.vendor', 'fabric'])
                ->where('fabric_id', $fabricId)
                ->when($request->filled('start_date'), function ($q) use ($request) {
                    $q->whereHas('purchaseOrder', function ($po) use ($request) {
                        $po->whereDate('date', '>=', $request->start_date);
                    });
                })
                ->when($request->filled('end_date'), function ($q) use ($request) {
                    $q->whereHas('purchaseOrder', function ($po) use ($request) {
                        $po->whereDate('date', '<=', $request->end_date);
                    });
                })
                ->when($request->filled('vendor_id'), function ($q) use ($request) {
                    $q->whereHas('purchaseOrder', function ($po) use ($request) {
                        $po->where('vendor_id', $request->vendor_id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Case 1: List of fabrics with purchase summary
        $query = \App\Models\Fabric::query()
            ->select([
                'fabrics.id',
                'fabrics.name',
                \DB::raw('COUNT(purchase_order_items.id) as total_purchase_orders'),
                \DB::raw('SUM(purchase_order_items.meter) as total_meters'),
                \DB::raw('AVG(purchase_order_items.price) as avg_rate')
            ])
            ->join('purchase_order_items', 'fabrics.id', '=', 'purchase_order_items.fabric_id')
            ->groupBy('fabrics.id', 'fabrics.name')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('fabrics.name', 'LIKE', '%' . $request->search . '%');
            })
            ->orderBy('fabrics.name');

        return $query->paginate(20)->withQueryString();
    }

    public function purchaseOrderFabricWiseShipments(Request $request)
    {
        $fabricId = $request->fabric_id;

        return \App\Models\FabricReceiptDetail::with(['purchase_order', 'master_fabric_warehouse', 'fabric', 'fabric_receipt.vendor'])
            ->where('fabric_id', $fabricId)
            ->where('status', '>', 0) // Arrived (1) or Received (2)
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('vendor_id'), function ($q) use ($request) {
                $q->whereHas('fabric_receipt', function ($r) use ($request) {
                    $r->where('vendor_id', $request->vendor_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function warehouses()
    {
        $warehouses = MasterFabricWarehouse::orderBy('cutting_master_name')->get();
        return $warehouses;
    }
    public function fabrics()
    {
        $fabrics = Fabric::orderBy('name')->get();
        return $fabrics;
    }
    public function vendors()
    {
        return \App\Models\Vendor::orderBy('name')->get();
    }

    public function purchaseOrder(Request $request)
    {
        $query = PurchaseOrder::with([
            'vendor',
            'items.receipts'
        ])
            ->when($request->filled('sku'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->sku . '%');
            })
            ->when($request->filled('vendor_id'), function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->end_date);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->status;
                $orderedSql = '(SELECT COALESCE(SUM(meter), 0) FROM purchase_order_items WHERE purchase_order_id = purchase_orders.id)';
                $receivedSql = '(SELECT COALESCE(SUM(fabric_receipt_details.meter), 0) FROM fabric_receipt_details INNER JOIN purchase_order_items ON purchase_order_items.id = fabric_receipt_details.purchase_order_item_id WHERE purchase_order_items.purchase_order_id = purchase_orders.id)';
                
                if ($status === 'delayed') {
                    $q->whereDate('delivery_date', '<', now()->format('Y-m-d'))
                      ->whereRaw("{$orderedSql} > {$receivedSql}");
                } elseif ($status === 'open') {
                    $q->whereRaw("{$orderedSql} > {$receivedSql}");
                } elseif ($status === 'closed') {
                    $q->whereRaw("{$orderedSql} <= {$receivedSql}");
                }
            })
            ->orderBy('date', 'desc');

        if ($request->has('is_export')) {
            return $query->get();
        }

        return $query->paginate(20);
    }

    public function purchaseOrderItemReceipts($poItemId)
    {
        return FabricReceiptDetail::with('master_fabric_warehouse')
            ->where('purchase_order_item_id', $poItemId)
            ->where('status', '>', 0) // Arrived or Received
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'roll_number',
                'meter',
                'barcode',
                'qrcode_number',
                'master_fabric_warehouse_id',
                'price_per_meter',
                'shipment_number',
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
            ->orderBy('id', 'desc')->get();
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
                $expected_delivery_date = $this->calculateExpectedDeliveryFromPlan($order->expected_delivery_date, $lot_no);



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
                $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->where('from_stage_id', 3)->where('to_stage_id', 4)->get();
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

        if (!$row)
            return null;

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

        if (!$currentStage)
            return 'No';

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

        $stage_numeric_array = [3, 2, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12];

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
                        'box_id' => $box->id,
                        'bar_code' => $box->bar_code,
                        'set_quantity' => $box->set_quantity,
                    ];
                }

                $cartons_data[] = [
                    'carton_id' => $carton->id,
                    'created_at' => $carton->created_at ? $carton->created_at->format('d-m-Y h:i A') : null,
                    'boxes' => $cartons_data_details
                ];
            }
            if (empty($cartons_data)) {
                continue; // Skip orders with no cartons
            }
            $data[] = [
                'order_main_id' => $order->id,
                'order_no' => $order->sku,

                'customer_id' => optional($order->customer)->id,
                'customer_name' => optional($order->customer)->name,
                'address' => optional($order->customer)->address,

                'total_cartons' => $total_cartons,
                'total_boxes' => $total_boxes,
                'cartons' => $cartons_data
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
        foreach ($results as $order_dispatch) {
            if ($order_dispatch) {
                $order_dispatch_data = [
                    'id' => $order_dispatch['id'],
                    'order_dispatch_no' => $order_dispatch['sku'],
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
            ])->whereIn('id', $dispatch_carton_ids)->get()->toArray();

            $total_boxes_session = 0;
            $cartonsDetails = [];
            foreach ($cartons_data as $carton) {
                $total_boxes = 0;
                $car_data = [];
                foreach ($carton['cartons_details'] as $val) {
                    foreach ($carton['order_main']['order_product_sets'] as $order_product_sets) {
                        if ($val['bar_code'] == $order_product_sets['bar_code']) {
                            $car_data[$val['bar_code']] = [
                                'bar_code' => $order_product_sets['bar_code'],
                                'design_number' => $order_product_sets['design_number'],
                                'set_size' => $order_product_sets['size_measurement']['set_size'],
                                'size_group' => $order_product_sets['size_measurement']['size_group'],
                                'color' => isset($order_product_sets['colors']) ? $order_product_sets['colors']['name'] . ' (' . $order_product_sets['colors']['id'] . ')' : '',
                                'no_of_pcs' => $order_product_sets['no_of_pcs'],
                                'set_quantity' => $val['set_quantity'],
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
        $searchLot = $request->lot_no;
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
                if (is_array($searchLot)) {
                    $q->whereIn('lot_no', $searchLot);
                } else {
                    $q->where('lot_no', 'like', "%{$searchLot}%");
                }
            })

            ->when($searchOrder, function ($q) use ($searchOrder) {
                $q->whereHas('orderProductSet.orderMain', function ($qq) use ($searchOrder) {
                    $qq->where('id', 'like', "%{$searchOrder}%");
                });
            })

            ->groupBy('lot_no')
            ->orderBy('id', 'desc')

            ->paginate(10)
            ->withQueryString();
        // dd($lots);
        $result = $lots->through(function ($lot) {

            $orderMain = $lot->orderProductSet?->orderMain;

            return [
                'order_id' => $orderMain->id ?? null,
                'order_no' => $orderMain->sku ?? '',
                'customer_name' => $orderMain->customer->name ?? '',
                'lot_no' => $lot->lot_no,
                'lot_quantity' => $lot->lot_quantity ?? 0,
            ];
        });

        return $result;
    }

    public function orderLotsDetailed(Request $request)
    {
        $searchLot = $request->lot_no;
        $searchOrder = $request->order_id;

        $query = \App\Models\OrderLot::with([
            'orderMain.customer',
            'orderProductSet'
        ])
            ->when($searchLot, function ($q) use ($searchLot) {
                if (is_array($searchLot)) {
                    $q->whereIn('lot_no', $searchLot);
                } else {
                    $q->where('lot_no', 'like', "%{$searchLot}%");
                }
            })
            ->when($searchOrder, function ($q) use ($searchOrder) {
                $q->where('order_main_id', $searchOrder);
            })
            ->orderBy('id', 'desc');

        $lots = $query->paginate(15)->withQueryString();

        return $lots->through(function ($lot) {

            $quantity = \App\Models\FabricRollAssigning::where('lot_no', $lot->lot_no)
                ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                ->get()
                ->sum('total');

            return [
                'id' => $lot->id,
                'order_id' => $lot->order_main_id,
                'order_no' => $lot->orderMain->sku ?? '',
                'customer_name' => $lot->orderMain->customer->name ?? '',
                'lot_no' => $lot->lot_no,
                'lot_quantity' => $quantity ?? 0,
                'status' => $lot->status,
                'is_printing' => $lot->is_printing,
                'is_stitching' => $lot->is_stitching,
                'date' => $lot->created_at->format('d M, Y'),
                'last_current_stage' => getLastCurrentStage($lot->lot_no),
            ];
        });
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

    public function lotDetails($lot_no)
    {

        if (!$lot_no) {
            return null;
        }

        /* ---------------- LOTS DATA ---------------- */
        $lots_data = FabricRollAssigning::with([
            'fabricRollAssigningsDetail',
            'orderProductSet.orderMain.customer',
            'orderProductSet.size_measurement',
            'orderProductSet.colors',
            'orderProductSet.master_product_fitting',
            'orderProductSet.master_design_pattern',
            'orderProductSet.fabric',
            'productionSlipDigitization.fromStage',
            'productionSlipDigitization.getUnitMaster',
        ])
            ->where('lot_no', $lot_no)
            ->select('lot_no', 'order_products_set_id', 'production_slip_digitization_id')
            ->distinct()
            ->get();

        if ($lots_data->isEmpty()) {
            $cuttingStage = \App\Models\OrderCuttingStage::with([
                'orderProductSet.orderMain.customer',
                'orderProductSet.size_measurement',
                'orderProductSet.colors',
                'orderProductSet.master_product_fitting',
                'orderProductSet.master_design_pattern',
                'orderProductSet.fabric'
            ])->where('lot_no', $lot_no)->first();
            
            if ($cuttingStage && $cuttingStage->orderProductSet) {
                $lots_data = collect([
                    (object)[
                        'lot_no' => $lot_no,
                        'order_products_set_id' => $cuttingStage->set_product_id,
                        'production_slip_digitization_id' => null,
                        'orderProductSet' => $cuttingStage->orderProductSet
                    ]
                ]);
            } else {
                return null;
            }
        }

        /* ---------------- ROLLS DATA ---------------- */
        $rolls_data = FabricRollAssigning::with(['fabricRollAssigningsDetail', 'fabricReceiptDetail.fabric'])->where('lot_no', $lot_no)
            ->select('id', 'roll_no', 'meter', 'fabric_receipt_detail_id')
            ->get();

        /* ---------------- ALL SESSIONS DATA ---------------- */
        $lots = \App\Models\OrderLot::where('lot_no', $lot_no)
            ->with([
                'orderMain',
                'orderProductSet.fabric',
                'orderProductSet.colors',
                'orderProductSet.master_design_pattern',
                'orderProductSet.master_product_fitting',
            ])->get();

        $printings = \App\Models\OrderPrintingStageTransaction::where('lot_no', $lot_no)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $stage_tx = \App\Models\OrderStageTransaction::where('lot_no', $lot_no)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $printing_to_stitching_tx = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $lot_no)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $godam_tx = \App\Models\OrderGodamStageTransaction::where('lot_no', $lot_no)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'godamDetails'
            ])->get();

        $godam_tx->each(function($tx) {
            $tx->details = $tx->godamDetails;
        });

        $stage_transactions = $stage_tx->concat($printing_to_stitching_tx)->concat($godam_tx);
        
        $rolls = \App\Models\FabricRollAssigning::with(['fabricRollAssigningsDetail', 'stageMasterUnit.masterFabricWarehouse'])
            ->where('lot_no', $lot_no)
            ->get();

        return [
            // 'order'      => $order,
            'lot_no' => $lot_no,
            'lots_data' => $lots_data,
            'rolls_data' => $rolls_data,
            'last_current_stage' => getLastCurrentStage($lot_no),
            'lots' => $lots,
            'printings' => $printings,
            'stage_transactions' => $stage_transactions,
            'rolls' => $rolls
        ];
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
                'lot_no' => $lot->lot_no,
            ];
        });
        return $result;
    }

    public function customers()
    {
        $data = MasterCustomer::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function master_stages()
    {
        $data = MasterProductStage::where('status', 1)->whereNotIn('id', [3, 12])->orderBy('sequence', 'asc')->get();
        return $data;
    }

    public function stockPending(Request $request)
    {
        // Re-use the unit assignments logic but we can customize if needed later
        // The view will handle displaying only the required columns.
        return $this->unitAssignments($request);
    }

    public function unitAssignments(Request $request)
    {
        $assignments = [];
        $type = '';
        $productionStatus = $request->get('production_status');

        $unitIdsReq = $request->get('unit_id', []);
        if (!is_array($unitIdsReq) && $unitIdsReq) {
            $unitIdsReq = [$unitIdsReq];
        } elseif (!is_array($unitIdsReq)) {
            $unitIdsReq = [];
        }
        
        $lotNo = $request->get('lot_no');
        $orderNo = $request->get('order_no');
        $designNoReq = $request->get('design_no', []);
        if (!is_array($designNoReq) && $designNoReq) {
            $designNoReq = [$designNoReq];
        } elseif (!is_array($designNoReq)) {
            $designNoReq = [];
        }

        if ($productionStatus) {
            $type = 'other';
            $query = \App\Models\OrderLot::with(['orderMain.customer', 'orderProductSet.colors', 'orderProductSet.master_design_pattern', 'orderProductSet.order_cutting_stage.cutting_master'])
                ->orderBy('id', 'asc');

            if ($productionStatus === 'not_printing') {
                $query->where('is_printing', 0);
            } elseif ($productionStatus === 'not_stitching') {
                $query->where('is_stitching', 0);
            } elseif ($productionStatus === 'not_both') {
                $query->where('is_printing', 0)->where('is_stitching', 0);
            } elseif ($productionStatus === 'printing_sent_stitching_pending') {
                $query->where('is_printing', 1)->where('is_stitching', 0);
            }

            if ($request->filled('lot_no')) {
                $query->where('lot_no', $request->lot_no);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            if ($orderNo) {
                $query->whereHas('orderMain', function ($q) use ($orderNo) {
                    $q->where('sku', $orderNo);
                });
            }
            
            if (!empty($designNoReq)) {
                $query->whereHas('orderProductSet', function ($q) use ($designNoReq) {
                    $q->whereIn('design_number', $designNoReq);
                });
            }

            if (!empty($unitIdsReq)) {
                $query->whereHas('orderProductSet.order_cutting_stage', function ($q) use ($unitIdsReq) {
                    $q->whereIn('to_assign_id', $unitIdsReq);
                });
            }

            $records = $query->get();

            foreach ($records as $item) {
                $item->transaction_type = 'cutting_lot';
                $item->design_number = $item->orderProductSet->design_number ?? '-';
                $item->size_set_name = $item->orderProductSet->size_set_name ?? '-';
                $item->stage_master_unit = $item->orderProductSet->order_cutting_stage->cutting_master ?? null;
                $item->order_type = ucfirst($item->orderMain->order_type ?? '-');

                $rolls = \App\Models\FabricRollAssigning::where('order_lot_id', $item->id)->with('fabricRollAssigningsDetail')->get();
                $totalPieces = 0;
                foreach ($rolls as $roll) {
                    $totalPieces += $roll->fabricRollAssigningsDetail->sum('quantity');
                }

                $item->assigned_qty = $totalPieces;
                $item->pending_qty = $totalPieces;
                $item->to_stage = (object) ['name' => 'Cutting'];
                $item->from_stage = (object) ['name' => 'Admin'];
                $item->status_text = ($item->is_printing && $item->is_stitching) ? 'Done' : 'Pending';
                $item->status_class = ($item->is_printing && $item->is_stitching) ? 'success' : 'warning';
                $item->production_date = $item->production_datetime ? \Carbon\Carbon::parse($item->production_datetime)->format('j M Y') : \Carbon\Carbon::parse($item->created_at)->format('j M Y');
                $item->created_at = \Carbon\Carbon::parse($item->created_at);

                $assignments[] = $item;
            }

            $designs = \App\Models\OrderProductSet::whereNotNull('design_number')
                        ->where('design_number', '!=', '')
                        ->select('design_number')
                        ->distinct()
                        ->orderBy('design_number')
                        ->pluck('design_number');

            return [
                'assignments' => collect($assignments)->sortBy('id')->values(),
                'type' => $type,
                'view' => 'open',
                'canCloseTasks' => false,
                'stages' => \App\Models\MasterProductStage::where('status', 1)->orderBy('sequence', 'asc')->get(),
                'units' => \App\Models\StageMasterUnit::where('master_stage_id', 3)->where('status', 1)->get(),
                'designs' => $designs,
                'selectedStage' => '',
                'selectedUnit' => $unitIdsReq,
                'lotNo' => $request->get('lot_no'),
                'orderNo' => $request->get('order_no'),
                'productionStatus' => $productionStatus
            ];
        }

        $stageId = $request->get('stage_id');
        $view = $request->get('view', 'open');
        if (!in_array($view, ['open', 'closed', 'delayed', 'all'])) {
            $view = 'open';
        }

        $lotNo = $request->get('lot_no');

        $isCutting = $stageId == 3;
        $isPacking = $stageId == 11;
        $canCloseTasks = $isCutting || $isPacking;

        $unitIds = [];
        if (!empty($unitIdsReq)) {
            $selectedUnits = \App\Models\StageMasterUnit::whereIn('id', $unitIdsReq)->get();
            $unitIds = $unitIdsReq;
            foreach ($selectedUnits as $su) {
                $matching = \App\Models\StageMasterUnit::where('name', $su->name)
                    ->where('master_stage_id', $su->master_stage_id)
                    ->pluck('id')
                    ->toArray();
                $unitIds = array_merge($unitIds, $matching);
            }
            $unitIds = array_unique($unitIds);
        }

        $hasFilters = $lotNo || $orderNo || !empty($designNoReq) || $request->filled('start_date') || $request->filled('end_date') || !empty($unitIdsReq);
        $type = 'other';

        if ($request->get('is_pagination') && \Illuminate\Support\Facades\Cache::has('stock_pending_data_' . md5(json_encode($request->except(['page', 'is_pagination', '_']))))) {
            $cached = \Illuminate\Support\Facades\Cache::get('stock_pending_data_' . md5(json_encode($request->except(['page', 'is_pagination', '_']))));
            $assignments = $cached['assignments'];
            $type = $cached['type'];
        } else {
            // When All Stages is selected, we exclude Cutting (stage 3), Printing (4), and Embroidery (5) etc.
            if ($stageId == 3) {
                $type = 'cutting';

            $query = \App\Models\OrderCuttingStage::with(['orderMain.customer', 'productSet.fabric', 'productSet.colors', 'productSet.master_design_pattern', 'cutting_master'])
                ->where('is_po', 0)
                ->where('to_assign_id', '>', 0)
                ->orderBy('id', 'asc');

            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            if (!empty($unitIds)) {
                $query->whereIn('to_assign_id', $unitIds);
            }

            if ($lotNo) {
                $query->where('lot_no', $lotNo);
            }

            if (!empty($designNoReq)) {
                $query->whereHas('productSet', function ($q) use ($designNoReq) {
                    $q->whereIn('design_number', $designNoReq);
                });
            }

            if ($orderNo) {
                $query->whereHas('orderMain', function ($q) use ($orderNo) {
                    $q->where('sku', $orderNo);
                });
            }

            $records = $query->get();

            foreach ($records as $item) {
                $eta = $item->end_date;
                $assignedQty = $item->quantity;
                $pendingQty = min((int) $item->remaining_quantity, (int) $item->quantity);
                $item->order_type = ucfirst($item->orderMain->order_type ?? '-');
                
                $isClosed = ($item->is_closed_for_unit == 1);

                // Status Logic
                if ($pendingQty <= 0) {
                    $item->status_text = 'Done';
                    $item->status_class = 'success';
                    $endTime = $item->complete_date ?? $item->updated_at;
                    if ($eta && \Carbon\Carbon::parse($endTime)->gt($eta)) {
                        $item->status_text = 'Delayed Done';
                        $item->status_class = 'danger';
                    }
                } elseif ($eta && now()->gt($eta)) {
                    $item->status_text = 'Delayed';
                    $item->status_class = 'danger';
                } else {
                    $item->status_text = 'Pending';
                    $item->status_class = 'warning';
                }

                if ($view === 'closed' && !($item->status_text === 'Done' || $item->status_text === 'Delayed Done')) {
                    continue;
                }
                if ($view === 'open' && ($item->status_text === 'Done' || $item->status_text === 'Delayed Done')) {
                    continue;
                }
                if ($view === 'delayed' && $item->status_text !== 'Delayed') {
                    continue;
                }

                // Map for View
                $item->design_number = $item->productSet->design_number ?? '-';
                $item->size_set_name = $item->productSet->size_set_name ?? '-';
                $item->stage_master_unit = $item->cutting_master;
                $item->start_time = $item->start_date ? \Carbon\Carbon::parse($item->start_date) : $item->created_at;
                $item->end_time = ($isClosed || $pendingQty <= 0)
                    ? ($item->complete_date ? \Carbon\Carbon::parse($item->complete_date) : $item->updated_at)
                    : null;
                $item->estimated_time = $eta ? \Carbon\Carbon::parse($eta) : null;
                $item->assigned_qty = $assignedQty;
                $item->pending_qty = $pendingQty;

                $assignments[] = $item;
            }
        } // Close if ($stageId == 3)

        if (!$stageId || $stageId != 3) {

            if ($stageId == 13) {
                $ass1Query = \App\Models\OrderGodamStageTransaction::with(['from_stage', 'to_stage', 'getFromUnitMaster', 'getToUnitMaster']);
            } else {
                $ass1Query = \App\Models\OrderStageTransaction::with(['from_stage', 'to_stage', 'getFromUnitMaster', 'getToUnitMaster']);
            }
            $ass2Query = \App\Models\OrderPrintingStageTransaction::with(['from_stage', 'to_stage', 'getFromUnitMaster', 'getToUnitMaster']);
            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::with(['from_stage', 'to_stage', 'getFromUnitMaster', 'getToUnitMaster']);

            if ($stageId == 4) {
                $ass1Query->where(function ($q) {
                    $q->where('from_stage_id', '!=', 1)->orWhereNull('from_stage_id');
                });
                $ass2Query->where(function ($q) {
                    $q->where('from_stage_id', '!=', 1)->orWhereNull('from_stage_id');
                });
                $ass3Query->where(function ($q) {
                    $q->where('from_stage_id', '!=', 1)->orWhereNull('from_stage_id');
                });
            }

            $stageFilter = function ($q) use ($stageId) {
                if ($stageId) {
                    $q->where('to_stage_id', $stageId);
                } else {
                    $excludedStages = \App\Models\MasterProductStage::whereIn('name', ['Cutting', 'Printing', 'Embroidery', 'Printing & Embroidery'])->pluck('id')->toArray();
                    $q->whereNotIn('to_stage_id', $excludedStages);
                }
            };

            if (!empty($unitIds)) {
                $ass1Query->whereIn('sub_stage_id_to', $unitIds);
                $ass2Query->whereIn('sub_stage_id_to', $unitIds);
                $ass3Query->whereIn('sub_stage_id_to', $unitIds);
            } else {
                $ass1Query->where($stageFilter);
                $ass2Query->where($stageFilter);
                $ass3Query->where($stageFilter);
            }

            if ($lotNo) {
                $ass1Query->where('lot_no', $lotNo);
                $ass2Query->where('lot_no', $lotNo);
                $ass3Query->where('lot_no', $lotNo);
            }

            if ($orderNo) {
                $orderFilter = function ($q) use ($orderNo) {
                    $lotNos = \App\Models\OrderLot::whereHas('orderMain', function ($sq) use ($orderNo) {
                        $sq->where('sku', $orderNo);
                    })->pluck('lot_no')->toArray();
                    $q->whereIn('lot_no', $lotNos);
                };
                $ass1Query->where($orderFilter);
                $ass2Query->where($orderFilter);
                $ass3Query->where($orderFilter);
            }

            if (!empty($designNoReq)) {
                $designFilter = function ($q) use ($designNoReq) {
                    $lotNos = \App\Models\OrderLot::whereHas('orderProductSet', function ($sq) use ($designNoReq) {
                        $sq->whereIn('design_number', $designNoReq);
                    })->pluck('lot_no')->toArray();
                    $q->whereIn('lot_no', $lotNos);
                };
                $ass1Query->where($designFilter);
                $ass2Query->where($designFilter);
                $ass3Query->where($designFilter);
            }

            if ($request->filled('start_date')) {
                $ass1Query->whereDate('created_at', '>=', $request->start_date);
                $ass2Query->whereDate('created_at', '>=', $request->start_date);
                $ass3Query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $ass1Query->whereDate('created_at', '<=', $request->end_date);
                $ass2Query->whereDate('created_at', '<=', $request->end_date);
                $ass3Query->whereDate('created_at', '<=', $request->end_date);
            }

            $results1 = $ass1Query->orderBy('id', 'asc')->get()->map(function ($item) {
                $item->transaction_type = 'stage';
                return $item;
            });
            $results2 = $ass2Query->orderBy('id', 'asc')->get()->map(function ($item) {
                $item->transaction_type = 'printing';
                return $item;
            });
            $results3 = $ass3Query->orderBy('id', 'asc')->get()->map(function ($item) {
                $item->transaction_type = 'printing_to_stitching';
                return $item;
            });

            $allTransactions = $results1->merge($results2)->merge($results3);

            $groupedAssignments = [];
            
            $lotNos = $allTransactions->pluck('lot_no')->unique()->toArray();
            
            // Fetch bulk sums for incoming
            $in1Sums = \App\Models\OrderStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, to_stage_id, sub_stage_id_to, SUM(quantity) as sum_qty')->groupBy('lot_no', 'to_stage_id', 'sub_stage_id_to')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id_to => $i->sum_qty]; })->toArray();
            $in2Sums = \App\Models\OrderGodamStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, to_stage_id, sub_stage_id_to, SUM(quantity) as sum_qty')->groupBy('lot_no', 'to_stage_id', 'sub_stage_id_to')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id_to => $i->sum_qty]; })->toArray();
            $in3Sums = \App\Models\OrderPrintingStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, to_stage_id, sub_stage_id_to, SUM(quantity) as sum_qty')->groupBy('lot_no', 'to_stage_id', 'sub_stage_id_to')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id_to => $i->sum_qty]; })->toArray();
            $in4Sums = \App\Models\OrderPrintingToStichingTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, to_stage_id, sub_stage_id_to, SUM(quantity) as sum_qty')->groupBy('lot_no', 'to_stage_id', 'sub_stage_id_to')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id_to => $i->sum_qty]; })->toArray();

            // Fetch bulk sums for outflow
            $out1Sums = \App\Models\OrderStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, from_stage_id, sub_stage_id, SUM(quantity) as sum_qty')->groupBy('lot_no', 'from_stage_id', 'sub_stage_id')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id => $i->sum_qty]; })->toArray();
            $out2Sums = \App\Models\OrderGodamStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, from_stage_id, sub_stage_id, SUM(quantity) as sum_qty')->groupBy('lot_no', 'from_stage_id', 'sub_stage_id')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id => $i->sum_qty]; })->toArray();
            $out3Sums = \App\Models\OrderPrintingStageTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, from_stage_id, sub_stage_id, SUM(quantity) as sum_qty')->groupBy('lot_no', 'from_stage_id', 'sub_stage_id')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id => $i->sum_qty]; })->toArray();
            $out4Sums = \App\Models\OrderPrintingToStichingTransaction::whereIn('lot_no', $lotNos)
                ->selectRaw('lot_no, from_stage_id, sub_stage_id, SUM(quantity) as sum_qty')->groupBy('lot_no', 'from_stage_id', 'sub_stage_id')
                ->get()->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->sub_stage_id => $i->sum_qty]; })->toArray();

            // Fetch bulk sums for packing (stage 11 completed work) grouped by lot and unit
            $packedSums = \App\Models\PackingItem::join('packing_mains', 'packing_items.packing_main_id', '=', 'packing_mains.id')
                ->join('production_slip_digitization', 'packing_mains.slip_id', '=', 'production_slip_digitization.id')
                ->whereIn('packing_items.lot_no', $lotNos)
                ->selectRaw('packing_items.lot_no, production_slip_digitization.stage_master_unit_id, SUM(packing_items.quantity) as sum_qty')
                ->groupBy('packing_items.lot_no', 'production_slip_digitization.stage_master_unit_id')
                ->get()
                ->mapWithKeys(function ($i) { return [$i->lot_no . '_' . $i->stage_master_unit_id => $i->sum_qty]; })
                ->toArray();

            $orderLots = \App\Models\OrderLot::with('orderProductSet.orderMain')
                ->whereIn('lot_no', $lotNos)->get()->keyBy('lot_no');
                
            $timings = \App\Models\OrderLotStageTiming::whereIn('lot_no', $lotNos)
                ->get()->groupBy('lot_no');
                
            $timeTrackings = \App\Models\OrderStageWiseTimeTracking::whereIn('lot_no', $lotNos)
                ->get()->keyBy('lot_no');
                
            $stageMasterUnits = \App\Models\StageMasterUnit::all()->keyBy('id');

            foreach ($allTransactions as $item) {
                $item->to_stage_name = $item->to_stage->name ?? 'Unknown';
                if ($stageId && $item->to_stage_name != \App\Models\MasterProductStage::find($stageId)->name) {
                    continue;
                }
                
                $t_stage_id = $item->to_stage_id;
                
                $item->stage_master_unit = $stageMasterUnits[$item->sub_stage_id_to] ?? null;

                $orderLot = $orderLots[$item->lot_no] ?? null;
                $item->design_number = $orderLot?->orderProductSet?->design_number ?? '-';
                $item->size_set_name = $orderLot?->orderProductSet?->size_set_name ?? '-';
                $item->sku = $orderLot?->orderProductSet?->orderMain?->sku ?? '-';
                $item->order_type = ucfirst($orderLot?->orderProductSet?->orderMain?->order_type ?? '-');

                // Fetch Unified Timing
                $lotTimings = $timings[$item->lot_no] ?? collect();
                $timing = $lotTimings->firstWhere('master_stage_id', $t_stage_id);

                $column_namevar = 'stage_id_' . $t_stage_id;
                $timeTracking = $timeTrackings[$item->lot_no] ?? null;
                $eta = $timing?->end_date ?? ($item->end_date ?? ($timeTracking && isset($timeTracking->$column_namevar) ? \Carbon\Carbon::parse($timeTracking->$column_namevar) : null));

                $assignedQty = $item->quantity;
                
                // Dynamic pending quantity calculation for the unit
                $inKey = $item->lot_no . '_' . $item->sub_stage_id_to;
                $outKey = $item->lot_no . '_' . $item->sub_stage_id_to;

                $in1 = $in1Sums[$inKey] ?? 0;
                $in2 = $in2Sums[$inKey] ?? 0;
                $in3 = $in3Sums[$inKey] ?? 0;
                
                // Exclude in4 from incoming to prevent double counting per user instructions
                $incomingAll = $in1 + $in2 + $in3;
                
                $out1 = $out1Sums[$outKey] ?? 0;
                $out2 = $out2Sums[$outKey] ?? 0;
                $out3 = $out3Sums[$outKey] ?? 0;
                $out4 = $out4Sums[$outKey] ?? 0;
                
                $outflowAll = $out1 + $out2 + $out3 + $out4;
                
                // Add packed items to outflow if stage is Packing (11)
                if ($t_stage_id == 11) {
                    $packedKey = $item->lot_no . '_' . $item->sub_stage_id_to;
                    $packedQty = $packedSums[$packedKey] ?? 0;
                    $outflowAll += $packedQty;
                }
                
                $pendingQty = max(0, $incomingAll - $outflowAll);

                $isClosed = ($item->is_closed_for_unit == 1);

                // Status Logic
                if ($pendingQty <= 0) {
                    $item->status_text = 'Done';
                    $item->status_class = 'success';
                    $endTime = $timing?->complete_date ?? ($item->complete_date ?? $item->updated_at);
                    if ($eta && \Carbon\Carbon::parse($endTime)->gt($eta)) {
                        $item->status_text = 'Delayed Done';
                        $item->status_class = 'danger';
                    }
                } elseif ($eta && now()->gt($eta)) {
                    $item->status_text = 'Delayed';
                    $item->status_class = 'danger';
                } else {
                    $item->status_text = 'Pending';
                    $item->status_class = 'warning';
                }

                if ($view === 'closed' && !($item->status_text === 'Done' || $item->status_text === 'Delayed Done')) {
                    continue;
                }
                if ($view === 'open' && ($item->status_text === 'Done' || $item->status_text === 'Delayed Done')) {
                    continue;
                }
                if ($view === 'delayed' && $item->status_text !== 'Delayed') {
                    continue;
                }

                $item->start_time = $timing?->start_date ? \Carbon\Carbon::parse($timing->start_date) : ($item->start_date ? \Carbon\Carbon::parse($item->start_date) : $item->created_at);
                $item->end_time = ($isClosed || $pendingQty <= 0)
                    ? ($timing?->complete_date ? \Carbon\Carbon::parse($timing->complete_date) : ($item->complete_date ? \Carbon\Carbon::parse($item->complete_date) : $item->updated_at))
                    : null;
                $item->estimated_time = $eta ? \Carbon\Carbon::parse($eta) : null;

                $groupKey = $item->lot_no . '_' . $t_stage_id . '_' . $item->sub_stage_id_to;
                if (!isset($groupedAssignments[$groupKey])) {
                    $item->assigned_qty = $assignedQty;
                    $item->pending_qty = $pendingQty;
                    $groupedAssignments[$groupKey] = $item;
                } else {
                    $groupedAssignments[$groupKey]->assigned_qty += $assignedQty;
                    $groupedAssignments[$groupKey]->pending_qty = $pendingQty;
                }
            }
            $assignmentsOther = collect(array_values($groupedAssignments))->sortBy('id')->values()->all();
            $assignments = array_merge($assignments, $assignmentsOther);
        } // Close if (!$stageId || $stageId != 3)
        
        if ($request->get('is_pagination') && !isset($cached)) {
            \Illuminate\Support\Facades\Cache::put('stock_pending_data_' . md5(json_encode($request->except(['page', 'is_pagination', '_']))), [
                'assignments' => $assignments,
                'type' => $type
            ], 300);
        }

        } // Close else {

        $stages = \App\Models\MasterProductStage::where('status', 1)->orderBy('sequence', 'asc')->get();
        // Return only unit persons for the selected stage, or all if none
        $unitsQuery = \App\Models\StageMasterUnit::where('status', 1);
        if ($stageId) {
            $unitsQuery->where('master_stage_id', $stageId);
        }
        $units = $unitsQuery->get();
        
        $totalPending = 0;
        foreach ($assignments as $item) {
            $qty = $item->pending_qty ?? $item->quantity ?? 0;
            $totalPending += $qty;
        }

        $assignmentsCollection = collect($assignments)->sortBy('id')->values();

        if ($request->get('is_pagination')) {
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $perPage = 20;
            $assignmentsCollection = new \Illuminate\Pagination\LengthAwarePaginator(
                $assignmentsCollection->forPage($page, $perPage),
                $assignmentsCollection->count(),
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }

        $designs = \App\Models\OrderProductSet::whereNotNull('design_number')
                    ->where('design_number', '!=', '')
                    ->select('design_number')
                    ->distinct()
                    ->orderBy('design_number')
                    ->pluck('design_number');

        return [
            'assignments' => $assignmentsCollection,
            'totalPending' => $totalPending,
            'type' => $type,
            'view' => $view,
            'canCloseTasks' => $canCloseTasks,
            'stages' => $stages,
            'units' => $units,
            'designs' => $designs,
            'selectedStage' => $stageId,
            'selectedUnit' => $unitIdsReq,
            'lotNo' => $lotNo,
            'orderNo' => $orderNo,
            'productionStatus' => $productionStatus
        ];
    }

    public function closeUnitAssignment($type, $id)
    {
        $record = $this->findAssignmentRecordForAdmin($type, $id);
        if ($record) {
            $record->is_closed_for_unit = 1;
            $record->complete_date = now();
            $record->save();
            return true;
        }
        return false;
    }

    public function reopenUnitAssignment($type, $id)
    {
        $record = $this->findAssignmentRecordForAdmin($type, $id);
        if ($record) {
            $record->is_closed_for_unit = 0;
            $record->save();
            return true;
        }
        return false;
    }

    protected function findAssignmentRecordForAdmin(string $type, int $id)
    {
        switch ($type) {
            case 'cutting':
                return OrderProductSet::find($id);
            case 'stage':
                return \App\Models\OrderStageTransaction::find($id);
            case 'printing':
                return \App\Models\OrderPrintingStageTransaction::find($id);
            case 'printing_to_stitching':
                return \App\Models\OrderPrintingToStichingTransaction::find($id);
            default:
                return null;
        }
    }

    public function designWipApiCustomers(Request $request)
    {
        $query = \App\Models\MasterCustomer::whereHas('orders', function($q) {
            $q->whereHas('OrderProductSets');
        });

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->orderBy('name')->paginate(20, ['id', 'name']);
        
        return [
            'status' => true, 
            'data' => $customers->items(), 
            'has_more' => $customers->hasMorePages()
        ];
    }

    public function designWipApiOrders(Request $request)
    {
        $query = \App\Models\OrderMain::whereHas('OrderProductSets');

        if ($request->filled('customer_id')) {
            $query->where('master_customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', '%' . $search . '%')
                  ->orWhere('po_number', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(20, ['id', 'sku', 'po_number']);
        
        return [
            'status' => true, 
            'data' => $orders->items(), 
            'has_more' => $orders->hasMorePages()
        ];
    }

    public function designWipApiDesigns(Request $request)
    {
        $query = \App\Models\OrderProductSet::with('product.series', 'size_measurement', 'colors');

        if ($request->filled('order_id')) {
            $query->where('order_main_id', $request->order_id);
        } elseif ($request->filled('customer_id')) {
            $query->whereHas('orderMain', function($q) use ($request) {
                $q->where('master_customer_id', $request->customer_id);
            });
        }

        if ($request->filled('design_no')) {
            $search = $request->design_no;
            $query->where(function($q) use ($search) {
                $q->where('design_number', 'like', '%' . $search . '%')
                  ->orWhereHas('product', function($q2) use ($search) {
                      $q2->where('name_of_garment', 'like', '%' . $search . '%')
                         ->orWhereHas('series', function($q3) use ($search) {
                             $q3->where('name', 'like', '%' . $search . '%');
                         });
                  });
            });
        }

        $paginator = $query->paginate(20);

        $result = [];
        foreach ($paginator as $set) {
            $productName = '';
            if ($set->product) {
                $series = $set->product->series->name ?? '';
                $garment = $set->product->name_of_garment ?? '';
                $productName = trim($series . ' ' . $garment);
            }

            // Calculate lot count and total actual pieces for this set
            $lots1 = \App\Models\OrderLot::where('order_products_set_id', $set->id)->whereNotNull('lot_no')->pluck('lot_no')->toArray();
            $allLots = array_unique($lots1);
            $unassigned = $set->remain_total_quantity;
            
            $actualTotalQty = $unassigned;
            foreach ($allLots as $lot) {
                $assignings = \App\Models\FabricRollAssigning::where('lot_no', $lot)->pluck('id');
                $q = \App\Models\FabricRollAssigningsDetail::whereIn('production_fabric_roll_assigning_id', $assignings)->sum('quantity');
                if ($q == 0) {
                    $q = \App\Models\OrderCuttingStage::where('lot_no', $lot)->sum('quantity');
                }
                $actualTotalQty += $q;
            }

            if ($unassigned > 0 || empty($allLots)) {
                $allLots[] = 'UNASSIGNED_' . $set->id;
            }
            $allLots = array_unique($allLots);
            $lotCount = count($allLots);

            $sizes = $set->size_set_name !== 'N/A' ? $set->size_set_name : '-';
            $colors = $set->color_name !== 'N/A' ? $set->color_name : '-';

            $result[] = [
                'set_id' => $set->id,
                'design_no' => $set->design_number,
                'product_name' => $productName,
                'lot_count' => $lotCount,
                'total_qty' => $actualTotalQty,
                'sizes' => $sizes,
                'colors' => $colors
            ];
        }

        return [
            'status' => true, 
            'data' => $result,
            'has_more' => $paginator->hasMorePages()
        ];
    }

    public function designWipApiLots(Request $request)
    {
        $setId = $request->set_id;
        $search = $request->filled('search') ? strtolower($request->search) : null;
        
        $allLots = [];
        $unassigned = 0;

        if ($setId) {
            $set = \App\Models\OrderProductSet::find($setId);
            if ($set) {
                // Lots from OrderLot for this specific set
                $lots1 = \App\Models\OrderLot::where('order_products_set_id', $setId)->whereNotNull('lot_no')->pluck('lot_no')->toArray();
                $allLots = array_unique($lots1);

                $unassigned = $set->remain_total_quantity;
                if ($unassigned > 0 || empty($allLots)) {
                    $allLots[] = 'UNASSIGNED_' . $setId;
                }
                
                if ($search) {
                    $allLots = array_filter($allLots, function($lot) use ($search) {
                        return str_contains(strtolower($lot), $search);
                    });
                }
            }
        } else {
            // Global lot search
            $lotQuery = \App\Models\OrderLot::whereNotNull('lot_no')
                                            ->where('lot_no', 'like', '%' . $request->search . '%');
            if ($request->filled('order_id')) {
                $lotQuery->where('order_main_id', $request->order_id);
            } elseif ($request->filled('customer_id')) {
                $lotQuery->whereHas('orderMain', function($q) use ($request) {
                    $q->where('master_customer_id', $request->customer_id);
                });
            }
            $allLots = $lotQuery->pluck('lot_no')->toArray();
        }

        $allLots = array_values(array_unique($allLots));
        
        $perPage = 20;
        $page = $request->input('page', 1);
        $total = count($allLots);
        $offset = ($page - 1) * $perPage;
        
        $pagedLots = array_slice($allLots, $offset, $perPage);
        $hasMore = ($offset + $perPage) < $total;
        
        $result = [];
        foreach ($pagedLots as $lot) {
            $qty = 0;
            if (str_starts_with($lot, 'UNASSIGNED_')) {
                $qty = $unassigned;
            } else {
                $assignings = \App\Models\FabricRollAssigning::where('lot_no', $lot)->pluck('id');
                $qty = \App\Models\FabricRollAssigningsDetail::whereIn('production_fabric_roll_assigning_id', $assignings)->sum('quantity');
                if ($qty == 0) {
                    $qty = \App\Models\OrderCuttingStage::where('lot_no', $lot)->sum('quantity');
                }
            }

            $cuttingMaster = '-';
            $assignDate = '-';
            if (!str_starts_with($lot, 'UNASSIGNED_')) {
                $fabricRollAssigning = \App\Models\FabricRollAssigning::with('productionSlipDigitization.getUnitMaster')->where('lot_no', $lot)->first();
                if ($fabricRollAssigning && $fabricRollAssigning->productionSlipDigitization && $fabricRollAssigning->productionSlipDigitization->getUnitMaster) {
                    $cuttingMaster = $fabricRollAssigning->productionSlipDigitization->getUnitMaster->name;
                    $assignDate = \Carbon\Carbon::parse($fabricRollAssigning->productionSlipDigitization->created_at)->format('d M y');
                } else {
                    $cuttingDetails = getLotDetails($lot, 3);
                    if ($cuttingDetails && !empty($cuttingDetails['unit_name'])) {
                        $cuttingMaster = $cuttingDetails['unit_name'];
                        if (!empty($cuttingDetails['start_date'])) {
                            $assignDate = \Carbon\Carbon::parse($cuttingDetails['start_date'])->format('d M y');
                        } elseif (!empty($cuttingDetails['time_allocation'])) {
                            $assignDate = \Carbon\Carbon::parse($cuttingDetails['time_allocation'])->format('d M y');
                        }
                    }
                }
            }

            $result[] = [
                'lot_no' => $lot,
                'qty' => $qty,
                'cutting_master' => $cuttingMaster,
                'assign_date' => $assignDate
            ];
        }

        return [
            'status' => true, 
            'data' => $result,
            'has_more' => $hasMore
        ];
    }

    public function designWipApiLotDetails(Request $request)
    {
        $lotNo = $request->lot_no;
        $designNo = $request->design_no;
        
        if (!$lotNo) return ['status' => false, 'message' => 'Lot No required'];

        $reportData = [];

        // 1. Pending Cutting
        if ($lotNo === $designNo) {
            $sets = \App\Models\OrderProductSet::with('stage_master_unit')->where('design_number', $designNo)->get();
            $unassigned = $sets->sum('remain_total_quantity');
            if ($unassigned > 0) {
                $groupedCutting = $sets->where('remain_total_quantity', '>', 0)->groupBy('stage_master_unit_id');
                foreach ($groupedCutting as $unitId => $unitSets) {
                    $qty = $unitSets->sum('remain_total_quantity');
                    $unitName = $unitSets->first()->stage_master_unit->name ?? 'Unknown Unit';
                    $reportData[] = [
                        'stage' => 'Pending Cutting Assignment',
                        'location' => $unitName,
                        'quantity' => $qty,
                        'color_class' => 'primary'
                    ];
                }
            }
        }

        // 2. Cutting Tasks
        $cuttingTasks = \App\Models\OrderCuttingStage::with(['cutting_master'])->where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)->get();
        foreach ($cuttingTasks as $ct) {
            $reportData[] = [
                'stage' => 'Cutting',
                'location' => $ct->cutting_master->name ?? 'Unknown Unit',
                'quantity' => $ct->remaining_quantity,
                'color_class' => 'warning'
            ];
        }

        // 3. Stage Transactions
        $stageTransactions = \App\Models\OrderStageTransaction::with(['to_stage', 'getToUnitMaster'])
            ->where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)->get();
        
        foreach ($stageTransactions as $st) {
            $stageName = $st->to_stage->name ?? 'Unknown Stage';
            $unitName = $st->getToUnitMaster->name ?? 'Unknown Unit';
            $reportData[] = [
                'stage' => $stageName,
                'location' => $unitName,
                'quantity' => $st->remaining_quantity,
                'color_class' => 'info'
            ];
        }

        // 4. Printing Transactions
        $printingTx = \App\Models\OrderPrintingStageTransaction::with(['to_stage', 'getToUnitMaster'])
            ->where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)->get();
        foreach ($printingTx as $pt) {
            $stageName = $pt->to_stage->name ?? 'Unknown Stage';
            $unitName = $pt->getToUnitMaster->name ?? 'Unknown Unit';
            $reportData[] = [
                'stage' => $stageName,
                'location' => $unitName,
                'quantity' => $pt->remaining_quantity,
                'color_class' => 'secondary'
            ];
        }

        // 5. Print -> Stitch Transactions
        $psTx = \App\Models\OrderPrintingToStichingTransaction::with(['to_stage', 'getToUnitMaster'])
            ->where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)->get();
        foreach ($psTx as $pst) {
            $stageName = $pst->to_stage->name ?? 'Unknown Stage';
            $unitName = $pst->getToUnitMaster->name ?? 'Unknown Unit';
            $reportData[] = [
                'stage' => $stageName,
                'location' => $unitName,
                'quantity' => $pst->remaining_quantity,
                'color_class' => 'dark'
            ];
        }
        
        // 6. Godam / Inventory
        $godamTx = \App\Models\OrderGodamStageTransaction::where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)->get();
        foreach ($godamTx as $gt) {
            $reportData[] = [
                'stage' => 'Inventory',
                'location' => 'Main Warehouse',
                'quantity' => $gt->remaining_quantity,
                'color_class' => 'success'
            ];
        }

        // If filtering by stage in UI, we can do it in Javascript.

        return ['status' => true, 'data' => $reportData];
    }

    public function designWip(Request $request)

    {
        $designNo = $request->get('design_no');
        $colorId = $request->get('color_id');
        $patternId = $request->get('pattern_id');
        $fittingId = $request->get('fitting_id');

        $query = \App\Models\OrderProductSet::with(['colors', 'master_design_pattern', 'master_product_fitting', 'stage_master_unit']);

        if ($designNo) {
            $query->where('design_number', 'like', '%' . $designNo . '%');
        }
        if ($colorId) {
            $query->where('color_id', $colorId);
        }
        if ($patternId) {
            $query->where('master_design_pattern_id', $patternId);
        }
        if ($fittingId) {
            $query->where('master_product_fitting_id', $fittingId);
        }

        $perPage = 10;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;

        // Fetch just the distinct design numbers to paginate them, rather than fetching all product sets
        $baseQuery = clone $query;
        $filteredDesignNumbers = $baseQuery->select('design_number')->distinct()->pluck('design_number');

        $total = $filteredDesignNumbers->count();
        $paginatedDesignNumbers = $filteredDesignNumbers->slice(($page - 1) * $perPage, $perPage)->values();

        $setsQuery = clone $query;
        $sets = $setsQuery->whereIn('design_number', $paginatedDesignNumbers)->get()->groupBy('design_number');

        $reportData = [];

        foreach ($paginatedDesignNumbers as $designNumber) {
            if (!$sets->has($designNumber))
                continue;
            $designSets = $sets->get($designNumber);

            $firstSet = $designSets->first();
            $itemBase = [
                'design_no' => $designNumber,
                'color' => $firstSet->colors->name ?? '-',
                'pattern' => $firstSet->master_design_pattern->name ?? '-',
                'fitting' => $firstSet->master_product_fitting->name ?? '-',
            ];

            // 1. Cutting Tasks
            $cuttingPending = $designSets->sum('remain_total_quantity');
            if ($cuttingPending > 0) {
                // Group by unit person
                $groupedCutting = $designSets->where('remain_total_quantity', '>', 0)->groupBy('stage_master_unit_id');
                foreach ($groupedCutting as $unitId => $unitSets) {
                    $qty = $unitSets->sum('remain_total_quantity');
                    $unitName = $unitSets->first()->stage_master_unit->name ?? 'Unknown Unit';
                    $itemStage = 'Cutting';
                    if ($request->filled('stage') && $request->stage !== $itemStage) {
                        continue;
                    }

                    $reportData[] = array_merge($itemBase, [
                        'stage' => $itemStage,
                        'location' => $unitName,
                        'quantity' => $qty
                    ]);
                }
            }

            // 2. Other Stages
            $stageTransactions = \App\Models\OrderStageTransaction::with(['to_stage', 'getToUnitMaster'])
                ->where('lot_no', $designNumber)
                ->where('remaining_quantity', '>', 0)
                ->get();
            $printingTransactions = \App\Models\OrderPrintingStageTransaction::with(['to_stage', 'getToUnitMaster'])
                ->where('lot_no', $designNumber)
                ->where('remaining_quantity', '>', 0)
                ->get();
            $printToStitchTransactions = \App\Models\OrderPrintingToStichingTransaction::with(['to_stage', 'getToUnitMaster'])
                ->where('lot_no', $designNumber)
                ->where('remaining_quantity', '>', 0)
                ->get();

            $allTransactions = $stageTransactions->concat($printingTransactions)->concat($printToStitchTransactions);

            // Group by Stage AND Unit Person
            $groupedTransactions = $allTransactions->groupBy(function ($item) {
                return $item->to_stage_id . '_' . $item->sub_stage_id_to;
            });

            foreach ($groupedTransactions as $groupKey => $transGroup) {
                $firstTrans = $transGroup->first();
                $qty = $transGroup->sum('remaining_quantity');
                $stageName = $firstTrans->to_stage->name ?? 'Unknown Stage';
                $unitName = $firstTrans->getToUnitMaster->name ?? 'Unknown Unit';

                $itemStage = $stageName;
                if ($request->filled('stage') && $request->stage !== $itemStage) {
                    continue;
                }

                $reportData[] = array_merge($itemBase, [
                    'stage' => $itemStage,
                    'location' => $unitName,
                    'quantity' => $qty
                ]);
            }

            // 3. Domestic Inventory
            $inventoryQty = \App\Models\DomesticInventory::whereHas('product', function ($q) use ($designNumber) {
                $q->where('design_number', $designNumber);
            })->sum('quantity');

            if ($inventoryQty > 0) {
                $itemStage = 'Inventory';
                if ($request->filled('stage') && $request->stage !== $itemStage) {
                    continue;
                }

                $reportData[] = array_merge($itemBase, [
                    'stage' => $itemStage,
                    'location' => 'Main Warehouse',
                    'quantity' => $inventoryQty
                ]);
            }
        }

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($reportData),
            $total,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $paginator->appends($request->all());

        $colors = \App\Models\MasterColor::where('status', 1)->get();
        $patterns = \App\Models\MasterDesignPattern::where('status', 1)->get();
        $fittings = \App\Models\MasterProductFitting::where('status', 1)->get();

        // Dynamically get available stages for filter
        $stages = collect(['Cutting', 'Inventory']);
        $masterStages = \App\Models\MasterProductStage::where('status', 1)->pluck('name');
        $stages = $stages->concat($masterStages)->unique()->sort()->values();

        return [
            'reportData' => $paginator,
            'colors' => $colors,
            'patterns' => $patterns,
            'fittings' => $fittings,
            'stages' => $stages,
            'filters' => $request->all(),
        ];
    }

    public function outflowsReport(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\ProductionOutflowInventory::with([
            'product', 'color', 'size', 'responsibleUnit', 'slip'
        ])
        ->where('type', '!=', 'packing_divert')
        ->where('status', 'active')
        ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('lot_no')) {
            $query->where('lot_no', 'LIKE', '%' . $request->lot_no . '%');
        }
        if ($request->filled('slip_id')) {
            $query->where('slip_id', $request->slip_id);
        }
        if ($request->filled('unit_id')) {
            $query->where('responsible_unit_id', $request->unit_id);
        }
        if ($request->filled('design_no')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('design_number', 'LIKE', '%' . $request->design_no . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $totalQuantity = $query->sum('quantity');

        if ($request->get('is_pagination', true)) {
            $outflows = $query->paginate(25);
            $outflows->appends($request->all());
        } else {
            $outflows = $query->get();
        }

        $units = \App\Models\StageMasterUnit::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $designs = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->orderBy('design_number')
            ->pluck('design_number')
            ->unique()
            ->values();
        $masters = \App\Models\AdjustmentMaster::where('status', 1)->get();

        return [
            'outflows' => $outflows,
            'totalQuantity' => $totalQuantity,
            'units' => $units,
            'designs' => $designs,
            'filters' => $request->all(),
            'masters' => $masters
        ];
    }

    public function outflowsDisposeHistoryReport(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\ProductionOutflowInventory::with([
            'product', 'color', 'size', 'responsibleUnit', 'slip', 'disposeHistory.creator'
        ])
        ->where('type', '!=', 'packing_divert')
        ->where('status', 'disposed')
        ->orderBy('updated_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('lot_no')) {
            $query->where('lot_no', 'LIKE', '%' . $request->lot_no . '%');
        }
        if ($request->filled('slip_id')) {
            $query->where('slip_id', $request->slip_id);
        }
        if ($request->filled('unit_id')) {
            $query->where('responsible_unit_id', $request->unit_id);
        }
        if ($request->filled('design_no')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('design_number', 'LIKE', '%' . $request->design_no . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('updated_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('updated_at', '<=', $request->end_date);
        }

        $totalQuantity = $query->sum('quantity');

        if ($request->get('is_pagination', true)) {
            $outflows = $query->paginate(25);
            $outflows->appends($request->all());
        } else {
            $outflows = $query->get();
        }

        $units = \App\Models\StageMasterUnit::whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $designs = \App\Models\ProductionGoods::where('status', 1)
            ->whereNotNull('design_number')
            ->orderBy('design_number')
            ->distinct()
            ->pluck('design_number');

        return [
            'outflows' => $outflows,
            'totalQuantity' => $totalQuantity,
            'units' => $units,
            'designs' => $designs,
            'filters' => $request->all()
        ];
    }
}
