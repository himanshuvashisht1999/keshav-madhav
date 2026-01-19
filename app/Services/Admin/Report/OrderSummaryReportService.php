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
        $query = OrderMain::with(['customer'])
            ->select('order_main.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($q) use ($request) {
                if ($request->filled('order_no')) {
                    $q->where('sku', 'like', '%' . $request->order_no . '%');
                }
                if ($request->filled('customer_id')) {
                    $q->where('customer_id', $request->customer_id);
                }
            })
            ->editColumn('created_at', function ($row) {
                return date('d M, Y', strtotime($row->created_at));
            })
            ->addColumn('customer_name', function ($row) {
                return $row->customer->name ?? 'N/A';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('admin.report.order-summary.view', ['id' => $row->id]) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View Summary</a>';
            })
            ->rawColumns(['action'])
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
            'orderProductSets.stage_master_unit',
            'orderProductSets.product_set_details'
        ])->find($id);

        if (!$order) return null;

        // 1. Production Stages (Lots)
        // Assuming OrderStage or FabricRollAssigning links to OrderMain. 
        // Based on previous context, OrderMain hasMany OrderProductSets. 
        // We might need to fetch stages from a related model or if OrderStage links directly.
        // Let's assume there's a way to get stages or we just list the defined stages for now.
        // CHECK: OrderMain doesn't seem to have direct Stage link in previous files, but ProductOrderService used OrderStage.
        // Usage: `OrderStage::where('order_main_id', $id)->get()`?
        // Let's try to fetch generic stages if specific ones aren't linked, or check if we can find lot details.
        
        // For now, let's look for "Lots" which seem to be FabricRollAssigning or similar.
        // Actually, let's load what we can find. Users mentioned "orders lot details".
        
        // 2. Packing Details
        // OrderMain -> PackingMain -> PackingCarton
        // We need to fetch all cartons for this order.
        $cartons = PackingCarton::whereHas('main', function($q) use ($id) {
            $q->where('order_main_id', $id);
        })->with(['items.detail'])->get();
        // dd($cartons);
        // 3. Dispatch Details
        // OrderMain -> OrderDispatch (via connection or direct)
        // OrderDispatch usually links to OrderMain.
        $dispatches = OrderDispatch::with('orderMain.customer')->where('main_order_id', $id)->get();

        // dd($order);

        // lot details 

        return [
            'order' => $order,
            'cartons' => $cartons,
            'dispatches' => $dispatches
            // 'lots' => $lots // Placeholder until we confirm relation
        ];
    }

    public function lots($id)
    {
        // $searchLot   = $request->lot_no;
        $searchOrder = $id;

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
}
