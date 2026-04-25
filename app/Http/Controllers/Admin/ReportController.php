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
        return view('admin.report.sales_order', $response);
    }

    public function salesOrderDetail($id)
    {
        $response['order'] = $this->service->getSalesOrderDetails($id);
        // dd($response['order']);
        return view('admin.report.sales_order_detail', $response);
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
        $reportData = $this->service->stock($request);
        
        $response = $reportData;
        $response['warehouses'] = $this->service->warehouses();
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();

        return view('admin.report.stock', $response);
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
        $request->merge(['is_export' => true]);
        $reportData = $this->service->stock($request);
        $level = $reportData['level'];

        // Dynamic view selection based on level
        $view = 'admin.report.stock_export'; // Default for fabrics summary
        $filenamePrefix = 'fabric-stock';

        if ($level === 'warehouses') {
            $view = 'admin.report.stock_warehouse_export';
            $filenamePrefix = 'fabric-warehouse-stock';
        } elseif ($level === 'receipts') {
            $view = 'admin.report.stock_receipts_export';
            $filenamePrefix = 'fabric-shipments';
        } elseif ($level === 'usages') {
            $view = 'admin.report.stock_usages_export';
            $filenamePrefix = 'fabric-usages';
        }

        // Add extra chronological ledger if just a fabric is selected but no specific type
        if ($request->filled('fabric_id') && !$request->filled('type')) {
            $ledger = $this->service->fabricLedger($request->fabric_id, $request->warehouse_id);
            $reportData['ledger'] = $ledger;
            $reportData['fabric_name'] = \App\Models\Fabric::find($request->fabric_id)?->name ?? $request->fabric_id;
            $view = 'admin.report.stock_ledger_export';
            $filenamePrefix = 'fabric-ledger';
        }

        return response()
            ->view($view, array_merge($reportData, ['exportedAt' => now()]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="' . $filenamePrefix . '-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }


    public function stockPdf(Request $request)
    {
        $request->merge(['is_export' => true]);
        $reportData = $this->service->stock($request);
        $level = $reportData['level'];

        // Dynamic view selection based on level
        $view = 'admin.report.stock_pdf'; // Base template for PDF
        $filenamePrefix = 'fabric-stock';

        if ($level === 'warehouses') {
            $filenamePrefix = 'fabric-warehouse-stock';
        } elseif ($level === 'receipts') {
            $filenamePrefix = 'fabric-shipments';
        } elseif ($level === 'usages') {
            $filenamePrefix = 'fabric-usages';
        }

        // Add extra chronological ledger logic
        // Add extra chronological ledger logic
        if ($request->filled('fabric_id') && !$request->filled('type')) {
            $ledger = $this->service->fabricLedger($request->fabric_id, $request->warehouse_id);
            $reportData['ledger'] = $ledger;
            $reportData['fabric_name'] = \App\Models\Fabric::find($request->fabric_id)?->name ?? $request->fabric_id;
            $filenamePrefix = 'fabric-ledger';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report.stock_pdf', array_merge($reportData, [
            'fabric_name' => $reportData['fabric_name'] ?? ($reportData['fabric']->name ?? null),
            'warehouses' => $this->service->warehouses(),
            'fabrics' => $this->service->fabrics(),
            'exportedAt' => now(),
            'filename' => $filenamePrefix
        ]))->setPaper('A4', 'portrait');

        return $pdf->download($filenamePrefix . '-' . now()->format('d-m-Y') . '.pdf');
    }

    public function purchaseOrder(Request $request)
    {
        if ($request->filled('purchase_order_id')) {
            $po = \App\Models\PurchaseOrder::with(['vendor', 'items.fabric'])->findOrFail($request->purchase_order_id);
            $receipts = \App\Models\FabricReceiptDetail::with(['master_fabric_warehouse', 'fabric'])
                ->whereHas('purchase_order_item', function($q) use ($po) {
                    $q->where('purchase_order_id', $po->id);
                })
                ->orderBy('created_at')
                ->get();
            return view('admin.report.purchase_order_details', compact('po', 'receipts'));
        }

        $response['data'] = $this->service->purchaseOrder($request);
        $response['vendors'] = $this->service->vendors();
        $response['filters'] = $request->all();
        return view('admin.report.purchase_order', $response);
    }

    public function purchaseOrderItemDetails(Request $request)
    {
        return $this->service->purchaseOrderItemReceipts(
            $request->purchase_order_item_id
        );
    }
    public function purchaseOrderExport(Request $request)
    {
        // Same filtered purchase orders (all for export)
        $request->merge(['is_export' => true]);
        $orders = $this->service->purchaseOrder($request);

        // Get ALL receipt data for these PO items
        $receipts = FabricReceiptDetail::with('master_fabric_warehouse')
            ->whereIn(
                'purchase_order_item_id',
                $orders->pluck('items')->flatten()->pluck('id')
            )
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
        return view('admin.report.order_tracking', $response);
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
        return view('admin.report.dispatch_order', $response);
    }

    public function lots(Request $request)
    {
        $response['data'] = $this->service->orderLotsDetailed($request);
        $response['lotNos'] = $this->service->lot_numbers();
        return view('admin.report.lots', $response);
    }

    public function lotDetails(Request $request)
    {
        $response['data'] = $this->service->lotDetails($request->lot_no);
        $response['master_stages'] = $this->service->master_stages();
        // dd($response['data']);
        return view('admin.report.lot_details', $response);
    }

    public function lotDetailsPdf(Request $request)
    {
        $response['data'] = $this->service->lotDetails($request->lot_no);
        $response['master_stages'] = $this->service->master_stages();

        if (!$response['data']) {
            return redirect()->back()->with('error', 'Lot not found');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.reports.lot_details_pdf', $response)->setPaper('A4', 'portrait');
        return $pdf->download('lot-details-' . $request->lot_no . '.pdf');
    }

    public function unitAssignments(Request $request)
    {
        $response = $this->service->unitAssignments($request);
        return view('admin.report.unit_assignments', $response);
    }

    public function closeUnitAssignment(Request $request, $type, $id)
    {
        $success = $this->service->closeUnitAssignment($type, $id);
        if ($success) {
            return redirect()->route('admin.reports.unit-assignments', $request->query())->with('success', 'Task closed successfully.');
        }
        return redirect()->back()->with('error', 'Assignment not found.');
    }

    public function reopenUnitAssignment(Request $request, $type, $id)
    {
        $success = $this->service->reopenUnitAssignment($type, $id);
        if ($success) {
            return redirect()->route('admin.reports.unit-assignments', ['view' => 'open'] + $request->query())->with('success', 'Task re-opened successfully.');
        }
        return redirect()->back()->with('error', 'Assignment not found.');
    }

    public function unitAssignmentsExport(Request $request)
    {
        $response = $this->service->unitAssignments($request);

        return response()
            ->view('admin.report.unit_assignments_export', $response)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="unit-assignments-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function designWip(Request $request)
    {
        $response = $this->service->designWip($request);
        return view('admin.report.design_wip', $response);
    }

    public function fabricReturn(Request $request)
    {
        $response['data'] = $this->service->fabricReturn($request);
        $response['vendors'] = $this->service->vendors();
        $response['filters'] = $request->all();
        return view('admin.report.fabric_return', $response);
    }

    public function fabricReturnView($id)
    {
        $response['return'] = $this->service->getFabricReturnDetails($id);
        return view('admin.report.fabric_return_view', $response);
    }
}
