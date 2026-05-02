<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ReportService as Service;
use App\Models\FabricReceiptDetail;
use App\Models\Fabric;
use App\Models\MasterFabricWarehouse;

class ReportController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function stock(Request $request)
    {
        $reportData = $this->service->stock($request);
        $response = $reportData;
        $response['warehouses'] = $this->service->warehouses();
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();

        return view('owner.reports.stock', $response);
    }

    public function stockRolls(Request $request)
    {
        $query = FabricReceiptDetail::with(['fabric', 'master_fabric_warehouse', 'fabric_receipt.vendor'])
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

        $rolls = $query->paginate(20)->withQueryString();
        $warehouses = $this->service->warehouses();
        $fabrics = $this->service->fabrics();

        return view('owner.reports.stock_rolls', compact('rolls', 'warehouses', 'fabrics'));
    }

    public function stockRollTracking(Request $request)
    {
        $fabricId = $request->fabric_id;
        $rollNo = $request->roll_no;

        if (!$fabricId || !$rollNo) {
            return redirect()->route('owner.report.stock.rolls')->with('error', 'Roll Number and Fabric selection are required.');
        }

        $shipping = FabricReceiptDetail::where('fabric_id', $fabricId)
            ->where('roll_number', $rollNo)
            ->with(['fabric_receipt.vendor', 'purchase_order', 'master_fabric_warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();

        $internalUsages = \App\Models\FabricRollAssigning::where('roll_no', $rollNo)
            ->with(['orderProductSet.colors', 'stageMasterUnit'])
            ->orderBy('created_at', 'desc')
            ->get();

        $agentUsages = \App\Models\AgentOrderFabricItem::whereHas('roll', function($q) use ($rollNo) {
            $q->where('roll_number', $rollNo);
        })->where('fabric_id', $fabricId)
          ->with(['order.party', 'roll'])
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

        $data = $rollLedger->sortByDesc('date')->values();
        $fabric = Fabric::find($fabricId);

        return view('owner.reports.stock_roll_details', compact('data', 'fabric', 'rollNo'));
    }

    public function stockRollDetails(Request $request)
    {
        return $this->service->fabricRollDetails(
            $request->fabric_sku,
            $request->warehouse_id
        );
    }

    public function lots(Request $request)
    {
        $response['data'] = $this->service->orderLotsDetailed($request);
        $response['lotNos'] = $this->service->lot_numbers();
        return view('owner.reports.lots', $response);
    }

    public function lotDetails(Request $request)
    {
        $response['data'] = $this->service->lotDetails($request->lot_no);
        $response['master_stages'] = $this->service->master_stages();
        return view('owner.reports.lot_details', $response);
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
}
