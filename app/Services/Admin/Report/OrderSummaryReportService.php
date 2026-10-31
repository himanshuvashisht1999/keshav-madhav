<?php

namespace App\Services\Admin\Report;

use App\Models\OrderMain;
use App\Models\OrderStage;
use App\Models\PackingCarton;
use App\Models\OrderDispatch;
use Yajra\DataTables\Facades\DataTables;

class OrderSummaryReportService
{
    public function indexList($request)
    {
        $query = OrderMain::with(['customer', 'OrderProductSets.season', 'orderLots'])
            ->select('order_main.*')->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($q) use ($request) {
                if ($request->filled('order_no')) {
                    $q->where('sku', 'like', '%' . $request->order_no . '%');
                }
                if ($request->filled('po_number')) {
                    $q->where('po_number', 'like', '%' . $request->po_number . '%');
                }
                if ($request->filled('design_number')) {
                    $q->whereHas('OrderProductSets', function($sq) use ($request) {
                        $sq->where('design_number', 'like', '%' . $request->design_number . '%');
                    });
                }
                if ($request->filled('lot_no')) {
                    $lotNo = trim($request->lot_no);
                    $q->where(function ($lq) use ($lotNo) {
                        $lq->whereHas('orderLots', function ($olq) use ($lotNo) {
                            $olq->where('lot_no', 'like', '%' . $lotNo . '%');
                        })
                        ->orWhereIn('sku', function ($frq) use ($lotNo) {
                            $frq->select('order_no')->from('production_fabric_roll_assigning')
                                ->where('lot_no', 'like', '%' . $lotNo . '%');
                        })
                        ->orWhereHas('OrderProductSets', function ($osq) use ($lotNo) {
                            $osq->whereIn('id', function ($frq2) use ($lotNo) {
                                $frq2->select('order_products_set_id')->from('production_fabric_roll_assigning')
                                    ->where('lot_no', 'like', '%' . $lotNo . '%');
                            });
                        });
                    });
                }
                if ($request->filled('customer_id')) {
                    $q->where('master_customer_id', $request->customer_id);
                }
                filterOrdersBySeason($q, $request->get('product_season_id'));
            })
            ->editColumn('created_at', function ($row) {
                return date('d M, Y', strtotime($row->created_at));
            })
            ->addColumn('expected_delivery_date', function ($row) {
                return $row->expected_delivery_date ? date('d M, Y', strtotime($row->expected_delivery_date)) : '-';
            })
            ->addColumn('customer_name', function ($row) {
                return $row->customer->name ?? 'N/A';
            })
            ->addColumn('designs', function ($row) {
                $designs = $row->OrderProductSets->groupBy('design_number')->map(function ($sets) {
                    return $sets->sum('total_quantity');
                })->toArray();
                return $designs;
            })
            ->addColumn('season', function ($row) {
                return seasonBadges(orderSetSeasonNames($row->OrderProductSets));
            })
            ->addColumn('lots', function ($row) {
                $orderLots = $row->orderLots ? $row->orderLots->pluck('lot_no') : collect();
                $rollLotsCount = \App\Models\FabricRollAssigning::where('order_no', $row->sku)->distinct('lot_no')->count('lot_no');
                $totalLotsCount = max($orderLots->filter()->unique()->count(), $rollLotsCount);

                if ($totalLotsCount > 0) {
                    return '<button type="button" class="btn btn-sm btn-outline-info font-weight-bold px-2 py-1 shadow-xs" onclick="showLotsModal(' . $row->id . ', \'' . htmlspecialchars($row->sku, ENT_QUOTES) . '\')" title="View ' . $totalLotsCount . ' Lots"><i class="fas fa-eye mr-1"></i> ' . $totalLotsCount . '</button>';
                } else {
                    return '<button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="showLotsModal(' . $row->id . ', \'' . htmlspecialchars($row->sku, ENT_QUOTES) . '\')" title="0 Lots"><i class="fas fa-eye mr-1"></i> 0</button>';
                }
            })
            ->addColumn('total_pcs', function ($row) {
                return getOrderDispatchData($row->id)['total'] ?? 0;
            })
            ->addColumn('scanned_pcs', function ($row) {
                return getOrderDispatchData($row->id)['packed'] ?? 0;
            })
            ->addColumn('status', function ($row) {
                $stats = getOrderDispatchData($row->id);

                if ($stats['remaining'] === 0) {
                    return '<span class="badge badge-success">Completed</span>';
                }

                if ($stats['packed'] > 0) {
                    return '<span class="badge badge-warning">Partial</span>';
                }

                return '<span class="badge badge-primary">In Progress</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('admin.report.order-summary.view', ['id' => $row->id]) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View Summary</a>';
            })
            ->rawColumns(['action', 'status', 'lots', 'season'])
            ->make(true);
    }

    public function view($id)
    {
        $order = OrderMain::with([
            'customer',
            'orderProductSets.colors',
            'orderProductSets.size_measurement',
            'orderProductSets.fabric.receiptDetails',
            'orderProductSets.master_design_pattern',
            'orderProductSets.master_product_fitting',
            'orderProductSets.lots.stageMasterUnit',
            'orderProductSets.product_set_details',
            'orderProductSets.order_cutting_stage.vendor',
            'orderProductSets.order_cutting_stage.customer',
            'orderProductSets.season'
        ])->find($id);

        if (!$order)
            return null;

        $cartons = PackingCarton::whereHas('main', function ($q) use ($id) {
            $q->where('order_main_id', $id);
        })->with(['items.detail'])->get();

        $dispatches = OrderDispatch::with('orderMain.customer')->where('main_order_id', $id)->get();

        $stats = getOrderDispatchData($id);
        $status = '<span class="badge badge-primary px-3">In Progress</span>';
        if ($stats['remaining'] === 0) {
            $status = '<span class="badge badge-success px-3">Completed</span>';
        } elseif ($stats['packed'] > 0) {
            $status = '<span class="badge badge-warning px-3">Partial</span>';
        }

        return [
            'order' => $order,
            'cartons' => $cartons,
            'dispatches' => $dispatches,
            'status' => $status
        ];
    }

    public function lots($id)
    {
        $searchOrder = $id;
        $orderMain = \App\Models\OrderMain::find($searchOrder);
        $orderSku = $orderMain->sku ?? '';

        $lots = \App\Models\FabricRollAssigning::query()
            ->selectRaw('
                lot_no,
                MIN(id) as id,
                MIN(order_products_set_id) as order_products_set_id,
                MIN(stage_master_unit_id) as stage_master_unit_id
            ')
            ->withSum('fabricRollAssigningsDetail as lot_quantity', 'quantity')
            ->with([
                'orderProductSet.orderMain.customer',
                'orderProductSet.season',
                'stageMasterUnit'
            ])
            ->when($searchOrder, function ($q) use ($searchOrder, $orderSku) {
                $q->where(function ($qq) use ($searchOrder, $orderSku) {
                    $qq->whereHas('orderProductSet.orderMain', function ($qqq) use ($searchOrder) {
                        $qqq->where('id', $searchOrder);
                    });
                    if ($orderSku) {
                        $qq->orWhere('order_no', $orderSku);
                    }
                });
            })
            ->groupBy('lot_no')
            ->get();

        $existingLotNos = $lots->pluck('lot_no')->filter()->toArray();

        $orderLots = \App\Models\OrderLot::where(function($q) use ($searchOrder) {
                $q->where('order_main_id', $searchOrder)
                  ->orWhereHas('orderProductSet', function($sq) use ($searchOrder) {
                      $sq->where('order_main_id', $searchOrder);
                  });
            })
            ->whereNotIn('lot_no', $existingLotNos)
            ->with(['orderProductSet.orderMain.customer', 'orderProductSet.season'])
            ->get();

        $result = collect();

        // Current stage of every lot in one batch from the lot records (ReportService::lotProgress = lotFacts,
        // the same figures as the Sales Order report: "Dispatched" once all cut pieces have left packing).
        $allLotNos = $lots->pluck('lot_no')->merge($orderLots->pluck('lot_no'))->filter()->unique()->values();
        $facts = app(\App\Services\Admin\ReportService::class)->lotProgress($allLotNos);
        $stageOf = function ($lotNo) use ($facts) {
            $key = \App\Services\Admin\ReportService::lotKeyOf($lotNo);
            return isset($facts[$key]) ? $facts[$key]['stage_name'] : 'N/A';
        };
        // Cutting plan (stage 3) of each lot: the first row per lot, read once for all lots
        $cuttingTimings = \App\Models\OrderLotStageTiming::whereIn('lot_no', $allLotNos->all())
            ->where('master_stage_id', 3)
            ->orderBy('id')
            ->get()
            ->groupBy(fn($t) => \App\Services\Admin\ReportService::lotKeyOf($t->lot_no))
            ->map->first();
        $timingOf = fn($lotNo) => $cuttingTimings[\App\Services\Admin\ReportService::lotKeyOf($lotNo)] ?? null;

        foreach ($lots as $lot) {
            $om = $lot->orderProductSet?->orderMain ?? $orderMain;
            $cuttingMaster = $lot->stageMasterUnit->name ?? 'N/A';
            $timing = $timingOf($lot->lot_no);

            $result->push([
                'order_id' => $om->id ?? null,
                'order_no' => $om->sku ?? '',
                'customer_name' => $om->customer->name ?? '',
                'lot_no' => $lot->lot_no,
                'lot_quantity' => (int)($lot->lot_quantity ?? 0),
                'design_number' => $lot->orderProductSet?->design_number ?? 'N/A',
                'season' => $lot->orderProductSet?->season?->name,
                'cutting_master' => $cuttingMaster,
                'start_time' => $timing ? $timing->start_date : null,
                'end_time' => $timing ? $timing->end_date : null,
                'status' => $timing ? $timing->status : 0,
                'last_current_stage' => $stageOf($lot->lot_no),
            ]);
        }

        foreach ($orderLots as $ol) {
            $om = $ol->orderProductSet?->orderMain ?? $orderMain;
            $roll = \App\Models\FabricRollAssigning::where('lot_no', $ol->lot_no)
                ->with('stageMasterUnit')
                ->withSum('fabricRollAssigningsDetail as roll_qty', 'quantity')
                ->first();
            $qty = $roll ? $roll->roll_qty : ($ol->orderProductSet->total_quantity ?? 0);
            $cuttingMaster = $roll?->stageMasterUnit?->name ?? 'N/A';
            $timing = $timingOf($ol->lot_no);

            $result->push([
                'order_id' => $om->id ?? null,
                'order_no' => $om->sku ?? '',
                'customer_name' => $om->customer->name ?? '',
                'lot_no' => $ol->lot_no,
                'lot_quantity' => (int)($qty ?? 0),
                'design_number' => $ol->orderProductSet?->design_number ?? 'N/A',
                'season' => $ol->orderProductSet?->season?->name,
                'cutting_master' => $cuttingMaster,
                'start_time' => $timing ? $timing->start_date : null,
                'end_time' => $timing ? $timing->end_date : null,
                'status' => $timing ? $timing->status : 0,
                'last_current_stage' => $stageOf($ol->lot_no),
            ]);
        }

        return $result->toArray();
    }
}
