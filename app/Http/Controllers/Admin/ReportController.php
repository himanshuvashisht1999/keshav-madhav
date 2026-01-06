<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FabricReceiptDetail;
use App\Services\Admin\{
    ReportService as Service
};


class ReportController extends Controller
{
    protected $service;
    public function __construct(
        Service $service, 
    ) {
        $this->service = $service;
    }

    public function salesOrder(Request $request)
    {
        $response['data'] = $this->service->salesOrder($request);
        return view('admin.report.sales_order',$response);
    }
    public function salesOrderExport(Request $request)
    {
        // SAME data as screen
        $data = $this->service->salesOrder($request);

        return response()
            ->view('admin.report.sales_order_export', [
                'data' => $data,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="sales-order-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }
    public function stock(Request $request)
    {
        $response['data'] = $this->service->stock($request);
        $response['warehouses'] = $this->service->warehouses();
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();
        return view('admin.report.stock',$response);
    }
    public function fabricRollDetails(Request $request)
    {
        return $this->service->fabricRollDetails(
            $request->fabric_sku,
            $request->warehouse_id
        );
    } 
    // public function stockExport(Request $request)
    // {
    //     // Same summary data as stock page
    //     $data = $this->service->stock($request);

    //     // Get ALL roll data for same filters
    //     $rolls = FabricReceiptDetail::query()
    //         ->where('remaining_quantity', '>', 0)
    //         ->when($request->filled('warehouse_id'), function ($q) use ($request) {
    //             $q->where('master_fabric_warehouse_id', $request->warehouse_id);
    //         })
    //         ->when($request->filled('fabric_sku'), function ($q) use ($request) {
    //             $q->where('fabric_sku', $request->fabric_sku);
    //         })
    //         ->orderBy('fabric_sku')
    //         ->orderBy('master_fabric_warehouse_id')
    //         ->orderBy('roll_number')
    //         ->get()
    //         ->groupBy(function ($row) {
    //             return $row->fabric_sku . '_' . $row->master_fabric_warehouse_id;
    //         });

    //     return response()
    //         ->view('admin.report.stock_export', [
    //             'data' => $data,
    //             'rolls' => $rolls,
    //             'exportedAt' => now()
    //         ])
    //         ->header('Content-Type', 'application/vnd.ms-excel')
    //         ->header(
    //             'Content-Disposition',
    //             'attachment; filename="fabric-stock-report-' . now()->format('d-m-Y_H-i') . '.xls"'
    //         );
    // }

    public function stockExport(Request $request)
    {
        // Summary data (same as UI)
        $data = $this->service->stock($request);

        // Roll-level data (same logic as fabricRollDetails)
        $rolls = FabricReceiptDetail::with([
                'fabric_receipt.vendor',
                'purchase_order'
            ])
            ->where('remaining_quantity', '>', 0)

            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->where('master_fabric_warehouse_id', $request->warehouse_id);
            })
            ->when($request->filled('fabric_sku'), function ($q) use ($request) {
                $q->where('fabric_sku', $request->fabric_sku);
            })

            ->orderBy('fabric_sku')
            ->orderBy('master_fabric_warehouse_id')
            ->orderBy('shipment_number')
            ->orderBy('roll_number')
            ->get()

            // group same way as summary table
            ->groupBy(function ($row) {
                return $row->fabric_sku . '_' . $row->master_fabric_warehouse_id;
            });

        return response()
            ->view('admin.report.stock_export', [
                'data'       => $data,
                'rolls'      => $rolls,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="fabric-stock-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }


    public function purchaseOrder(Request $request)
    {
        $response['data'] = $this->service->purchaseOrder($request);
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();
        return view('admin.report.purchase_order',$response);
    }

    public function purchaseOrderItemDetails(Request $request)
    {
        return $this->service->purchaseOrderItemReceipts(
            $request->purchase_order_item_id
        );
    }
    public function purchaseOrderExport(Request $request)
    {
        // Same filtered purchase orders
        $orders = $this->service->purchaseOrder($request);

        // Get ALL receipt (modal) data for these PO items
        $receipts = FabricReceiptDetail::with('master_fabric_warehouse')
            ->whereIn(
                'purchase_order_item_id',
                $orders->pluck('items')->flatten()->pluck('id')
            )
            ->where('status', 2)
            ->orderBy('created_at')
            ->get()
            ->groupBy('purchase_order_item_id');

        return response()
            ->view('admin.report.purchase_order_export', [
                'orders' => $orders,
                'receipts' => $receipts,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="purchase-order-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function orderTrackingSystem(Request $request)
    {
        $response['data'] = $this->service->orderTrackingSystem($request);
        return view('admin.report.order_tracking',$response);
    }

    public function lotTrackingDetails(Request $request)
    {
        $lot_no = $request->lot_no;

        $data = $this->service->lotTrackingDetails($request);

        return response()->json($data);
    }
    public function orderTrackingExport(Request $request)
    {
        // SAME DATA as screen
        $data = $this->service->orderTrackingSystem($request);

        return response()
            ->view('admin.report.order_tracking_export', [
                'data' => $data,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="order-tracking-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function dispatchOrder(Request $request)
    {   
        $response['customers'] = $this->service->customers();   
        $response['data'] = $this->service->dispatchOrder($request);
        return view('admin.report.dispatch_order',$response);
    }

}
