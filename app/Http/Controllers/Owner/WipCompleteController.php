<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Owner WIP Complete: every lot of a customer (or of all customers) with the pieces In / Done / WIP at each stage.
 * Same lots and figures as the admin WIP Complete report (ReportService::wipCompleteLots + lotProgress + lotStageFigures),
 * read only, a few batched queries for any number of lots.
 */
class WipCompleteController extends Controller
{
    private const PER_PAGE = 50;
    /** stages whose WIP is pieces in production (Stitching .. Packing); Printing (1) and Godam (13) hold embroidery parts */
    private const PRODUCTION_STAGES = [4, 5, 6, 7, 8, 9, 10, 11];
    private const PARTS_STAGES = [1, 13];

    protected ReportService $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $customers = $this->service->customers();
        $customerId = $request->filled('customer_id') && $customers->contains('id', (int) $request->customer_id) ? (int) $request->customer_id : null;
        $status = in_array($request->status, ['running', 'late', 'done'], true) ? $request->status : '';

        $wip = $this->service->wipCompleteLots(
            $customerId,
            $customerId ? (array) $request->input('order_ids', []) : [],
            $customerId ? (array) $request->input('design_nos', []) : [],
            $customerId ? (array) $request->input('lot_nos', []) : []
        );
        $selected = $wip['selected'];

        // stages in flow order: Printing, Godam (embroidery parts store), then Stitching .. Packing
        $stages = $this->service->master_stages();
        $printingSeq = (float) ($stages->firstWhere('id', 1)->sequence ?? 3);
        $stages = $stages->sortBy(fn($s) => (int) $s->id === 13 ? $printingSeq + 0.5 : (float) $s->sequence)->values();
        $stageIds = $stages->pluck('id')->map(fn($id) => (int) $id)->all();

        $lotNos = array_map(fn($r) => $r['lot']->lot_no, $wip['rows']);
        $facts = $this->service->lotProgress($lotNos);
        $figures = $this->service->lotStageFigures($lotNos, $stageIds);
        $customerNames = $customerId ? [] : \App\Models\MasterCustomer::whereIn('id', collect($wip['rows'])->pluck('order.master_customer_id')->unique()->filter()->values())
            ->pluck('name', 'id')->all();

        $rows = [];
        foreach ($wip['rows'] as $r) {
            $key = ReportService::lotKeyOf($r['lot']->lot_no);
            $f = $facts[$key] ?? null;
            $isDone = $f && (int) $f['stage_id'] === 12;
            $isLate = !$isDone && $f && $f['is_delayed'] === 'Yes';
            $rowStatus = $isDone ? 'done' : ($isLate ? 'late' : 'running');
            if ($status !== '' && $status !== $rowStatus && !($status === 'running' && $rowStatus === 'late')) {
                continue;
            }
            $cells = [];
            foreach ($stageIds as $sid) {
                $d = $figures[$key][$sid] ?? null;
                // as the admin report: a stage without a plan (timing end date) shows '-'
                $cells[$sid] = ($d && $d['time_allocation'])
                    ? ['in' => (int) $d['quantity'], 'done' => (int) $d['done'], 'wip' => (int) $d['remaining_quantity']]
                    : null;
            }
            $rows[] = [
                'lot_no' => $r['lot']->lot_no,
                'order_no' => $r['order']->sku,
                'order_date' => $r['order']->created_at,
                'customer' => $customerNames[$r['order']->master_customer_id] ?? null,
                'design' => $r['set']->design_number,
                'pieces' => (int) ($f['pieces'] ?? 0),
                'packed' => (int) ($f['packed'] ?? 0),
                'dispatched' => (int) ($f['dispatched'] ?? 0),
                'in_stock' => (int) ($f['in_stock'] ?? 0),
                'stage_name' => $f['stage_name'] ?? 'Cutting',
                'status' => $rowStatus,
                'allowed_till' => $f['allowed_till'] ?? null,
                'cells' => $cells,
            ];
        }

        // totals of every filtered lot (not just the page shown), and the stage columns any lot has figures in
        $totals = ['lots' => count($rows), 'pieces' => 0, 'packed' => 0, 'dispatched' => 0, 'in_stock' => 0,
            'wip' => 0, 'parts' => 0, 'late' => 0, 'done' => 0];
        $stageTotals = array_fill_keys($stageIds, ['in' => 0, 'done' => 0, 'wip' => 0]);
        foreach ($rows as $row) {
            $totals['pieces'] += $row['pieces'];
            $totals['packed'] += $row['packed'];
            $totals['dispatched'] += $row['dispatched'];
            $totals['in_stock'] += $row['in_stock'];
            $totals['late'] += $row['status'] === 'late' ? 1 : 0;
            $totals['done'] += $row['status'] === 'done' ? 1 : 0;
            foreach ($row['cells'] as $sid => $c) {
                if (!$c) {
                    continue;
                }
                foreach (['in', 'done', 'wip'] as $k) {
                    $stageTotals[$sid][$k] += $c[$k];
                }
                if (in_array($sid, self::PRODUCTION_STAGES, true)) {
                    $totals['wip'] += $c['wip'];
                } elseif (in_array($sid, self::PARTS_STAGES, true)) {
                    $totals['parts'] += $c['wip'];
                }
            }
        }
        $stages = $stages->filter(fn($s) => $stageTotals[(int) $s->id]['in'] > 0 || $stageTotals[(int) $s->id]['wip'] > 0)->values();

        $page = min(max(1, (int) $request->input('page', 1)), max(1, (int) ceil(count($rows) / self::PER_PAGE)));
        $data = new LengthAwarePaginator(array_slice($rows, ($page - 1) * self::PER_PAGE, self::PER_PAGE), count($rows), self::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->query()]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.reports.partials.wip_complete_rows', ['data' => $data, 'stages' => $stages, 'showCustomer' => !$customerId])->render(),
                'next_page' => $data->hasMorePages() ? $data->currentPage() + 1 : null,
            ]);
        }

        return view('owner.reports.wip_complete', [
            'customers' => $customers,
            'customerId' => $customerId,
            'status' => $status,
            'options' => $wip['options'],
            'selected' => $selected,
            'stages' => $stages,
            'stageTotals' => $stageTotals,
            'totals' => $totals,
            'data' => $data,
            'showCustomer' => !$customerId,
        ]);
    }
}
