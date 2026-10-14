@extends('layouts.unit')

@section('title', !empty($delayedOnly) ? 'Late Work' : 'My Work')

@push('styles')
<style>
    .as-tabs { display: flex; background: #fff; border: 1px solid var(--u-line); border-radius: 6px; padding: 3px; margin-bottom: 10px; }
    .as-tabs a { flex: 1; text-align: center; padding: 8px 6px; border-radius: 4px; font-size: 13px; font-weight: 700; color: var(--u-muted); text-decoration: none !important; }
    .as-tabs a.active { background: var(--u-green); color: #fff; }
    .as-tabs a.active i { color: var(--u-yellow); }
    .as-filter { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)) auto auto; gap: 8px; align-items: end; }
    .as-filter .xl-btn { height: 36px; }
    .as-filter .as-actions { display: contents; }
    .as-summary { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 13px; color: var(--u-muted); margin: 0 2px 8px; }
    .as-summary strong { color: var(--u-green); }
    .as-table tbody tr { cursor: pointer; }
    .as-table td:nth-child(2), .as-table th:nth-child(2) { position: sticky; left: 0; z-index: 1; box-shadow: 2px 0 0 var(--u-line); }
    .as-table thead th:nth-child(2) { z-index: 3; }
    .as-table tr.as-late td { background: #fff1f2 !important; }
    .as-sku { font-weight: 800; white-space: nowrap; }
    .mw-msg { border-radius: 6px; padding: 9px 12px; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .mw-msg-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .mw-msg-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    @media (max-width: 640px) {
        .as-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .as-filter .as-actions { grid-column: 1 / -1; display: flex; gap: 8px; }
        .as-filter .as-actions .xl-btn { flex: 1; }
    }
</style>
@endpush

@section('content')
    @php
        $label = $groupLabel ?? 'Order';
        $currentView = $view ?? 'open';
        $totalPcs = collect($orders)->sum('total_quantity');
        $delayedOnly = !empty($delayedOnly);
        $allCount = $allCount ?? count($orders);
        $lateCount = $lateCount ?? collect($orders)->where('is_delayed', true)->count();
        $isArchive = $currentView === 'closed';
    @endphp

    @if(session('success'))
        <div class="mw-msg mw-msg-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mw-msg mw-msg-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <!-- Open / Late / Done -->
    <div class="xl-chips">
        <a href="{{ route('unit.assignments', ['view' => 'open']) }}" class="{{ !$isArchive && !$delayedOnly ? 'active' : '' }}">
            Open @unless($isArchive)<span class="xl-chip-n">{{ $allCount }}</span>@endunless
        </a>
        <a href="{{ route('unit.assignments', ['view' => 'open', 'delayed' => 1]) }}" class="xl-chip-late {{ !$isArchive && $delayedOnly ? 'active' : '' }}">
            <i class="fas fa-exclamation-triangle"></i> Late @unless($isArchive)<span class="xl-chip-n">{{ $lateCount }}</span>@endunless
        </a>
        @if(!empty($canCloseTasks) && $canCloseTasks)
            <a href="{{ route('unit.assignments', ['view' => 'closed']) }}" class="{{ $isArchive ? 'active' : '' }}">
                <i class="fas fa-archive"></i> Closed
            </a>
        @endif
    </div>

    <form action="{{ route('unit.assignments') }}" method="GET" class="xl-panel">
        <input type="hidden" name="view" value="{{ $currentView }}">
        @if($delayedOnly)
            <input type="hidden" name="delayed" value="1">
        @endif
        <div class="as-filter">
            <div class="xl-field">
                <label for="order_no">Order No</label>
                <input type="text" id="order_no" name="order_no" placeholder="e.g. 398" value="{{ request('order_no') }}">
            </div>
            <div class="xl-field">
                <label for="lot_no">Design No</label>
                <input type="text" id="lot_no" name="lot_no" placeholder="e.g. 217788" value="{{ request('lot_no') }}">
            </div>
            <div class="as-actions">
                <button type="submit" class="xl-btn"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('unit.assignments', $delayedOnly ? ['view' => $currentView, 'delayed' => 1] : ['view' => $currentView]) }}" class="xl-btn xl-btn-outline"><i class="fas fa-undo"></i> Clear</a>
            </div>
        </div>
    </form>

    <div class="as-summary">
        <span><strong>{{ count($orders) }}</strong> {{ strtolower(Str::plural($label, count($orders))) }}</span>
        <span>Total <strong>{{ number_format($totalPcs) }}</strong> pcs</span>
        @if(count($orders))
            <span><i class="fas fa-hand-pointer"></i> Tap an {{ strtolower($label) }} to open</span>
        @endif
    </div>

    <div class="xl-wrap">
        <table class="as-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ $label }} No</th>
                    <th>Design No</th>
                    <th>Color</th>
                    <th class="xl-num">Tasks</th>
                    <th class="xl-num">Total Pcs</th>
                    <th>Start</th>
                    <th>Est. End</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $sku => $order)
                    @php $href = $order['direct_url'] ?? route('unit.assignments', ['order_sku' => $order['sku'], 'view' => $currentView]); @endphp
                    <tr data-href="{{ $href }}" class="{{ $order['is_delayed'] ? 'as-late' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td class="as-sku"><a href="{{ $href }}">{{ $order['sku'] }}</a></td>
                        <td class="xl-nowrap">{{ $order['design_no'] }}</td>
                        <td class="xl-nowrap">{{ $order['color'] }}</td>
                        <td class="xl-num">{{ $order['task_count'] }}</td>
                        <td class="xl-num xl-strong">{{ number_format((float) $order['total_quantity']) }}</td>
                        <td class="xl-nowrap">{{ $order['start_date'] ? date('d M y', strtotime($order['start_date'])) : '-' }}</td>
                        <td class="xl-nowrap {{ $order['is_delayed'] ? 'xl-late' : '' }}">
                            {{ $order['end_date'] ? date('d M y', strtotime($order['end_date'])) : '-' }}
                            @if($order['is_delayed'])
                                <span class="xl-pill xl-pill-late">Late</span>
                            @endif
                        </td>
                        <td class="xl-nowrap xl-sub">{{ $order['latest_task'] ? \Carbon\Carbon::parse($order['latest_task'])->diffForHumans() : 'Recently' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="xl-empty">
                            <strong>{{ $delayedOnly ? 'No Delayed' : 'No' }} {{ Str::plural($label) }} Found</strong><br>
                            @if($delayedOnly)
                                Nothing is past its estimated end date.
                            @elseif($currentView === 'closed')
                                Your archived {{ strtolower(Str::plural($label)) }} list is empty.
                            @else
                                You have no pending assignments at the moment.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($orders))
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td colspan="2"></td>
                        <td class="xl-num">{{ collect($orders)->sum('task_count') }}</td>
                        <td class="xl-num">{{ number_format($totalPcs) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var row = e.target.closest('.as-table tbody tr[data-href]');
        if (!row || e.target.closest('a, button, form, input')) return;
        window.location.href = row.getAttribute('data-href');
    });
</script>
@endpush
