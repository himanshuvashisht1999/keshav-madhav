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
    public function stockRolls(Request $request)
    {
        $query = \App\Models\FabricReceiptDetail::with(['fabric', 'master_fabric_warehouse', 'fabric_receipt.vendor'])
            ->orderBy('fabric_id', 'asc')
            ->orderBy('roll_number', 'asc');

        if ($request->filled('fabric_id')) {
            $query->where('fabric_id', $request->fabric_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('master_fabric_warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('roll_no')) {
            $query->where('roll_number', 'LIKE', '%' . $request->roll_no . '%');
        }

        if ($request->filled('qty_from')) {
            $query->where('remaining_quantity', '>=', $request->qty_from);
        }

        if ($request->filled('qty_to')) {
            $query->where('remaining_quantity', '<=', $request->qty_to);
        }

        $rolls = $query->paginate(30)->withQueryString();
        $warehouses = $this->service->warehouses();
        $fabrics = $this->service->fabrics();

        return view('admin.report.stock_rolls', compact('rolls', 'warehouses', 'fabrics'));
    }

    public function stockRollTracking(Request $request)
    {
        $fabricId = $request->fabric_id;
        $rollNo = $request->roll_no;

        if (!$fabricId || !$rollNo) {
            return redirect()->route('admin.report.stock.rolls')->with('error', 'Roll Number and Fabric selection are required.');
        }

        $shipping = \App\Models\FabricReceiptDetail::where('fabric_id', $fabricId)
            ->where('roll_number', $rollNo)
            ->with(['fabric_receipt.vendor', 'purchase_order', 'master_fabric_warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();

        $receiptIds = \App\Models\FabricReceiptDetail::where('fabric_id', $fabricId)->where('roll_number', $rollNo)->pluck('id');

        $internalUsages = \App\Models\FabricRollAssigning::whereIn('fabric_receipt_detail_id', $receiptIds->isEmpty() ? [0] : $receiptIds)
            ->with(['orderProductSet.colors', 'stageMasterUnit'])
            ->orderBy('created_at', 'desc')
            ->get();

        $agentUsages = \App\Models\AgentOrderFabricItem::whereHas('roll', function($q) use ($rollNo) {
            $q->where('roll_number', $rollNo);
        })->where('fabric_id', $fabricId)
          ->whereNotNull('agent_order_dispatch_id')
          ->with(['order.party', 'roll'])
          ->orderBy('created_at', 'desc')
          ->get();

        $returns = \App\Models\FabricReturnDetail::whereHas('receipt_detail', function($q) use ($rollNo) {
            $q->where('roll_number', $rollNo);
        })->where('fabric_id', $fabricId)
          ->with(['fabric_return.receipt.vendor'])
          ->orderBy('created_at', 'desc')
          ->get();

        $rollLedger = collect();

        foreach ($shipping as $s) {
            $rollLedger->push((object)[
                'date' => $s->created_at,
                'type' => 'Shipping / Arrival',
                'qty' => $s->meter,
                'details' => 'Warehouse: ' . ($s->master_fabric_warehouse?->cutting_master_name ?? '-') . ' | Supplier: ' . ($s->fabric_receipt->vendor->name ?? '-'),
                'order_no' => $s->purchase_order?->sku ?? '-',
                'lot_no' => $s->shipment_number ?? '-',
            ]);
        }

        foreach ($internalUsages as $u) {
            $designNo = $u->orderProductSet?->design_number ?? '-';
            $colorName = $u->orderProductSet?->colors?->name ?? '-';
            $rollLedger->push((object)[
                'date' => $u->created_at,
                'type' => 'Usage (Production)',
                'qty' => -$u->meter,
                'details' => 'Design: ' . $designNo . ' | Color: ' . $colorName . ' | Unit: ' . ($u->stageMasterUnit?->name ?? '-'),
                'order_no' => $u->order_no,
                'lot_no' => $u->lot_no,
            ]);
        }

        foreach ($agentUsages as $a) {
            $partyName = $a->order?->party?->name ?? '-';
            $rollLedger->push((object)[
                'date' => $a->created_at,
                'type' => 'Usage (Agent Order)',
                'qty' => -$a->meter,
                'details' => 'Party: ' . $partyName . ' | Price: ' . number_format($a->selling_price, 2),
                'order_no' => $a->order?->sku ?? ('PO-' . $a->agent_order_id),
                'lot_no' => 'Agent Order',
            ]);
        }

        foreach ($returns as $r) {
            $vendorName = $r->fabric_return?->receipt?->vendor?->name ?? 'Vendor';
            $rollLedger->push((object)[
                'date' => $r->created_at,
                'type' => 'Return to Vendor',
                'qty' => -$r->return_meter,
                'details' => 'Supplier: ' . $vendorName . ' | Return Remarks: ' . ($r->fabric_return?->remarks ?? 'N/A'),
                'order_no' => $r->fabric_return?->return_number ?? '-',
                'lot_no' => 'Return',
            ]);
        }

        $data = $rollLedger->sortByDesc('date')->values();
        $fabric = \App\Models\Fabric::find($fabricId);

        return view('admin.report.stock_roll_details', compact('data', 'fabric', 'rollNo'));
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
                ->whereHas('purchase_order_item', function ($q) use ($po) {
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

    public function purchaseOrderFabricWise(Request $request)
    {
        $fabricId = $request->fabric_id;

        if ($fabricId) {
            $fabric = \App\Models\Fabric::findOrFail($fabricId);
            $history = $this->service->purchaseOrderFabricWise($request);
            $vendors = $this->service->vendors();
            $filters = $request->all();
            return view('admin.report.purchase_order_fabric_wise_details', compact('fabric', 'history', 'vendors', 'filters'));
        }

        $response['data'] = $this->service->purchaseOrderFabricWise($request);
        $response['filters'] = $request->all();
        return view('admin.report.purchase_order_fabric_wise', $response);
    }

    public function purchaseOrderFabricWiseShipments(Request $request)
    {
        $fabricId = $request->fabric_id;
        $fabric = \App\Models\Fabric::findOrFail($fabricId);
        $shipments = $this->service->purchaseOrderFabricWiseShipments($request);
        $vendors = $this->service->vendors();
        $filters = $request->all();
        return view('admin.report.purchase_order_fabric_wise_shipments', compact('fabric', 'shipments', 'vendors', 'filters'));
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

    public function closePurchaseOrder($id)
    {
        $po = \App\Models\PurchaseOrder::findOrFail($id);
        $po->is_closed = 1;
        $po->save();
        
        return redirect()->back()->with('success', 'Purchase Order closed successfully. It will no longer be available for shipment receipts.');
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

    public function unitAssignmentsPdf(Request $request)
    {
        $response = $this->service->unitAssignments($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report.pdf.unit_assignments', $response);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('unit-assignments-report-' . now()->format('d-m-Y_H-i') . '.pdf');
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
