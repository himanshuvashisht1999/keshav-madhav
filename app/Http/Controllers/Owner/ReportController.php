<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ReportService as Service;
use App\Models\FabricReceiptDetail;
use App\Models\MasterFabricWarehouse;

class ReportController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Fabric stock in 4 levels (same ReportService::stock as the admin page, so the figures are the admin's):
     * fabrics -> warehouses of one fabric -> its shipments (type=receipts) or its usages (type=usages).
     * Ajax (?page=N) returns the next page's table rows as JSON {html, next_page, left}.
     */
    public function stock(Request $request)
    {
        if ($request->filled('fabric_id') && $request->filled('type') && !in_array($request->type, ['receipts', 'usages'], true)) {
            return redirect()->route('owner.stock', $request->except(['type', 'page']));
        }

        $isUsages = $request->filled('fabric_id') && $request->type === 'usages';
        $svcRequest = $request;
        if ($isUsages) {
            // the service hands back the whole usage list in export mode: the header totals cover every row, the table is paged here
            $svcRequest = $request->duplicate();
            $svcRequest->merge(['is_export' => 1]);
        }
        $response = $this->service->stock($svcRequest);

        if ($isUsages) {
            $all = $response['data'];
            $response['usage_count'] = $all->count();
            $response['usage_meter'] = (float) $all->sum('meter');
            $response['usage_by_type'] = $all->groupBy('type_badge')->map(fn($g) => (float) $g->sum('meter'))->all();
            // fabric customers sent back on agent-order returns goes back onto the roll; the list above shows only metres out,
            // so "net used" = listed - these returns (without a warehouse filter it equals Issued)
            $whIds = array_values(array_filter((array) $request->input('warehouse_id', []), fn($v) => $v !== null && $v !== ''));
            // (joined to agent_order_returns: lines of a deleted return are left behind without one and do not count)
            $response['usage_customer_returns'] = $request->ajax() ? 0.0 : (float) \DB::table('agent_order_return_items as ri')
                ->join('agent_order_returns as r', 'r.id', '=', 'ri.agent_order_return_id')
                ->join('agent_order_fabric_items as fi', 'fi.id', '=', 'ri.item_id')
                ->where('ri.item_type', 'fabric')
                ->where('fi.fabric_id', (int) $request->fabric_id)
                ->when(!empty($whIds), fn($q) => $q->join('fabric_receipt_details as d', 'd.id', '=', 'fi.fabric_receipt_detail_id')
                    ->whereIn('d.master_fabric_warehouse_id', $whIds))
                ->sum('ri.quantity');
            $perPage = 50;
            $page = min(max(1, (int) $request->input('page', 1)), max(1, (int) ceil($all->count() / $perPage)));
            $response['data'] = new \Illuminate\Pagination\LengthAwarePaginator(
                $all->slice(($page - 1) * $perPage, $perPage)->values(),
                $all->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->except('page')]
            );
        }

        $level = $response['level'];
        $partial = ['fabrics' => 'stock_fabrics_list', 'receipts' => 'stock_receipts_list', 'usages' => 'stock_usages_list'][$level] ?? null;

        if ($request->ajax()) {
            $data = $response['data'];
            if (!$partial || !($data instanceof \Illuminate\Pagination\LengthAwarePaginator)) {
                return response()->json(['html' => '', 'next_page' => null, 'left' => 0]);
            }
            return response()->json([
                'html' => view('owner.reports.partials.' . $partial, ['data' => $data])->render(),
                'next_page' => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                'left' => max(0, $data->total() - (int) $data->lastItem()),
            ]);
        }

        $response['totals'] = $response['totals'] ?? (object) ['sum_received' => 0, 'sum_issued' => 0, 'sum_remaining' => 0];
        $response['warehouses'] = $this->service->warehouses();
        $response['vendors'] = $this->service->vendors();
        $response['partial'] = $partial;

        return view('owner.reports.stock', $response);
    }

    /**
     * Rolls list: same filters as admin Stock Rolls (fabric, warehouses, roll no, remaining from/to) plus "in stock only".
     * Header totals are one SQL sum over the whole filtered set. Ajax (?page=N) returns the next rows as JSON.
     */
    public function stockRolls(Request $request)
    {
        $query = FabricReceiptDetail::with(['fabric', 'master_fabric_warehouse', 'fabric_receipt.vendor', 'purchase_order']);

        if ($request->filled('fabric_id')) {
            $query->where('fabric_id', $request->fabric_id);
        }

        $warehouseIds = array_values(array_filter((array) $request->input('warehouse_id', []), fn($v) => $v !== null && $v !== ''));
        if (!empty($warehouseIds)) {
            $query->whereIn('master_fabric_warehouse_id', $warehouseIds);
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

        if ($request->boolean('in_stock')) {
            $query->where('remaining_quantity', '>', 0);
        }

        $totalsQuery = (clone $query)->toBase(); // taken before paginate(), which adds limit/offset to $query
        $query->orderBy('fabric_id', 'asc')->orderBy('roll_number', 'asc')->orderBy('id', 'asc');
        $rolls = $query->paginate(50)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.reports.partials.stock_rolls_list', compact('rolls'))->render(),
                'next_page' => $rolls->hasMorePages() ? $rolls->currentPage() + 1 : null,
                'left' => max(0, $rolls->total() - (int) $rolls->lastItem()),
            ]);
        }

        $totals = $totalsQuery->selectRaw('
            COUNT(*) as rolls,
            COALESCE(SUM(remaining_quantity > 0), 0) as rolls_in_stock,
            COALESCE(SUM(meter), 0) as received,
            COALESCE(SUM(remaining_quantity), 0) as remaining
        ')->first();

        $warehouses = $this->service->warehouses();
        $fabrics = $this->service->fabrics();

        return view('owner.reports.stock_rolls', compact('rolls', 'warehouses', 'fabrics', 'totals', 'warehouseIds'));
    }

    /**
     * History of one roll: arrival, cutting, agent sales (dispatched or not: the metres leave the roll when the line is made),
     * customer returns, vendor returns, disposals and warehouse moves. Oldest first with a running balance.
     * ?frd_id= tracks one roll row; ?fabric_id=&roll_no= (old links) tracks every row with that roll number.
     */
    public function stockRollTracking(Request $request)
    {
        $frdId = (int) $request->input('frd_id');
        $rollQuery = FabricReceiptDetail::with(['fabric', 'fabric_receipt.vendor', 'fabric_receipt.cutting_master', 'purchase_order', 'master_fabric_warehouse']);

        if ($frdId > 0) {
            $rolls = $rollQuery->where('id', $frdId)->get();
        } elseif ($request->filled('fabric_id') && $request->filled('roll_no')) {
            $rolls = $rollQuery->where('fabric_id', $request->fabric_id)->where('roll_number', $request->roll_no)->orderBy('id')->get();
        } else {
            return redirect()->route('owner.report.stock.rolls')->with('error', 'Pick a roll from the list to see its history.');
        }

        if ($rolls->isEmpty()) {
            return redirect()->route('owner.report.stock.rolls')->with('error', 'Roll not found.');
        }

        $receiptIds = $rolls->pluck('id')->all();
        $rollNos = $rolls->pluck('roll_number', 'id');
        $multi = $rolls->count() > 1;
        $rollRef = fn($frdId) => $multi ? ' (row ' . $frdId . ')' : '';
        $rows = collect();

        foreach ($rolls as $s) {
            $receivedAt = $s->fabric_receipt?->cutting_master?->cutting_master_name ?? ($s->master_fabric_warehouse?->cutting_master_name ?? '-');
            $rows->push((object) [
                'date' => $s->created_at,
                'type' => 'Arrival',
                'tone' => 'green',
                'in' => (float) $s->meter,
                'out' => 0.0,
                'ref' => $s->fabric_receipt?->sku ?: ($s->purchase_order?->sku ?? '-'),
                'lot' => $s->shipment_number ?: '-',
                'details' => 'Received at ' . $receivedAt . ' | Supplier: ' . ($s->fabric_receipt?->vendor?->name ?? '-')
                    . ' | Bill: ' . ($s->fabric_receipt?->bill_no ?? '-') . ' | PO: ' . ($s->purchase_order?->sku ?? '-') . $rollRef($s->id),
            ]);
        }

        $cuttings = \App\Models\FabricRollAssigning::whereIn('fabric_receipt_detail_id', $receiptIds)
            ->with(['orderProductSet.colors', 'stageMasterUnit'])
            ->get();
        foreach ($cuttings as $u) {
            $rows->push((object) [
                'date' => $u->created_at,
                'type' => 'Cutting',
                'tone' => 'grey',
                'in' => 0.0,
                'out' => (float) $u->meter,
                'ref' => $u->order_no ?: '-',
                'lot' => $u->lot_no ?: '-',
                'details' => 'Design: ' . ($u->orderProductSet?->design_number ?? '-') . ' | Colour: ' . ($u->orderProductSet?->colors?->name ?? '-')
                    . ' | Unit: ' . ($u->stageMasterUnit?->name ?? '-') . $rollRef($u->fabric_receipt_detail_id),
            ]);
        }

        $sales = \App\Models\AgentOrderFabricItem::whereIn('fabric_receipt_detail_id', $receiptIds)
            ->with(['order.shop', 'order.vendor'])
            ->get();
        foreach ($sales as $a) {
            $order = $a->order;
            $party = $order ? ($order->party_type === 'vendor' ? $order->vendor?->name : $order->shop?->name) : null;
            $rows->push((object) [
                'date' => $a->created_at,
                'type' => $a->agent_order_dispatch_id ? 'Agent sale' : 'Agent sale (not dispatched)',
                'tone' => 'yellow',
                'in' => 0.0,
                'out' => (float) $a->meter,
                'ref' => 'ORD-' . str_pad($a->agent_order_id, 5, '0', STR_PAD_LEFT),
                'lot' => '-',
                'details' => 'Party: ' . ($party ?: '-') . ' | Rate: ' . number_format((float) $a->selling_price, 2) . $rollRef($a->fabric_receipt_detail_id),
            ]);
        }

        // fabric that customers sent back on an agent-order return goes back onto the roll
        // (lines of a deleted return have no agent_order_returns row and are left out)
        $customerReturns = \DB::table('agent_order_return_items as ri')
            ->join('agent_order_fabric_items as fi', 'fi.id', '=', 'ri.item_id')
            ->join('agent_order_returns as r', 'r.id', '=', 'ri.agent_order_return_id')
            ->where('ri.item_type', 'fabric')
            ->whereIn('fi.fabric_receipt_detail_id', $receiptIds)
            ->select('ri.id', 'ri.quantity', 'ri.created_at', 'ri.agent_order_return_id', 'r.return_date', 'fi.agent_order_id', 'fi.fabric_receipt_detail_id')
            ->get();
        foreach ($customerReturns as $cr) {
            $rows->push((object) [
                'date' => \Carbon\Carbon::parse($cr->created_at ?? $cr->return_date),
                'type' => 'Customer return',
                'tone' => 'green',
                'in' => (float) $cr->quantity,
                'out' => 0.0,
                'ref' => 'SR-' . $cr->agent_order_return_id,
                'lot' => '-',
                'details' => 'Returned on order ORD-' . str_pad($cr->agent_order_id, 5, '0', STR_PAD_LEFT) . $rollRef($cr->fabric_receipt_detail_id),
            ]);
        }

        $vendorReturns = \App\Models\FabricReturnDetail::whereIn('fabric_receipt_detail_id', $receiptIds)
            ->with(['fabric_return.receipt.vendor'])
            ->get();
        foreach ($vendorReturns as $r) {
            $rows->push((object) [
                'date' => $r->created_at,
                'type' => 'Return to vendor',
                'tone' => 'red',
                'in' => 0.0,
                'out' => (float) $r->return_meter,
                'ref' => $r->fabric_return?->return_number ?? '-',
                'lot' => '-',
                'details' => 'Supplier: ' . ($r->fabric_return?->receipt?->vendor?->name ?? '-') . ' | Remarks: ' . ($r->fabric_return?->remarks ?: '-') . $rollRef($r->fabric_receipt_detail_id),
            ]);
        }

        $disposals = \DB::table('stock_disposal_items as i')
            ->join('stock_disposal_mains as m', 'i.stock_disposal_main_id', '=', 'm.id')
            ->where('m.item_type', 'fabric')
            ->whereIn('i.item_id', $receiptIds)
            ->select('i.item_id', 'i.quantity', 'i.created_at', 'm.disposal_no', 'm.reason', 'm.remarks')
            ->get();
        foreach ($disposals as $d) {
            $rows->push((object) [
                'date' => \Carbon\Carbon::parse($d->created_at),
                'type' => 'Disposal' . ($d->reason ? ' (' . $d->reason . ')' : ''),
                'tone' => 'red',
                'in' => 0.0,
                'out' => (float) $d->quantity,
                'ref' => $d->disposal_no ?: '-',
                'lot' => '-',
                'details' => 'Remarks: ' . ($d->remarks ?: '-') . $rollRef($d->item_id),
            ]);
        }

        // warehouse moves change where the roll lies, not how much is left
        $moves = \DB::table('fabric_transfer_items as ti')
            ->join('fabric_transfers as t', 'ti.fabric_transfer_id', '=', 't.id')
            ->leftJoin('master_fabric_warehouse as fw', 'fw.id', '=', 't.from_warehouse_id')
            ->leftJoin('master_fabric_warehouse as tw', 'tw.id', '=', 't.to_warehouse_id')
            ->whereIn('ti.fabric_receipt_detail_id', $receiptIds)
            ->select('ti.fabric_receipt_detail_id', 'ti.meter', 'ti.created_at', 't.transfer_no', 't.transfer_date', 'fw.cutting_master_name as from_name', 'tw.cutting_master_name as to_name')
            ->get();
        foreach ($moves as $mv) {
            $rows->push((object) [
                'date' => \Carbon\Carbon::parse($mv->created_at ?? $mv->transfer_date),
                'type' => 'Moved to ' . ($mv->to_name ?? 'warehouse'),
                'tone' => 'grey',
                'in' => 0.0,
                'out' => 0.0,
                'ref' => $mv->transfer_no ?: '-',
                'lot' => '-',
                'details' => number_format((float) $mv->meter, 2) . ' m moved from ' . ($mv->from_name ?? '-') . ' to ' . ($mv->to_name ?? '-') . $rollRef($mv->fabric_receipt_detail_id),
            ]);
        }

        // oldest first; on the same moment the arrival comes first
        $rows = $rows->sortBy(fn($r) => [\Carbon\Carbon::parse($r->date)->timestamp, $r->type === 'Arrival' ? 0 : 1])->values();
        $balance = 0.0;
        foreach ($rows as $r) {
            $balance += $r->in - $r->out;
            $r->balance = $balance;
        }

        $totals = (object) [
            'in' => (float) $rows->sum('in'),
            'out' => (float) $rows->sum('out'),
            'remaining' => (float) $rolls->sum('remaining_quantity'),
        ];
        $totals->balance = $totals->in - $totals->out;
        $totals->gap = round($totals->balance - $totals->remaining, 2);

        $fabric = $rolls->first()->fabric;
        $rollNo = $rolls->first()->roll_number;

        return view('owner.reports.stock_roll_details', compact('rows', 'rolls', 'fabric', 'rollNo', 'totals'));
    }

    /** Ids of the orders marked Completed (order_main.status 3), as array keys. */
    public static function completedOrderIds(): array
    {
        return \App\Models\OrderMain::where('status', 3)->pluck('id')->flip()->all();
    }

    /**
     * The one rule for "Lots in production" and "Delayed lots" (Home and the Lots page).
     * A lot is in production while its cut pieces are not all dispatched or put in stock (lotFacts stage 12) and its
     * order is not marked Completed; a lot whose order row is missing counts as not completed. Delayed lots = lots in
     * production whose current stage is past its planned end.
     * $lotRows = order_lots rows (lot_no, order_main_id); null = every lot. Returns the lot facts and the two counts.
     */
    public static function lotCounts(Service $service, $lotRows = null, ?array $completedOrders = null): array
    {
        $lotRows = $lotRows ?? \App\Models\OrderLot::get(['lot_no', 'order_main_id']);
        $completedOrders = $completedOrders ?? self::completedOrderIds();
        // lot numbers that sit on at least one order that is not Completed
        $openLots = [];
        foreach ($lotRows as $l) {
            if (!isset($completedOrders[$l->order_main_id])) {
                $openLots[Service::lotKeyOf($l->lot_no)] = true;
            }
        }
        $facts = collect($service->lotProgress($lotRows->pluck('lot_no')->all()));
        $inProduction = $facts->filter(fn($f, $key) => $f['stage_id'] != 12 && isset($openLots[$key]));

        return [
            'facts' => $facts,
            'in_production' => $inProduction->count(),
            'delayed' => $inProduction->where('is_delayed', 'Yes')->count(),
        ];
    }

    public function lots(Request $request)
    {
        // Lot filter is an exact match on one or more lots (lot_no[] like the admin page). A single value from an
        // old link (?lot_no=644) is matched exactly too; a plain string would make the service search "%644%".
        $lotSel = collect((array) $request->input('lot_no', []))
            ->map(fn($v) => trim((string) $v))->filter(fn($v) => $v !== '')->unique()->values()->all();
        $request->merge(['lot_no' => $lotSel]);

        $response['data'] = $this->service->orderLotsDetailed($request);
        // orders marked Completed (status 3): their unfinished lots are tagged and not counted as in production
        $response['completedOrders'] = self::completedOrderIds();

        if ($request->ajax()) {
            // "Load more": only the next rows; the header totals and dropdowns are already on the page
            return response()->json([
                'html' => view('owner.reports.partials.lots_list', $response)->render(),
                'next_page' => $response['data']->hasMorePages() ? $response['data']->currentPage() + 1 : null,
            ]);
        }

        // Header totals over every lot of the filter (not only this page), from lotFacts like the rows.
        $lotRows = \App\Models\OrderLot::query()
            ->when($lotSel, fn($q) => $q->whereIn('lot_no', $lotSel))
            ->when($request->order_id, fn($q) => $q->where('order_main_id', $request->order_id))
            ->tap(fn($q) => filterLotsBySeason($q, $request->product_season_id))
            ->get(['lot_no', 'order_main_id']);
        $lotNos = $lotRows->pluck('lot_no')->all();
        $counts = self::lotCounts($this->service, $lotRows, $response['completedOrders']);
        $facts = $counts['facts'];
        $response['totals'] = [
            'lots' => count($lotNos),
            'pieces' => (int) $facts->sum('pieces'),
            'packed' => (int) $facts->sum('packed'),
            'dispatched' => (int) $facts->sum('dispatched'),
            'in_stock' => (int) $facts->sum('in_stock'),
            // lotCounts rule (same as Home): lots of Completed orders are not in production and not delayed
            'in_production' => $counts['in_production'],
            'delayed' => $counts['delayed'],
        ];
        $response['lotNos'] = $this->service->lot_numbers();
        $response['lotSel'] = $lotSel;
        $response['seasons'] = \App\Models\ProductSeason::where('status', '!=', 3)->orderBy('name')->get();

        return view('owner.reports.lots', $response);
    }

    public function lotDetails(Request $request)
    {
        $data = $this->service->lotDetails($request->lot_no);

        if (!$data) {
            return redirect()->route('owner.lots')->with('error', 'Lot not found or no data available.');
        }

        $lotNo = $data['lot_no'];
        $lot = $data['lots_data'][0] ?? null;
        // same unit rule as the admin lot page: slip unit, else cutting master, else the order line's unit
        $unitName = $lot?->productionSlipDigitization?->getUnitMaster?->name
            ?? ($lot?->cutting_master?->name ?? ($lot?->orderProductSet?->stage_master_unit?->name ?? '-'));

        // Stage table (same rule as the admin lot page: a planned stage that no piece has reached is not shown)
        $stages = [];
        foreach ($this->service->master_stages() as $stage) {
            $d = getLotDetails($lotNo, $stage->id);
            if (!$d || !$d['time_allocation']) {
                continue;
            }
            $total = (int) $d['quantity'];
            if ($total === 0) {
                continue;
            }
            $remaining = (int) $d['remaining_quantity'];
            $eta = \Carbon\Carbon::parse($d['time_allocation']);
            $completed = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;
            if ($remaining === 0) {
                $status = ($completed && $completed->gt($eta)) ? 'late' : 'done';
            } elseif (now()->gt($eta)) {
                $status = 'delayed';
            } else {
                $status = 'progress';
            }
            $stages[] = [
                'id' => (int) $stage->id,
                'name' => $stage->name,
                'unit' => $d['unit_name'] ?? '-',
                'total' => $total,
                'remaining' => $remaining,
                'start' => $d['start_date'] ? \Carbon\Carbon::parse($d['start_date']) : null,
                'eta' => $eta,
                'completed' => ($remaining === 0 && $completed) ? $completed : null,
                'status' => $status,
            ];
        }

        // Production sessions (read only): cutting sessions, printing moves and stage transfers / rework returns
        $sessions = [];
        foreach ($data['lots'] as $s) {
            $rolls = $data['rolls']->where('order_lot_id', $s->id);
            $sessions[] = [
                'date' => $s->production_datetime ?? $s->created_at, 'type' => 'Cutting', 'rework' => false,
                'from_stage' => '-', 'to_stage' => 'Cutting', 'from_unit' => '-', 'to_unit' => $unitName,
                'pieces' => (int) $rolls->sum(fn($r) => $r->fabricRollAssigningsDetail->sum('quantity')),
                'rolls' => $rolls->count(), 'metres' => (float) $rolls->sum('meter'), 'remark' => null,
            ];
        }
        foreach ($data['printings'] as $p) {
            $sessions[] = [
                'date' => $p->production_datetime ?? $p->created_at, 'type' => 'Printing', 'rework' => false,
                'from_stage' => $p->from_stage?->name ?? 'Cutting', 'to_stage' => $p->to_stage?->name ?? '-',
                'from_unit' => $p->from_stage_id == 3 ? $unitName : ($p->getFromUnitMaster?->name ?? $unitName),
                'to_unit' => $p->getToUnitMaster?->name ?? '-',
                'pieces' => (int) $p->quantity, 'rolls' => null, 'metres' => null, 'remark' => null,
            ];
        }
        foreach ($data['stage_transactions'] as $t) {
            $isRework = ($t->type === 'rework' || (string) $t->type === '0' || stripos($t->remarks ?? '', 'rework') !== false || stripos($t->remarks ?? '', 'rewash') !== false);
            $sessions[] = [
                'date' => $t->production_datetime ?? $t->created_at, 'type' => $isRework ? 'Rework' : 'Transfer', 'rework' => $isRework,
                'from_stage' => $t->from_stage?->name ?? '-', 'to_stage' => $t->to_stage?->name ?? '-',
                'from_unit' => $t->getFromUnitMaster?->name ?? '-', 'to_unit' => $t->getToUnitMaster?->name ?? '-',
                'pieces' => (int) $t->quantity, 'rolls' => null, 'metres' => null,
                'remark' => $isRework ? ($t->remarks ?: null) : null,
            ];
        }
        $sessions = collect($sessions)->sortBy(fn($s) => (string) $s['date'])->values();

        $facts = $this->service->lotProgress([$lotNo])[Service::lotKeyOf($lotNo)] ?? null;
        $orderLot = $data['lots']->first();

        return view('owner.reports.lot_details', [
            'data' => $data,
            'lot' => $lot,
            'unitName' => $unitName,
            'stages' => $stages,
            'sessions' => $sessions,
            'facts' => $facts,
            'lotDate' => $orderLot ? ($orderLot->production_datetime ?? $orderLot->created_at) : null,
        ]);
    }

    public function lotDetailsPdf(Request $request)
    {
        $response['data'] = $this->service->lotDetails($request->lot_no);
        $response['master_stages'] = $this->service->master_stages();

        if (!$response['data']) {
            // back to the owner Lots list (back() with no previous page went to the site root, the admin login)
            return redirect()->route('owner.lots')->with('error', 'Lot not found');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.reports.lot_details_pdf', $response)->setPaper('A4', 'portrait');
        return $pdf->download('lot-details-' . $request->lot_no . '.pdf');
    }

    public function unitAssignments(Request $request)
    {
        // The whole filtered list comes back (no service paging / 5-minute cache), so the header totals cover
        // every task of the filter; the table is then paged here, 50 rows a page.
        $request->query->remove('is_pagination');
        $request->request->remove('is_pagination');
        $response = $this->service->unitAssignments($request);

        $all = collect($response['assignments'])->values();
        $isProduction = !empty($response['productionStatus']);
        $response['totals'] = [
            'tasks' => $all->count(),
            'assigned' => $all->sum(fn($i) => (float) ($i->assigned_qty ?? 0)),
            'received' => $all->sum(fn($i) => max(0, (float) ($i->assigned_qty ?? 0) - (float) ($i->pending_qty ?? 0))),
            'pending' => $all->sum(fn($i) => (float) ($i->pending_qty ?? 0)),
            'delayed' => $all->filter(fn($i) => ($i->status_text ?? '') === 'Delayed')->count(),
            'rework_pending' => $all->sum(fn($i) => (float) ($i->rework_pending_qty ?? 0)),
        ];

        // Cutting tasks often keep the design number in lot_no: link only the ones that are real lots.
        $response['realLots'] = [];
        if (($response['type'] ?? '') === 'cutting' && !$isProduction) {
            $response['realLots'] = \App\Models\OrderLot::whereIn('lot_no', $all->pluck('lot_no')->filter()->unique()->values()->all())
                ->pluck('lot_no')->mapWithKeys(fn($l) => [Service::lotKeyOf($l) => true])->all();
        }

        $perPage = 50;
        $page = min(max(1, (int) $request->input('page', 1)), max(1, (int) ceil($all->count() / $perPage)));
        $response['assignments'] = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(), $all->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        return view('owner.reports.unit_assignments', $response);
    }

    /**
     * Pieces ordered per design.
     * Sales orders = corporate and domestic orders (order_products_sets.total_quantity); the "order_type" filter
     * keeps one of the two. Domestic orders make our own stock, which the agents then sell.
     * Agent orders = agent_order_items.quantity (fabric sales have no items and no design), whatever the order type filter.
     * The dates filter the ORDER date: order_main.created_at (the order date of the admin Sales Order report)
     * and agent_orders.order_date.
     * Domestic orders saved without a design ("Domestic Design" placeholder) are one line under the ranking, not a design.
     */
    public function sellingItems(Request $request)
    {
        $date = function ($value) {
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            [$y, $m, $d] = array_map('intval', explode('-', $value));
            return checkdate($m, $d, $y) ? $value : null;
        };
        $start = $date($request->query('start_date'));
        $end = $date($request->query('end_date'));
        if ($start && $end && $start > $end) {
            [$start, $end] = [$end, $start];
        }
        $design = trim((string) $request->query('design'));
        $designLike = '%' . addcslashes($design, '%_\\') . '%';
        $orderBy = $request->query('order_by') === 'asc' ? 'asc' : 'desc';
        $orderType = in_array($request->query('order_type'), ['corporate', 'domestic'], true) ? $request->query('order_type') : '';

        $sales = \DB::table('order_products_sets as s')
            ->join('order_main as m', 'm.id', '=', 's.order_main_id')
            ->when($orderType !== '', fn ($q) => $q->where('m.order_type', $orderType))
            ->whereNotNull('s.design_number')
            ->where('s.design_number', '!=', '')
            ->when($start, fn ($q) => $q->whereDate('m.created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('m.created_at', '<=', $end))
            ->when($design !== '', fn ($q) => $q->where('s.design_number', 'like', $designLike))
            ->groupBy('s.design_number')
            // dom_qty = the domestic part of qty (pieces made for our own stock), for the double-count note
            ->selectRaw("s.design_number as design, SUM(s.total_quantity) as qty, SUM(CASE WHEN m.order_type = 'domestic' THEN s.total_quantity ELSE 0 END) as dom_qty")
            ->get();

        $agent = \DB::table('agent_order_items as i')
            ->join('agent_orders as o', 'o.id', '=', 'i.agent_order_id')
            ->whereNotNull('i.design_number')
            ->where('i.design_number', '!=', '')
            ->when($start, fn ($q) => $q->whereDate('o.order_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('o.order_date', '<=', $end))
            ->when($design !== '', fn ($q) => $q->where('i.design_number', 'like', $designLike))
            ->groupBy('i.design_number')
            ->selectRaw('i.design_number as design, SUM(i.quantity) as qty')
            ->get();

        // Merge the two lists by design (the two columns have different collations, so merge here and not in SQL;
        // the key ignores case and outer spaces, as MySQL's GROUP BY does inside each list)
        $byDesign = [];
        $domestic = []; // domestic sales-order pcs per design key
        foreach ([['sales_qty', $sales], ['agent_qty', $agent]] as [$field, $list]) {
            foreach ($list as $r) {
                $key = mb_strtoupper(trim((string) $r->design));
                if (!isset($byDesign[$key])) {
                    $byDesign[$key] = (object) ['design_number' => trim((string) $r->design), 'sales_qty' => 0, 'agent_qty' => 0, 'total_qty' => 0];
                }
                $byDesign[$key]->$field += (int) $r->qty;
                $byDesign[$key]->total_qty += (int) $r->qty;
                if ($field === 'sales_qty') {
                    $domestic[$key] = ($domestic[$key] ?? 0) + (int) $r->dom_qty;
                }
            }
        }
        // A piece made on a domestic order (our stock) and later sold by an agent is in both columns. The most that
        // can be counted twice is, per design, the smaller of its domestic pcs and its agent pcs.
        $maxDouble = 0;
        foreach ($byDesign as $key => $item) {
            $maxDouble += min($domestic[$key] ?? 0, $item->agent_qty);
        }
        // "Domestic Design" is the placeholder a domestic order gets when no design is chosen
        // (ProductOrderService::storeDomestic): its pieces stay in the totals, but it is not ranked as a design
        $noDesign = $byDesign['DOMESTIC DESIGN'] ?? null;
        unset($byDesign['DOMESTIC DESIGN']);
        $items = array_values($byDesign);
        usort($items, function ($a, $b) use ($orderBy) {
            $cmp = $orderBy === 'asc' ? $a->total_qty <=> $b->total_qty : $b->total_qty <=> $a->total_qty;
            return $cmp !== 0 ? $cmp : strnatcasecmp($a->design_number, $b->design_number);
        });
        foreach ($items as $i => $item) {
            $item->rank = $i + 1;
        }

        // Totals of the whole filtered list (not only the page), with the no-design pieces
        $totals = [
            'designs' => count($items),
            'sales' => array_sum(array_column($items, 'sales_qty')) + ($noDesign->sales_qty ?? 0),
            'agent' => array_sum(array_column($items, 'agent_qty')) + ($noDesign->agent_qty ?? 0),
            'total' => array_sum(array_column($items, 'total_qty')) + ($noDesign->total_qty ?? 0),
            'max_double' => $maxDouble,
        ];

        $perPage = 50;
        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil(count($items) / $perPage)));
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($items, ($page - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('owner.reports.selling_items', [
            'rows' => $rows,
            'noDesign' => $noDesign,
            'totals' => $totals,
            'start' => $start,
            'end' => $end,
            'design' => $design,
            'orderBy' => $orderBy,
            'orderType' => $orderType,
        ]);
    }

    /**
     * Parties that have not paid for goods we dispatched more than N days ago (default 120).
     * Owed = the party's balance when it is below 0 (Dr; ledger rule balance = credit - debit), parties of every status
     * (an inactive or deleted party that still owes us is listed with its status).
     * Parties = customers, plus vendors we sell to on agent orders (any other vendor in Dr holds an advance we paid).
     * Ageing is first-in-first-out: money received clears the oldest bills first, so what is still owed is made of the
     * NEWEST bills. Overdue = owed - the dispatch bills of the last N days (sales-order and agent-order dispatches, less
     * the sales returns of those agent-order bills), never below 0.
     * Only dispatch bills count as not yet due: other debits of the last N days (payments we made to the party,
     * journal-voucher debits) are overdue straight away.
     */
    public function delayedPayments(Request $request)
    {
        $days = (int) $request->query('days', 120);
        if ($days < 1 || $days > 3650) {
            $days = 120;
        }
        $type = in_array($request->query('type'), ['customer', 'vendor'], true) ? $request->query('type') : '';
        $search = trim((string) $request->query('search'));
        $like = '%' . addcslashes($search, '%_\\') . '%';
        $show = $request->query('show') === 'all' ? 'all' : 'overdue';
        $since = now()->subDays($days)->toDateString(); // bills on or after this date are not yet N days old

        $groups = [
            'customer' => ['label' => 'Customer', 'table' => 'master_customers', 'id_field' => 'master_customer_id'],
            'vendor' => ['label' => 'Vendor', 'table' => 'vendors', 'id_field' => 'master_vendor_id'],
        ];

        $rows = collect();
        foreach ($groups as $ptype => $g) {
            if ($type !== '' && $type !== $ptype) {
                continue;
            }
            $parties = \DB::table($g['table'])
                ->where('balance', '<', 0)
                ->when($ptype === 'vendor', fn ($q) => $q->whereIn('id', \DB::table('agent_order_dispatches')
                    ->where('status', 'dispatched')->where('party_type', 'vendor')->whereNotNull('master_vendor_id')->select('master_vendor_id')))
                ->when($search !== '', fn ($q) => $q->where('name', 'like', $like))
                ->get(['id', 'name', 'phone', 'balance', 'status']);
            if ($parties->isEmpty()) {
                continue;
            }
            $ids = $parties->pluck('id')->all();
            $idField = $g['id_field'];

            $recent = [];
            $add = function ($pairs) use (&$recent) {
                foreach ($pairs as $pid => $amount) {
                    $recent[$pid] = ($recent[$pid] ?? 0) + (float) $amount;
                }
            };

            // 1. agent order dispatch bills of the last N days
            $add(\DB::table('agent_order_dispatches')
                ->where('status', 'dispatched')->where('party_type', $ptype)
                ->whereIn($idField, $ids)->whereDate('dispatch_date', '>=', $since)
                ->groupBy($idField)->selectRaw("$idField as pid, SUM(grand_total) as amt")->pluck('amt', 'pid'));
            // 2. sales-order (corporate / domestic) dispatch bills of the last N days
            if ($ptype === 'customer') {
                $add(\DB::table('order_dispatch')
                    ->whereIn('customer_id', $ids)->whereDate('dispatch_date', '>=', $since)
                    ->groupBy('customer_id')->selectRaw('customer_id as pid, SUM(total_amount) as amt')->pluck('amt', 'pid'));
            }
            // 3. sales returns of those recent agent-order bills take the goods back, so they are no longer billed (never below 0)
            $returns = \DB::table('agent_order_returns as r')
                ->join('agent_order_dispatches as d', 'd.id', '=', 'r.agent_order_dispatch_id')
                ->where('d.status', 'dispatched')->where('d.party_type', $ptype)
                ->whereIn("d.$idField", $ids)->whereDate('d.dispatch_date', '>=', $since)
                ->groupBy("d.$idField")->selectRaw("d.$idField as pid, SUM(r.grand_total) as amt")->pluck('amt', 'pid');
            foreach ($returns as $pid => $amount) {
                $recent[$pid] = max(0, ($recent[$pid] ?? 0) - (float) $amount);
            }

            // last dispatch date (any age)
            $last = \DB::table('agent_order_dispatches')
                ->where('status', 'dispatched')->where('party_type', $ptype)->whereIn($idField, $ids)
                ->groupBy($idField)->selectRaw("$idField as pid, MAX(dispatch_date) as d")->pluck('d', 'pid')->all();
            if ($ptype === 'customer') {
                $lastOd = \DB::table('order_dispatch')->whereIn('customer_id', $ids)
                    ->groupBy('customer_id')->selectRaw('customer_id as pid, MAX(dispatch_date) as d')->pluck('d', 'pid');
                foreach ($lastOd as $pid => $d) {
                    if (!isset($last[$pid]) || $d > $last[$pid]) {
                        $last[$pid] = $d;
                    }
                }
            }

            foreach ($parties as $p) {
                $owed = round(-(float) $p->balance, 2);
                $recentAmt = round($recent[$p->id] ?? 0, 2);
                $overdue = max(0, round($owed - $recentAmt, 2));
                $rows->push((object) [
                    'type' => $ptype,
                    'type_label' => $g['label'],
                    'id' => $p->id,
                    'name' => $p->name,
                    'phone' => $p->phone,
                    'status' => (int) $p->status, // 1 active, 3 deleted, anything else inactive
                    'owed' => $owed,
                    'recent' => min($owed, $recentAmt),
                    'overdue' => $overdue,
                    'last_dispatch' => $last[$p->id] ?? null,
                ]);
            }
        }

        if ($show === 'overdue') {
            $rows = $rows->filter(fn ($r) => $r->overdue > 0);
        }
        $rows = $rows->sort(function ($a, $b) {
            return [$b->overdue, $b->owed, $a->name] <=> [$a->overdue, $a->owed, $b->name];
        })->values();

        $totals = [
            'parties' => $rows->count(),
            'customers' => $rows->where('type', 'customer')->count(),
            'vendors' => $rows->where('type', 'vendor')->count(),
            'owed' => round($rows->sum('owed'), 2),
            'overdue' => round($rows->sum('overdue'), 2),
            'recent' => round($rows->sum('recent'), 2),
        ];

        $perPage = 50;
        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($rows->count() / $perPage)));
        $parties = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('owner.reports.delayed_payments', compact('parties', 'totals', 'days', 'type', 'search', 'show', 'since'));
    }
}
