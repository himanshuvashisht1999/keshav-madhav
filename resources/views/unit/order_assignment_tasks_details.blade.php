@extends('layouts.unit')

@section('title', 'Order Assignments')

@section('header_icon')
    <a href="javascript:history.back()" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
<style>
    .ot-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
    .ot-top h2 { font-size: 15px; font-weight: 800; color: var(--u-green); margin: 0; word-break: break-all; }
    .ot-set { border-top: 3px solid var(--u-green); margin-top: 18px; padding-top: 2px; }
    .ot-set:first-of-type { margin-top: 8px; }
    .ot-rework { background: #e11d48; color: #fff; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 3px 9px; border-radius: 10px; }
    .ot-note { background: #fffbd6; border: 1px solid #f3e27a; border-radius: 6px; padding: 9px 12px; font-size: 13px; color: #6b5a00; margin-top: 8px; }
    .ot-total { font-size: 16px; font-weight: 800; color: var(--u-green); }
    .ot-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
    .ot-actions form { margin: 0; }
    .ot-actions .xl-btn { width: 100%; height: 40px; }
</style>
@endpush

@section('content')
    @php
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        $currentView = $view ?? 'open';
    @endphp

    @if(session('error'))
        <div class="ot-note" style="background: #fee2e2; border-color: #fecaca; color: #b91c1c; margin: 0 0 10px;"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="ot-top">
        <div>
            <h2>{{ $groupLabel }}: {{ $orderSku }}</h2>
            <div class="xl-sub">{{ count($allDetails) }} order set(s)</div>
        </div>
        <a href="{{ route('unit.order-summary', ['sku' => $orderSku]) }}" class="xl-btn xl-btn-sm">
            <i class="fas fa-file-invoice"></i> Order Summary
        </a>
    </div>

    @forelse($allDetails as $detail)
        @php
            $header = $detail['header'];
            $sizeData = $detail['sizeData'];
            $transaction = $detail['transaction'];
            $isRework = $detail['isRework'];
            $endDate = $header['end_date'] ?? null;
            $completeDate = $header['complete_date'] ?? null;
            $endLate = !$completeDate && $endDate && now() > \Carbon\Carbon::parse($endDate);
            $calculatedTotal = collect($sizeData)->sum('pcs');
        @endphp

        <div class="ot-set">
            <div class="xl-head">
                <h3><i class="fas fa-info-circle"></i> CMPO-{{ $header['id'] }}</h3>
                @if($isRework)
                    <span class="ot-rework"><i class="fas fa-exclamation-triangle"></i> Rework</span>
                @elseif($completeDate)
                    <span class="xl-pill xl-pill-done">Completed</span>
                @elseif($endLate)
                    <span class="xl-pill xl-pill-late">Delayed</span>
                @else
                    <span class="xl-pill xl-pill-open">Pending</span>
                @endif
            </div>
            <div class="xl-wrap">
                <table class="xl-kv">
                    <tbody>
                        <tr><th>Design No</th><td class="xl-strong">{{ $header['design_no'] }}</td></tr>
                        <tr><th>Lot No</th><td>{{ $header['lot_no'] }}</td></tr>
                        <tr><th>Company</th><td>{{ $header['customer'] }}</td></tr>
                        <tr><th>Date</th><td>{{ $header['date'] }}</td></tr>
                        <tr><th>Fabric</th><td>{{ $header['fabric'] }}</td></tr>
                        <tr><th>Color</th><td>{{ $header['color'] }}</td></tr>
                        <tr><th>Pattern</th><td>{{ $header['pattern'] }}</td></tr>
                        <tr><th>Fitting</th><td>{{ $header['fitting'] }}</td></tr>
                        <tr><th>Belt</th><td>{{ $header['belt'] }}</td></tr>
                        @if($type === 'cutting')
                            <tr><th>Cutting Master</th><td>{{ $header['warehouse'] }} ({{ $header['unit_name'] }})</td></tr>
                        @else
                            <tr><th>From Stage</th><td>{{ $header['from_stage'] }}</td></tr>
                            <tr><th>Sent By</th><td>{{ $header['sent_by'] }}</td></tr>
                        @endif
                        <tr>
                            <th>Printing</th>
                            <td>
                                {{ $header['printing_required'] ?? 'No' }}
                                @if(isset($header['printing_required']) && $header['printing_required'] == 'Yes')
                                    - {{ $header['printing_unit'] ?? '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr><th>Size Set</th><td class="xl-strong">{{ $header['size_set'] ?? '-' }}</td></tr>
                        <tr><th>Pcs / Set</th><td>{{ $header['pcs_in_set'] ?? 0 }}</td></tr>
                        <tr><th>Grand Total</th><td class="ot-total">{{ $fmt($header['total_pcs']) }}</td></tr>
                        @if(isset($header['start_date']) || isset($header['end_date']))
                            <tr><th>Start Date</th><td>{{ $header['start_date'] ? date('d M Y, h:i A', strtotime($header['start_date'])) : '-' }}</td></tr>
                            <tr>
                                <th>Expected End</th>
                                <td class="{{ $endLate ? 'xl-late' : '' }}">
                                    {{ $endDate ? date('d M Y, h:i A', strtotime($endDate)) : '-' }}
                                    @if($endLate)
                                        <span class="xl-pill xl-pill-late">Delayed</span>
                                    @endif
                                </td>
                            </tr>
                            @if($completeDate)
                                <tr><th>Completed At</th><td class="xl-strong" style="color: #166534;">{{ date('d M Y, h:i A', strtotime($completeDate)) }}</td></tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>

            @if($header['remark'] && $header['remark'] != '-')
                <div class="ot-note"><strong>Note:</strong> {{ $header['remark'] }}</div>
            @endif

            <div class="xl-head">
                <h3><i class="fas fa-ruler-horizontal"></i> Size Breakdown</h3>
                <span class="xl-sub">{{ count($sizeData) }} size(s)</span>
            </div>
            <div class="xl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th class="xl-num">Total Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sizeData as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="xl-strong">{{ $row['size'] }}</td>
                                <td>{{ $row['color'] }}</td>
                                <td class="xl-num">{{ number_format($row['pcs'], 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="xl-empty">No sizes found for this set.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td colspan="2">Total Quantity</td>
                            <td class="xl-num">{{ number_format($calculatedTotal ?: $header['total_pcs'], 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="ot-actions">
                @if($canCloseTasks)
                    <form method="POST"
                        action="{{ $currentView === 'closed'
                            ? route('unit.assignments.reopen', ['type' => 'cutting', 'id' => $header['id']])
                            : route('unit.assignments.close', ['type' => 'cutting', 'id' => $header['id']]) }}">
                        @csrf
                        @if($currentView === 'closed')
                            <button type="submit" class="xl-btn xl-btn-outline"><i class="fas fa-undo"></i> Re-open Task</button>
                        @else
                            <button type="submit" class="xl-btn xl-btn-danger"><i class="fas fa-check"></i> Close Task</button>
                        @endif
                    </form>
                @endif
                <a href="{{ route('unit.assignments.details', ['type' => 'cutting', 'id' => $header['id']]) }}" class="xl-btn" @unless($canCloseTasks) style="grid-column: 1 / -1;" @endunless>
                    <i class="fas fa-file-invoice"></i> Full Process / Upload
                </a>
            </div>
        </div>
    @empty
        <div class="xl-panel xl-empty" style="text-align: center; padding: 30px 16px; color: var(--u-muted);">
            <i class="fas fa-box-open" style="font-size: 28px;"></i><br>
            <strong>No assignments found for this order.</strong>
        </div>
    @endforelse
@endsection
