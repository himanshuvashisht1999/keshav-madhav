@extends('layouts.unit')

@section('title', $lateOnly ? 'Late Work' : 'My Work')

@push('styles')
<style>
    .mw-search { display: flex; gap: 8px; align-items: center; }
    .mw-search input { flex: 1; height: 42px; padding: 6px 12px; border: 1px solid var(--u-line); border-radius: 6px; font-size: 16px; font-weight: 700; }
    .mw-search .xl-btn { height: 42px; }
    .mw-msg { border-radius: 6px; padding: 9px 12px; font-size: 13px; font-weight: 600; margin-bottom: 10px; }
    .mw-msg-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .mw-msg-info { background: var(--u-soft); color: var(--u-green); border: 1px solid #cfe3c2; }
    .mw-summary { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 13px; color: var(--u-muted); margin: 0 2px 8px; }
    .mw-summary strong { color: var(--u-green); }
    .mw-bal { color: #b45309; }
    /* lot column stays visible while scrolling sideways */
    .mw-table th:first-child, .mw-table td:first-child { position: sticky; left: 0; z-index: 1; box-shadow: 2px 0 0 var(--u-line); }
    .mw-table thead th:first-child { z-index: 3; }
    .mw-lot { font-size: 15px; font-weight: 800; color: var(--u-green); white-space: nowrap; }
    .mw-empty i { font-size: 30px; color: var(--u-word); display: block; margin-bottom: 6px; }
</style>
@endpush

@section('content')
    @php
        $totalBalance = $grouped->sum('total_pending');
        $searching = request()->filled('lot_no');
        $baseParams = $lateOnly ? ['is_delayed' => 1] : [];
    @endphp

    @if(session('error'))
        <div class="mw-msg mw-msg-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <!-- All / Late -->
    <div class="xl-chips">
        <a href="{{ route('unit.pending-tasks', request()->only('lot_no')) }}" class="{{ $lateOnly ? '' : 'active' }}">
            All lots <span class="xl-chip-n">{{ $allCount }}</span>
        </a>
        <a href="{{ route('unit.pending-tasks', ['is_delayed' => 1] + request()->only('lot_no')) }}" class="xl-chip-late {{ $lateOnly ? 'active' : '' }}">
            <i class="fas fa-exclamation-triangle"></i> Late <span class="xl-chip-n">{{ $lateCount }}</span>
        </a>
    </div>

    <!-- Lot search -->
    <form action="{{ route('unit.pending-tasks') }}" method="GET" class="xl-panel">
        @if($lateOnly)
            <input type="hidden" name="is_delayed" value="1">
        @endif
        <div class="mw-search">
            <input type="text" name="lot_no" placeholder="Search lot no" value="{{ request('lot_no') }}" inputmode="search">
            <button type="submit" class="xl-btn"><i class="fas fa-search"></i> Find</button>
            @if($searching)
                <a href="{{ route('unit.pending-tasks', $baseParams) }}" class="xl-btn xl-btn-outline" title="Show all"><i class="fas fa-times"></i></a>
            @endif
        </div>
    </form>

    @if($grouped->isNotEmpty())
        <div class="mw-summary">
            <span><strong>{{ $grouped->count() }}</strong> lot(s)</span>
            <span>Balance to deliver <strong class="mw-bal">{{ number_format($totalBalance) }}</strong> pcs</span>
            <span><i class="fas fa-hand-pointer"></i> Tap a lot to see details</span>
        </div>
    @endif

    <div class="xl-wrap">
        <table class="mw-table">
            <thead>
                <tr>
                    <th>Lot No</th>
                    <th>Design No</th>
                    <th class="xl-num">Balance</th>
                    <th>Due Date</th>
                    <th>Size Set</th>
                    <th class="xl-num">Received</th>
                    <th class="xl-num">Done</th>
                    <th>From</th>
                    <th>Received On</th>
                    <th class="xl-num">Cut Pcs</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($grouped as $task)
                    <tr class="xl-tap {{ $task['is_delayed'] ? 'xl-late-row' : '' }}" data-href="{{ route('unit.work.lot', ['lot_no' => $task['lot_no']]) }}">
                        <td class="mw-lot">{{ $task['lot_no'] }}</td>
                        <td class="xl-nowrap">{{ $task['design_no'] }}</td>
                        <td class="xl-num xl-big mw-bal">{{ number_format($task['total_pending']) }}</td>
                        <td class="xl-nowrap {{ $task['is_delayed'] ? 'xl-late' : '' }}">
                            {{ $task['estimated_date'] ? $task['estimated_date']->format('d M y') : '-' }}
                            @if($task['is_delayed'])
                                <span class="xl-pill xl-pill-late">Late</span>
                            @endif
                        </td>
                        <td class="xl-nowrap">{{ $task['size_set'] }}</td>
                        <td class="xl-num">{{ number_format($task['total_assigned']) }}</td>
                        <td class="xl-num">{{ number_format($task['total_assigned'] - $task['total_pending']) }}</td>
                        <td class="xl-nowrap">{{ $task['sent_by'] }}</td>
                        <td class="xl-nowrap">{{ $task['assigned_date'] ? \Carbon\Carbon::parse($task['assigned_date'])->format('d M y') : '-' }}</td>
                        <td class="xl-num">{{ number_format($task['total_cutting_pieces']) }}</td>
                        <td><i class="fas fa-chevron-right"></i></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="xl-empty mw-empty">
                            @if($searching)
                                <i class="fas fa-search" style="color: var(--u-muted);"></i>
                                <strong>Lot {{ request('lot_no') }} is not in your {{ $lateOnly ? 'late ' : '' }}work.</strong>
                            @elseif($lateOnly)
                                <i class="fas fa-check-circle"></i>
                                <strong>Nothing is late. Good work!</strong>
                            @else
                                <i class="fas fa-check-circle"></i>
                                <strong>No work pending right now.</strong>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($grouped->count() > 1)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td></td>
                        <td class="xl-num">{{ number_format($totalBalance) }}</td>
                        <td colspan="2"></td>
                        <td class="xl-num">{{ number_format($grouped->sum('total_assigned')) }}</td>
                        <td class="xl-num">{{ number_format($grouped->sum('total_assigned') - $totalBalance) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var row = e.target.closest('.mw-table tr.xl-tap[data-href]');
        if (row) window.location.href = row.getAttribute('data-href');
    });
</script>
@endpush
