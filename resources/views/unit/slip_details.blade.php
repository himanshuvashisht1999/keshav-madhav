@extends('layouts.unit')

@section('title', 'Slip Details')

@section('header_icon')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('unit.dashboard') }}" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
<style>
    .sd-lots { color: var(--u-green); font-weight: 700; word-break: normal; overflow-wrap: normal; }
    .sd-sched td:first-child { min-width: 76px; }
    body.unit-app .app-content .sd-sched th,
    body.unit-app .app-content .sd-sched td { padding-left: 7px !important; padding-right: 7px !important; }
    .sd-img-missing { display: none; padding: 20px; color: var(--u-muted); }
    .sd-total { font-size: 15px; font-weight: 800; color: var(--u-green); }
    .sd-size-col { min-width: 52px; }
    .sd-sizes { min-width: 200px; font-size: 12px; }
    .sd-table td:nth-child(2), .sd-table th:nth-child(2) { position: sticky; left: 0; z-index: 1; box-shadow: 2px 0 0 var(--u-line); }
    .sd-table thead th:nth-child(2) { z-index: 3; }
    .sd-prev img { max-width: 100%; max-height: 420px; border: 1px solid var(--u-line); border-radius: 6px; cursor: pointer; }
</style>
@endpush

@section('content')
    @php
        $cleanSlipNo = str_replace('T', '', $slip_id);
        $totalPieces = 0;
        $lots = [];
        $isCompleted = true;
        $isCutting = $unit->master_stage_id == 1; // Assuming 1 is STAGE_CUTTING

        foreach($transactions as $tx) {
            $totalPieces += ($tx->quantity ?? $tx->remaining_quantity ?? 0);
            if ($tx->lot_no) $lots[] = $tx->lot_no;

            $status = !empty($tx->image) || $tx->is_closed_for_unit == 1;
            if (!$status) $isCompleted = false;
        }

        $lotString = empty($lots) ? '-' : implode(', ', array_unique($lots));

        $firstTx = $transactions->first();

        $dateGroups = [];
        $hasDelay = false;
        foreach($transactions as $tx) {
            $isTaskCompleted = !empty($tx->image) || $tx->is_closed_for_unit == 1;
            $tStartDate = $tx->timing->start_date ?? $tx->start_date ?? $tx->timing->created_at ?? null;
            $tEndDate = $tx->timing->end_date ?? $tx->end_date ?? null;

            if ($isTaskCompleted) {
                $tStatus = 'Completed';
            } elseif ($tEndDate && now()->startOfDay() > \Carbon\Carbon::parse($tEndDate)->startOfDay()) {
                $tStatus = 'Delayed';
                $hasDelay = true;
            } else {
                $tStatus = 'Pending';
            }

            $key = $tStartDate . '|' . $tEndDate . '|' . $tStatus;
            if (!isset($dateGroups[$key])) {
                $dateGroups[$key] = [
                    'lots' => [],
                    'start_date' => $tStartDate,
                    'end_date' => $tEndDate,
                    'status' => $tStatus
                ];
            }
            if ($tx->lot_no) {
                $dateGroups[$key]['lots'][] = $tx->lot_no;
            }
        }

        // One sheet row per lot; every size found on the slip becomes its own column
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        $sizeCols = [];
        $lotRows = [];
        foreach($transactions as $tx) {
            $data = $tx->productSet ?? $tx->orderProduct?->orderProductSet;
            $designNo = $data->design_number ?? '-';
            $sizeSetName = $data->size_set_name ?? '-';

            if ($designNo === '-' && !empty($tx->lot_no)) {
                $lRef = \App\Models\OrderLot::where('lot_no', $tx->lot_no)->with('orderProductSet')->first();
                $designNo = $lRef->orderProductSet->design_number ?? '-';
                $sizeSetName = $lRef->orderProductSet->size_set_name ?? '-';
            }

            $sizes = [];
            foreach (collect($tx->details ?? []) as $detail) {
                $size = trim((string) ($detail->size ?? '')) ?: '-';
                $sizes[$size] = ($sizes[$size] ?? 0) + (float) ($detail->quantity ?? 0);
                if (!in_array($size, $sizeCols, true)) $sizeCols[] = $size;
            }

            $lotRows[] = [
                'lot_no' => $tx->lot_no ?: '-',
                'design_no' => $designNo,
                'size_set' => $sizeSetName,
                'sizes' => $sizes,
                'pcs' => $tx->quantity ?? $tx->remaining_quantity ?? 0,
            ];
        }
        // Up to 8 sizes -> one column per size; more -> one "Sizes" column with "size: qty" text
        $pivot = count($sizeCols) <= 8;
        $sizeText = fn(array $sizes) => collect($sizes)->map(fn($q, $s) => $s . ': ' . $fmt($q))->implode(', ') ?: '-';
        $hasSizes = count($sizeCols) > 0;
        if (!$pivot) $sizeCols = [];
        $sizeTotals = [];
        foreach ($sizeCols as $size) {
            $sizeTotals[$size] = collect($lotRows)->sum(fn($r) => $r['sizes'][$size] ?? 0);
        }
    @endphp

    <!-- Slip summary -->
    <div class="xl-head">
        <h3><i class="fas fa-file-invoice"></i> Slip #{{ $cleanSlipNo }}</h3>
        <a href="{{ route('unit.download.slip', str_replace('T', '', $slip_id)) }}" target="_blank" download="slip_{{ $slip_id }}.pdf" class="xl-btn xl-btn-sm">
            <i class="fas fa-download"></i> Download PDF
        </a>
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                @if(!$isCutting)
                    <tr><th>From Stage</th><td>{{ $firstTx->from_stage->name ?? '-' }}</td></tr>
                    <tr><th>Sent By</th><td>{{ $firstTx->getFromUnitMaster->name ?? '-' }}</td></tr>
                @endif
                <tr><th>Lots</th><td class="sd-lots">{{ $lotString }}</td></tr>
                <tr><th>Total Pieces</th><td class="sd-total">{{ $fmt($totalPieces) }}</td></tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($isCompleted)
                            <span class="xl-pill xl-pill-done">Completed</span>
                        @elseif($hasDelay)
                            <span class="xl-pill xl-pill-late">Delayed</span>
                        @else
                            <span class="xl-pill xl-pill-open">Pending</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Dates per lot group -->
    <div class="xl-head">
        <h3><i class="fas fa-calendar-alt"></i> Schedule</h3>
    </div>
    <div class="xl-wrap">
        <table class="sd-sched">
            <thead>
                <tr>
                    <th>Lots</th>
                    <th>Assigned</th>
                    <th>Est. Complete</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dateGroups as $group)
                    <tr>
                        <td class="sd-lots">{{ empty($group['lots']) ? '-' : implode(', ', array_unique($group['lots'])) }}</td>
                        <td class="xl-nowrap">{{ $group['start_date'] ? date('d M y', strtotime($group['start_date'])) : '-' }}</td>
                        <td class="xl-nowrap {{ $group['status'] === 'Delayed' ? 'xl-late' : '' }}">{{ $group['end_date'] ? date('d M y', strtotime($group['end_date'])) : '-' }}</td>
                        <td>
                            @if($group['status'] === 'Delayed')
                                <span class="xl-pill xl-pill-late">Delayed</span>
                            @elseif($group['status'] === 'Completed')
                                <span class="xl-pill xl-pill-done">Completed</span>
                            @else
                                <span class="xl-pill xl-pill-open">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Lot breakdown: one row per lot, one column per size -->
    <div class="xl-head">
        <h3><i class="fas fa-list-ul"></i> Lot Breakdown</h3>
        <span class="xl-sub">{{ count($lotRows) }} lot(s)</span>
    </div>
    <div class="xl-wrap">
        <table class="sd-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lot No</th>
                    <th>Design No</th>
                    <th>Size Set</th>
                    @foreach($sizeCols as $size)
                        <th class="xl-num sd-size-col">{{ $size }}</th>
                    @endforeach
                    <th class="xl-num">Total Pcs</th>
                    @unless($pivot)<th>Sizes</th>@endunless
                </tr>
            </thead>
            <tbody>
                @forelse($lotRows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="xl-strong xl-nowrap">{{ $row['lot_no'] }}</td>
                        <td class="xl-nowrap">{{ $row['design_no'] }}</td>
                        <td class="xl-nowrap">{{ $row['size_set'] }}</td>
                        @foreach($sizeCols as $size)
                            <td class="xl-num">{{ isset($row['sizes'][$size]) ? $fmt($row['sizes'][$size]) : '-' }}</td>
                        @endforeach
                        <td class="xl-num xl-strong">{{ $fmt($row['pcs']) }}</td>
                        @unless($pivot)<td class="sd-sizes">{{ $sizeText($row['sizes']) }}</td>@endunless
                    </tr>
                @empty
                    <tr><td colspan="{{ 5 + count($sizeCols) + ($pivot ? 0 : 1) }}" class="xl-empty">No lots on this slip.</td></tr>
                @endforelse
            </tbody>
            @if(count($lotRows) > 1 || count($sizeCols))
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        @foreach($sizeCols as $size)
                            <td class="xl-num">{{ $fmt($sizeTotals[$size]) }}</td>
                        @endforeach
                        <td class="xl-num">{{ $fmt($totalPieces) }}</td>
                        @unless($pivot)<td></td>@endunless
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if(!$hasSizes && count($lotRows))
        <div class="xl-sub" style="margin: 6px 2px 0;">No size breakdown available for this slip.</div>
    @endif

    @if($previousSlip && $previousSlip->slip_file)
        <div class="xl-head">
            <h3><i class="fas fa-image"></i> Previous Stage Slip</h3>
        </div>
        <div class="xl-panel sd-prev text-center">
            <img src="{{ asset('assets/production_slips/' . $previousSlip->slip_file) }}" alt="Previous stage slip" onclick="window.open(this.src, '_blank')"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='none'; this.parentNode.querySelector('.sd-img-missing').style.display='block';">
            <div class="xl-sub" style="margin-top: 6px;">Tap image to view full screen</div>
            <div class="sd-img-missing"><i class="fas fa-image"></i> Slip image file not found.</div>
        </div>
    @endif
@endsection
