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
    
    public function stockExportWithRate(Request $request)
    {
        $query = \App\Models\FabricReceiptDetail::with(['fabric'])
            ->where('remaining_quantity', '>', 0)
            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->where('master_fabric_warehouse_id', $request->warehouse_id);
            })
            ->when($request->filled('fabric_id'), function ($q) use ($request) {
                $q->where('fabric_id', $request->fabric_id);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('fabric', function($fq) use ($request) {
                    $fq->where('name', 'LIKE', '%' . $request->search . '%')
                       ->orWhere('sku', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->select([
                'fabric_id',
                'price_per_meter',
                \DB::raw('SUM(remaining_quantity) as total_remaining')
            ])
            ->groupBy('fabric_id', 'price_per_meter')
            ->having('total_remaining', '>', 0);
            
        $data = $query->get();

        return response()
            ->view('admin.report.stock_export_with_rate', [
                'data' => $data,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="fabric-stock-with-rate-' . now()->format('d-m-Y_H-i') . '.xls"'
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

    private function forceDeleteLot($id)
    {
        $session = \App\Models\OrderLot::findOrFail($id);
        $slip_id = $session->production_slip_digitization_id;

        if (\App\Models\ProductionOutflowInventory::where('lot_no', $session->lot_no)->exists()) {
            throw new \Exception("Cannot delete Lot {$session->lot_no}. It is already in packing.");
        }

        // FORCIBLY DELETE ALL SUBSEQUENT TRANSACTIONS FOR THIS LOT
        $printingTxs = \App\Models\OrderPrintingStageTransaction::where('lot_no', $session->lot_no)->get();
        foreach($printingTxs as $ptx) {
            \App\Models\OrderPrintingStageTransactionDetail::where('order_printing_stage_transaction_id', $ptx->id)->delete();
            $ptx->delete();
        }

        $stageTxs = \App\Models\OrderStageTransaction::where('lot_no', $session->lot_no)->get();
        foreach($stageTxs as $stx) {
            \App\Models\OrderStageTransactionDetail::where('order_stage_transaction_id', $stx->id)->delete();
            $stx->delete();
        }

        $printStitchTxs = \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $session->lot_no)->get();
        foreach($printStitchTxs as $pstx) {
            \App\Models\OrderPrintingToStichingTransactionDetail::where('order_printing_to_stiching_transaction_id', $pstx->id)->delete();
            $pstx->delete();
        }

        $godamTxs = \App\Models\OrderGodamStageTransaction::where('lot_no', $session->lot_no)->get();
        foreach($godamTxs as $gtx) {
            \App\Models\OrderGodamStageTransactionDetail::where('order_godam_stage_transaction_id', $gtx->id)->delete();
            $gtx->delete();
        }

        \App\Models\MasterStageWiseTimeAllocation::where('lot_no', $session->lot_no)->delete();

        $parts = \App\Models\ProductionSlipDigitizationParts::where('lot_no', $session->lot_no)->get();
        foreach($parts as $part) {
            \App\Models\ProductionDigitizationSetsDetails::where('production_slip_digitization_parts_id', $part->id)->delete();
            $part->delete();
        }
        \App\Models\OrderCuttingStage::where('lot_no', $session->lot_no)->delete();

        // Revert FabricRollAssigning
        $rolls = \App\Models\FabricRollAssigning::where('order_lot_id', $id)->get();
        foreach ($rolls as $roll) {
            // Revert FabricReceiptDetail meters
            $receipt = \App\Models\FabricReceiptDetail::find($roll->fabric_receipt_detail_id);
            if ($receipt) {
                $receipt->remaining_quantity += $roll->meter;
                $receipt->save();
            }

            // Revert OrderProductSetDetail quantities
            $details = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $roll->id)->get();
            foreach ($details as $detail) {
                $setDetail = \App\Models\OrderProductSetDetail::where('order_products_set_id', $session->order_products_set_id)
                    ->where('size', $detail->size)
                    ->first();
                if ($setDetail) {
                    $setDetail->remaining_lot_allocated += $detail->quantity;
                    $setDetail->save();
                }

                $detail->delete();
            }
            $roll->delete();
        }
        $session->delete();

        // Reset Digitized Status of Slip
        if ($slip_id) {
            \App\Models\ProductionSlipDigitization::where('id', $slip_id)->update([
                'status' => 0,
                'save_type' => 1, // Restore save type
                'lot_no' => null,
                'to_stage_id' => null
            ]);
        }
    }

    public function deleteMultipleLots(Request $request)
    {
        $ids = $request->input('lot_ids', []);
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No lots selected for deletion.');
        }

        $successCount = 0;
        $errorMsg = null;

        foreach ($ids as $id) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $this->forceDeleteLot($id);
                \Illuminate\Support\Facades\DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $errorMsg = $e->getMessage();
            }
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', "$successCount lot(s) deleted successfully." . ($errorMsg ? " But encountered error: " . $errorMsg : ""));
        }

        return redirect()->back()->with('error', $errorMsg ?: 'Error deleting selected lots.');
    }

    public function lotDetails(Request $request)
    {
        $response['data'] = $this->service->lotDetails($request->lot_no);

        if (empty($response['data'])) {
            if (session()->has('success')) {
                return redirect()->route('admin.report.lots')->with('success', session('success') . ' The lot has been completely removed.');
            }
            return redirect()->route('admin.report.lots')->with('error', 'Lot not found or has been completely deleted.');
        }

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

    public function deleteLotSession($type, $id)
    {
        $result = deleteProductionSession($type, $id);

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
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
            ->view('admin.report.unit_assignments_export', [
                'assignments' => $response['assignments'],
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="unit-assignments-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function stockPending(Request $request)
    {
        $request->merge(['is_pagination' => true]);
        $response = $this->service->stockPending($request);
        
        if ($request->ajax()) {
            return view('admin.report.partials.stock_pending_rows', $response);
        }
        
        return view('admin.report.stock_pending', $response);
    }

    public function stockPendingExport(Request $request)
    {
        $request->merge(['is_pagination' => false]);
        $response = $this->service->stockPending($request);

        return response()
            ->view('admin.report.stock_pending_export', [
                'assignments' => $response['assignments'],
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="stock-pending-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function stockPendingPdf(Request $request)
    {
        $request->merge(['is_pagination' => false]);
        $response = $this->service->stockPending($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report.pdf.stock_pending', $response);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('stock-pending-' . date('YmdHis') . '.pdf');
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
        $response['colors'] = \App\Models\MasterColor::where('status', 1)->get();
        $response['patterns'] = \App\Models\MasterDesignPattern::where('status', 1)->get();
        $response['fittings'] = \App\Models\MasterProductFitting::where('status', 1)->get();
        $response['stages'] = \App\Models\MasterProductStage::where('status', 1)->pluck('name')->toArray();
        return view('admin.report.design_wip', $response);
    }

    public function designWipApiCustomers(Request $request)
    {
        $response = $this->service->designWipApiCustomers($request);
        return response()->json($response);
    }

    public function designWipApiOrders(Request $request)
    {
        $response = $this->service->designWipApiOrders($request);
        return response()->json($response);
    }

    public function designWipApiDesigns(Request $request)
    {
        $response = $this->service->designWipApiDesigns($request);
        return response()->json($response);
    }

    public function designWipApiLots(Request $request)
    {
        $response = $this->service->designWipApiLots($request);
        return response()->json($response);
    }

    public function designWipApiLotDetails(Request $request)
    {
        if (str_starts_with($request->lot_no, 'UNASSIGNED_')) {
            $html = '<div style="text-align: center; color: #6b7280; font-size: 13px; margin-top: 40px;"><i class="fas fa-info-circle" style="font-size: 32px; color: #d1d5db; margin-bottom: 12px; display:block;"></i>This represents the unassigned quantity for the design.<br>It has not been cut into a production lot yet.</div>';
            return response()->json(['status' => true, 'html' => $html]);
        }

        $response['data'] = $this->service->lotDetails($request->lot_no);
        $response['master_stages'] = $this->service->master_stages();

        if (!$response['data']) {
            return response()->json(['status' => false, 'message' => 'Lot details not found']);
        }
        $html = view('admin.report.partials.design_wip_lot_details', $response)->render();
        return response()->json(['status' => true, 'html' => $html]);
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
    }

    public function salesManReport(Request $request)
    {
        $salesMen = \App\Models\SalesMan::with(['orders' => function($q) use ($request) {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $q->whereBetween('order_date', [$request->start_date, $request->end_date]);
            }
        }, 'orders.dispatches'])->get();

        return view('admin.report.sales_man', compact('salesMen'));
    }

    public function salesManReportDetail($id, Request $request)
    {
        $salesMan = \App\Models\SalesMan::findOrFail($id);
        $ordersQuery = $salesMan->orders()->with(['dispatches', 'items', 'fabricItems', 'shop', 'vendor']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $ordersQuery->whereBetween('order_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('order_no')) {
            $ordersQuery->where('id', $request->order_no);
        }

        if ($request->filled('party_name')) {
            $partyName = $request->party_name;
            $ordersQuery->where(function($q) use ($partyName) {
                $q->whereHas('shop', function($sq) use ($partyName) {
                    $sq->where('name', 'like', '%' . $partyName . '%');
                })->orWhereHas('vendor', function($vq) use ($partyName) {
                    $vq->where('name', 'like', '%' . $partyName . '%');
                });
            });
        }

        $orders = $ordersQuery->latest('order_date')->get();

        return view('admin.report.sales_man_detail', compact('salesMan', 'orders'));
    }

    public function wipComplete(Request $request)
    {
        $customers = \App\Models\MasterCustomer::where('status', 1)->get();
        $selectedCustomer = $request->customer_id;
        
        $data = [];
        $master_stages = $this->service->master_stages();

        $availableOrders = [];
        $availableLots = [];
        $availableDesigns = [];
        $selectedOrders = $request->order_ids ?? [];
        $selectedLots = $request->lot_nos ?? [];
        $selectedDesigns = $request->design_nos ?? [];

        if ($selectedCustomer) {
            $baseOrders = \App\Models\OrderMain::where('master_customer_id', $selectedCustomer)
                ->with([
                    'OrderProductSets' => function($q) {
                        $q->has('orderLots');
                    },
                    'OrderProductSets.orderLots'
                ])
                ->latest()
                ->get();

            // Build available options
            foreach ($baseOrders as $order) {
                if ($order->OrderProductSets->isEmpty()) continue;
                $availableOrders[$order->id] = $order->sku;
                foreach ($order->OrderProductSets as $set) {
                    if (!empty($set->design_number)) {
                        $availableDesigns[$set->design_number] = $set->design_number;
                    }
                    foreach ($set->orderLots as $lot) {
                        $availableLots[$lot->lot_no] = $lot->lot_no;
                    }
                }
            }

            // Process data with filters
            foreach ($baseOrders as $order) {
                if (!empty($selectedOrders) && !in_array($order->id, $selectedOrders)) continue;

                foreach ($order->OrderProductSets as $set) {
                    if (!empty($selectedDesigns) && !in_array($set->design_number, $selectedDesigns)) continue;

                    foreach ($set->orderLots as $lot) {
                        if (!empty($selectedLots) && !in_array($lot->lot_no, $selectedLots)) continue;

                        $quantity = \App\Models\FabricRollAssigning::where('lot_no', $lot->lot_no)
                            ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                            ->get()
                            ->sum('total');

                        $details = $this->service->lotDetails($lot->lot_no);
                        if ($details) {
                            $data[] = [
                                'order' => $order,
                                'set' => $set,
                                'lot' => $lot,
                                'lot_quantity' => $quantity,
                                'details' => $details
                            ];
                        }
                    }
                }
            }
        }

        if ($request->has('export')) {
            return response()
                ->view('admin.report.wip_complete_export', compact('data', 'master_stages'))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header(
                    'Content-Disposition',
                    'attachment; filename="wip-complete-report-' . now()->format('d-m-Y_H-i') . '.xls"'
                );
        }

        return view('admin.report.wip_complete', compact(
            'customers', 
            'selectedCustomer', 
            'data', 
            'master_stages',
            'availableOrders',
            'availableLots',
            'availableDesigns',
            'selectedOrders',
            'selectedLots',
            'selectedDesigns'
        ));
    }

    public function productCustomerCount(Request $request)
    {
        $query = \App\Models\AgentOrderItem::select(
                'agent_order_items.design_number',
                \DB::raw('MAX(agent_order_items.product_name) as product_name'),
                \DB::raw('COUNT(DISTINCT agent_orders.master_customer_id) as customer_count'),
                \DB::raw('SUM(agent_order_items.quantity) as total_quantity')
            )
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->groupBy('agent_order_items.design_number');

        if ($request->filled('agent_id')) {
            $query->join('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
                  ->whereIn('master_customers.sales_agent_id', $request->agent_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('agent_orders.order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('agent_orders.order_date', '<=', $request->end_date);
        }
        if ($request->filled('design_number')) {
            $query->whereIn('agent_order_items.design_number', $request->design_number);
        }
        if ($request->filled('product_name')) {
            $query->whereIn('agent_order_items.product_name', $request->product_name);
        }
        if ($request->filled('size_set_name')) {
            $query->whereIn('agent_order_items.size_set_name', $request->size_set_name);
        }
        if ($request->filled('color_name')) {
            $query->whereIn('agent_order_items.color_name', $request->color_name);
        }
        
        $query->orderByDesc('customer_count');
        $data = $query->paginate(50);

        // Fetch distinct options for filters
        $designs = \App\Models\AgentOrderItem::select('design_number')->distinct()->whereNotNull('design_number')->pluck('design_number');
        $products = \App\Models\AgentOrderItem::select('product_name')->distinct()->whereNotNull('product_name')->pluck('product_name');
        $sizeSets = \App\Models\AgentOrderItem::select('size_set_name')->distinct()->whereNotNull('size_set_name')->pluck('size_set_name');
        $colors = \App\Models\AgentOrderItem::select('color_name')->distinct()->whereNotNull('color_name')->pluck('color_name');
        $agents = \App\Models\SalesAgent::where('status', 1)->get();

        // Calculate total possible customers for percentage
        $totalCustomers = 0;
        if ($request->filled('agent_id')) {
            $totalCustomers = \App\Models\MasterCustomer::where('status', 1)
                ->whereIn('sales_agent_id', (array) $request->agent_id)
                ->count();
        } else {
            $totalCustomers = \App\Models\MasterCustomer::where('status', 1)->count();
        }
        // Fallback to avoid division by zero
        if ($totalCustomers == 0) $totalCustomers = 1;

        return view('admin.report.product_customer_count', compact('data', 'designs', 'products', 'sizeSets', 'colors', 'agents', 'totalCustomers'));
    }

    public function productCustomerCountDetail($design_number, Request $request)
    {
        $query = \App\Models\AgentOrderItem::with(['order', 'order.shop', 'order.shop.agent'])
            ->where('design_number', $design_number);

        if ($request->filled('start_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->end_date);
            });
        }
        if ($request->filled('order_number')) {
            $query->whereHas('order', function ($q) use ($request) {
                $orderNumber = preg_replace('/[^0-9]/', '', $request->order_number);
                $q->where('id', 'like', '%' . $orderNumber . '%');
            });
        }
        if ($request->filled('customer_name')) {
            $query->whereHas('order.shop', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }
        if ($request->filled('agent_id')) {
            $query->whereHas('order.shop', function ($q) use ($request) {
                $q->whereIn('sales_agent_id', (array) $request->agent_id);
            });
        }
        if ($request->filled('product_name')) {
            $query->whereIn('product_name', $request->product_name);
        }
        if ($request->filled('size_set_name')) {
            $query->whereIn('size_set_name', $request->size_set_name);
        }
        if ($request->filled('color_name')) {
            $query->whereIn('color_name', $request->color_name);
        }

        $items = $query->latest()->paginate(50);

        // Fetch distinct options for filters (restricted to this design_number)
        $products = \App\Models\AgentOrderItem::where('design_number', $design_number)->select('product_name')->distinct()->whereNotNull('product_name')->pluck('product_name');
        $sizeSets = \App\Models\AgentOrderItem::where('design_number', $design_number)->select('size_set_name')->distinct()->whereNotNull('size_set_name')->pluck('size_set_name');
        $colors = \App\Models\AgentOrderItem::where('design_number', $design_number)->select('color_name')->distinct()->whereNotNull('color_name')->pluck('color_name');
        $agents = \App\Models\SalesAgent::where('status', 1)->get();

        return view('admin.report.product_customer_count_detail', compact('items', 'design_number', 'products', 'sizeSets', 'colors', 'agents'));
    }
}
