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
            'orderProductSets.sizeMeasurement'
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

        // 3. Dispatch Details
        // OrderMain -> OrderDispatch (via connection or direct)
        // OrderDispatch usually links to OrderMain.
        $dispatches = OrderDispatch::where('main_order_id', $id)->get();


        return [
            'order' => $order,
            'cartons' => $cartons,
            'dispatches' => $dispatches,
            // 'lots' => $lots // Placeholder until we confirm relation
        ];
    }
}
