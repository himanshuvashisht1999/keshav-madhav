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

        if ($request->ajax()) {
            $view = '';
            $nextPage = null;

            if ($response['level'] === 'fabrics') {
                $view = 'owner.reports.partials.stock_fabrics_list';
            } elseif ($response['level'] === 'receipts') {
                $view = 'owner.reports.partials.stock_receipts_list';
            } elseif ($response['level'] === 'usages') {
                $view = 'owner.reports.partials.stock_usages_list';
            }

            if ($view && isset($response['data']) && method_exists($response['data'], 'hasMorePages')) {
                $nextPage = $response['data']->hasMorePages() ? $response['data']->currentPage() + 1 : null;
                return response()->json([
                    'html' => view($view, ['data' => $response['data']])->render(),
                    'next_page' => $nextPage
                ]);
            }
            return response()->json(['html' => '', 'next_page' => null]);
        }

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

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.reports.partials.stock_rolls_list', compact('rolls'))->render(),
                'next_page' => $rolls->hasMorePages() ? $rolls->currentPage() + 1 : null
            ]);
        }

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
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.reports.partials.lots_list', $response)->render(),
                'next_page' => $response['data']->hasMorePages() ? $response['data']->currentPage() + 1 : null
            ]);
        }
        
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

    public function unitAssignments(Request $request)
    {
        $response = $this->service->unitAssignments($request);
        return view('owner.reports.unit_assignments', $response);
    }

    public function sellingItems(Request $request)
    {
        $salesQuery = \DB::table('order_products')
            ->select('design_number', \DB::raw('SUM(quantity) as sales_qty'))
            ->whereNotNull('design_number')
            ->groupBy('design_number');

        $agentQuery = \DB::table('agent_order_items')
            ->select('design_number', \DB::raw('SUM(quantity) as agent_qty'))
            ->whereNotNull('design_number')
            ->groupBy('design_number');

        if ($request->filled('start_date')) {
            $salesQuery->whereDate('created_at', '>=', $request->start_date);
            $agentQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $salesQuery->whereDate('created_at', '<=', $request->end_date);
            $agentQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $salesOrders = $salesQuery->pluck('sales_qty', 'design_number')->toArray();
        $agentOrders = $agentQuery->pluck('agent_qty', 'design_number')->toArray();

        $allDesigns = array_unique(array_merge(array_keys($salesOrders), array_keys($agentOrders)));

        $sellingItems = [];
        foreach ($allDesigns as $design) {
            if (empty($design)) continue;
            
            $sQty = $salesOrders[$design] ?? 0;
            $aQty = $agentOrders[$design] ?? 0;
            
            $sellingItems[] = (object)[
                'design_number' => $design,
                'sales_qty' => $sQty,
                'agent_qty' => $aQty,
                'total_qty' => $sQty + $aQty
            ];
        }

        $orderBy = $request->get('order_by', 'desc');

        usort($sellingItems, function($a, $b) use ($orderBy) {
            if ($orderBy === 'asc') {
                return $a->total_qty <=> $b->total_qty;
            }
            return $b->total_qty <=> $a->total_qty;
        });

        // Add rank for display so pagination doesn't reset it
        foreach ($sellingItems as $index => $item) {
            $item->rank = $index + 1;
        }

        $perPage = 15;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $totalItems = count($sellingItems);
        
        $pagedItems = array_slice($sellingItems, $offset, $perPage);
        $hasMore = ($offset + $perPage) < $totalItems;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.reports.partials.selling_items_list', ['items' => $pagedItems])->render(),
                'hasMore' => $hasMore
            ]);
        }

        // Calculate grand totals for the summary (using all items, not just paged)
        $grandSales = array_sum(array_column($sellingItems, 'sales_qty'));
        $grandAgent = array_sum(array_column($sellingItems, 'agent_qty'));
        $grandTotal = array_sum(array_column($sellingItems, 'total_qty'));

        return view('owner.reports.selling_items', [
            'items' => collect($pagedItems),
            'filters' => $request->all(),
            'hasMore' => $hasMore,
            'grandSales' => $grandSales,
            'grandAgent' => $grandAgent,
            'grandTotal' => $grandTotal,
            'totalItemsCount' => $totalItems
        ]);
    }

    public function delayedPayments(Request $request)
    {
        $thresholdDate = now()->subDays(120)->format('Y-m-d');
        
        $customerIds1 = \App\Models\OrderDispatch::whereDate('dispatch_date', '<', $thresholdDate)
            ->pluck('customer_id')->toArray();
        $customerIds2 = \App\Models\AgentOrderDispatch::where('party_type', 'customer')
            ->whereDate('dispatch_date', '<', $thresholdDate)
            ->pluck('master_customer_id')->toArray();
            
        $customerIds = array_unique(array_merge($customerIds1, $customerIds2));
        
        $parties = collect();
        
        if (!empty($customerIds)) {
            $masterCustomer = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\MasterCustomer')->first();
            $customers = \App\Models\MasterCustomer::whereIn('id', $customerIds)->get();
            foreach ($customers as $v) {
                // Check if they owe money (debit)
                if ($v->balance > 0) {
                    $v->party_type = strtolower($masterCustomer->name ?? 'customer');
                    $v->master_id_val = $masterCustomer->id ?? 18;
                    $parties->push($v);
                }
            }
        }
        
        $vendorIds = \App\Models\AgentOrderDispatch::where('party_type', 'vendor')
            ->whereDate('dispatch_date', '<', $thresholdDate)
            ->pluck('master_vendor_id')->toArray();
            
        if (!empty($vendorIds)) {
            $masterVendor = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\Vendor')->first();
            $vendors = \App\Models\Vendor::whereIn('id', $vendorIds)->get();
            foreach ($vendors as $v) {
                if ($v->balance > 0) {
                    $v->party_type = strtolower($masterVendor->name ?? 'vendor');
                    $v->master_id_val = $masterVendor->id ?? 19;
                    $parties->push($v);
                }
            }
        }
        
        $masters = \App\Models\AdjustmentMaster::where('status', 1)->get();
        $parties = $parties->sortBy('name');
        
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $paginatedParties = new \Illuminate\Pagination\LengthAwarePaginator(
            $parties->slice($offset, $perPage)->values(),
            $parties->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $pageTitle = 'Delayed Payments (>120 Days)';
        $pageSubtitle = 'Parties with debit balance and old dispatches';
        
        return view('owner.party-ledger.index', [
            'parties' => $paginatedParties, 
            'masters' => $masters, 
            'pageTitle' => $pageTitle, 
            'pageSubtitle' => $pageSubtitle
        ]);
    }
}
