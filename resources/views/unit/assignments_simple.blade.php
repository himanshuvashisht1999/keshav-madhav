@extends('layouts.unit')

@section('title', isset($isHistory) && $isHistory ? 'History' : 'Assignments')

@push('styles')
    <style>
        .tk-filter { display: grid; grid-template-columns: 2fr repeat(3, minmax(0, 1fr)); gap: 8px; }
        .tk-filter label { display: block; font-size: 11px; font-weight: 700; color: var(--u-muted); text-transform: uppercase; margin-bottom: 3px; }
        .tk-filter input, .tk-filter select { width: 100%; height: 34px; padding: 4px 10px; border: 1px solid var(--u-line); border-radius: 6px; font-size: 14px; background: #fff; }
        .tk-count { font-size: 13px; color: var(--u-muted); margin: 0 2px 8px; }
        .tk-count strong { color: var(--u-green); }
        .tk-table td.tk-slip { font-weight: 800; white-space: nowrap; }
        .tk-table .tk-slip-link { display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border-radius: 6px; background: var(--u-green); color: #fff !important; text-decoration: none; }
        .tk-table .tk-slip-link i { font-size: 10px; color: var(--u-yellow); }
        .tk-table .tk-slip-link:hover { background: var(--u-green-2); }
        .tk-table td.tk-lots { font-weight: 700; color: var(--u-green); min-width: 90px; }
        .tk-table td.tk-stage { white-space: nowrap; font-size: 12px; }
        .tk-table td.tk-stage i { color: var(--u-lime); margin: 0 3px; }
        .tk-table tbody { border-top: 2px solid var(--u-green); }
        .tk-table td.tk-late { color: #b91c1c; font-weight: 700; }
        @media (max-width: 640px) {
            .tk-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .tk-filter .tk-search { grid-column: 1 / -1; }
        }
    </style>
@endpush
@section('content')
    @if(session('success'))
        <div class="alert alert-success py-2 mb-2"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 mb-2"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <div class="xl-panel">
        <div class="tk-filter">
            <div class="tk-search">
                <label for="filter-lot">Search</label>
                <input type="text" id="filter-lot" placeholder="Search lot no...">
            </div>
            @if(isset($isHistory) && $isHistory)
                @php $activity = request('activity', 'received'); @endphp
                <div>
                    <label>Type</label>
                    <select onchange="window.location.href=this.value">
                        <option value="{{ route('unit.history', ['activity' => 'received']) }}" {{ $activity === 'received' ? 'selected' : '' }}>Received Tasks</option>
                        <option value="{{ route('unit.history', ['activity' => 'sent']) }}" {{ $activity === 'sent' ? 'selected' : '' }}>Sent Tasks</option>
                    </select>
                </div>
            @endif
            <div>
                <label for="filter-from-stage">From Stage</label>
                <select id="filter-from-stage"><option value="">All</option></select>
            </div>
            <div>
                <label for="filter-to-stage">To Stage</label>
                <select id="filter-to-stage"><option value="">All</option></select>
            </div>
        </div>
    </div>

    <div class="tk-count"><strong id="tk-visible">{{ $groupedAssignments->count() }}</strong> slip(s)</div>

    <div class="xl-wrap">
        <table class="tk-table">
            <thead>
                <tr>
                    <th>Slip</th>
                    <th>Lots</th>
                    <th>Status</th>
                    <th>Est. Complete</th>
                    <th class="xl-num">Pieces</th>
                    <th>Assigned</th>
                    @if(!$isCutting)
                        <th>From &rarr; To</th>
                        <th>Sent By</th>
                    @endif
                </tr>
            </thead>
            @forelse($groupedAssignments as $slipNo => $tasks)
                @php
                    $firstTask = $tasks->first();
                    
                    $allCompleted = true;
                    $anyDelayed = false;
                    $totalPieces = 0;
                    $lots = [];
                    
                    $fromStageName = $firstTask->from_stage->name ?? 'Admin Assignment';
                    $toStageName = $firstTask->to_stage->name ?? 'Next Stage';

                    foreach($tasks as $task) {
                        $isCompleted = !empty($task->image) || $task->is_closed_for_unit == 1;
                        if (!$isCompleted) $allCompleted = false;
                        
                        $endDate = $task->timing->end_date ?? $task->end_date ?? null;
                        if (!$isCompleted && $endDate && now()->startOfDay() > \Carbon\Carbon::parse($endDate)->startOfDay()) {
                            $anyDelayed = true;
                        }
                        
                        $totalPieces += ($task->quantity ?? $task->remaining_quantity ?? 0);
                        if ($task->lot_no) {
                            $lots[] = $task->lot_no;
                        }
                    }
                    
                    if ($allCompleted) {
                        $statusClass = 'status-completed';
                        $statusText = 'Completed';
                    } elseif ($anyDelayed) {
                        $statusClass = 'status-delayed';
                        $statusText = 'Delayed';
                    } else {
                        $statusClass = 'status-pending';
                        $statusText = 'Pending';
                    }
                    
                    
                    $lotString = empty($lots) ? '-' : implode(', ', array_unique($lots));
                    $cleanSlipNo = str_replace('T', '', $slipNo);
                    
                    // Grouping for Third Row
                    $dateGroups = [];
                    foreach($tasks as $task) {
                        $isTaskCompleted = !empty($task->image) || $task->is_closed_for_unit == 1;
                        $tStartDate = $task->timing->start_date ?? $task->start_date ?? null;
                        $tEndDate = $task->timing->end_date ?? $task->end_date ?? null;
                        
                        if ($isTaskCompleted) {
                            $tStatus = 'Completed';
                        } elseif ($tEndDate && now()->startOfDay() > \Carbon\Carbon::parse($tEndDate)->startOfDay()) {
                            $tStatus = 'Delayed';
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
                        if ($task->lot_no) {
                            $dateGroups[$key]['lots'][] = $task->lot_no;
                        }
                    }
                @endphp
                @php
                    $rowGroups = $dateGroups ?: [['lots' => [], 'start_date' => null, 'end_date' => null, 'status' => $statusText]];
                    $rows = count($rowGroups);
                    $first = true;
                @endphp
                <tbody class="assignment-item"
                       data-lot="{{ strtolower(implode(',', array_unique($lots))) }}"
                       data-from-stage="{{ strtolower($fromStageName) }}"
                       data-to-stage="{{ strtolower($toStageName) }}">
                    @foreach($rowGroups as $group)
                        <tr>
                            @if($first)
                                <td class="tk-slip" rowspan="{{ $rows }}">
                                    <a href="{{ route('unit.assignments.slip_details', ['slip_id' => $slipNo]) }}" class="tk-slip-link" title="Open slip details">
                                        #{{ $cleanSlipNo }} <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                            @endif
                            <td class="tk-lots">{{ empty($group['lots']) ? '-' : implode(', ', array_unique($group['lots'])) }}</td>
                            <td>
                                @if($group['status'] === 'Delayed')
                                    <span class="xl-pill xl-pill-late">Delayed</span>
                                @elseif($group['status'] === 'Completed')
                                    <span class="xl-pill xl-pill-done">Completed</span>
                                @else
                                    <span class="xl-pill xl-pill-open">Pending</span>
                                @endif
                            </td>
                            <td class="xl-nowrap {{ $group['status'] === 'Delayed' ? 'tk-late' : '' }}">{{ $group['end_date'] ? date('d M Y', strtotime($group['end_date'])) : '-' }}</td>
                            @if($first)
                                <td class="xl-num xl-strong" rowspan="{{ $rows }}">{{ number_format($totalPieces) }}</td>
                            @endif
                            <td class="xl-nowrap">{{ $group['start_date'] ? date('d M Y', strtotime($group['start_date'])) : '-' }}</td>
                            @if($first && !$isCutting)
                                <td class="tk-stage" rowspan="{{ $rows }}">{{ $fromStageName }}<i class="fas fa-arrow-right"></i>{{ $toStageName }}</td>
                                <td class="xl-nowrap" rowspan="{{ $rows }}">{{ $tasks->first()->getFromUnitMaster->name ?? '-' }}</td>
                            @endif
                        </tr>
                        @php $first = false; @endphp
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px 16px; color: var(--u-muted);">
                            <strong>No tasks found</strong><br>
                            @if(($view ?? 'open') === 'closed')
                                Your archived tasks list is empty.
                            @else
                                You have no pending assignments at the moment.
                            @endif
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('.task-action-form');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var action = form.getAttribute('data-action');
                var isClose = action === 'close';
                
                Swal.fire({
                    title: isClose ? 'Close Task?' : 'Re-open Task?',
                    text: isClose ? 'This task will be moved to archives.' : 'This task will be active again.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isClose ? '#dc2626' : '#16a34a',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: isClose ? 'Yes, proceed' : 'Yes, re-open'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const filterLot = document.getElementById('filter-lot');
        const filterFrom = document.getElementById('filter-from-stage');
        const filterTo = document.getElementById('filter-to-stage');
        const items = document.querySelectorAll('.assignment-item');

        // Populate dropdowns
        let fromStages = new Set();
        let toStages = new Set();

        items.forEach(item => {
            const f = item.getAttribute('data-from-stage');
            const t = item.getAttribute('data-to-stage');
            if (f) fromStages.add(f);
            if (t) toStages.add(t);
        });

        fromStages.forEach(stage => {
            let opt = document.createElement('option');
            opt.value = stage;
            opt.textContent = stage.charAt(0).toUpperCase() + stage.slice(1);
            filterFrom.appendChild(opt);
        });

        toStages.forEach(stage => {
            let opt = document.createElement('option');
            opt.value = stage;
            opt.textContent = stage.charAt(0).toUpperCase() + stage.slice(1);
            filterTo.appendChild(opt);
        });

        function filterAssignments() {
            const lotVal = filterLot.value.toLowerCase().trim();
            const fromVal = filterFrom.value.toLowerCase();
            const toVal = filterTo.value.toLowerCase();

            items.forEach(function(item) {
                const itemLot = item.getAttribute('data-lot');
                const itemFrom = item.getAttribute('data-from-stage');
                const itemTo = item.getAttribute('data-to-stage');

                const matchesLot = itemLot.includes(lotVal) || lotVal === '';
                const matchesFrom = fromVal === '' || itemFrom === fromVal;
                const matchesTo = toVal === '' || itemTo === toVal;

                if (matchesLot && matchesFrom && matchesTo) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            var counter = document.getElementById('tk-visible');
            if (counter) { counter.textContent = Array.prototype.filter.call(items, function (i) { return i.style.display !== 'none'; }).length; }
        }

        filterLot.addEventListener('input', filterAssignments);
        filterFrom.addEventListener('change', filterAssignments);
        filterTo.addEventListener('change', filterAssignments);
    });
</script>
@endpush
