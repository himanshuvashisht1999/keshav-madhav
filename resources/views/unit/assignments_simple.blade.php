@extends('layouts.unit')

@section('title', isset($isHistory) && $isHistory ? 'History' : 'Assignments')

@push('styles')
    <style>
        body {
            background: #f1f5f9;
            color: #1e293b;
        }

        .dashboard-header-simple {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 0;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .dashboard-title-simple {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .dashboard-subtitle-simple {
            font-size: 14px;
            color: #64748b;
        }

        .tabs-simple {
            display: flex;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .tab-item-simple {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none !important;
            color: #64748b;
            transition: all 0.2s;
        }

        .tab-item-simple.active {
            background: #ffffff;
            color: #4f46e5;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .task-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            margin-bottom: 16px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .task-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .task-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef9c3;
            color: #a16207;
            border: 1px solid #fde047;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        
        .status-delayed {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }

        .total-pieces {
            color: #4f46e5;
            font-size: 16px;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        
        .btn-view-slip {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            flex: 1;
            transition: all 0.2s;
        }
        
        .btn-view-slip:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .btn-close-task {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            flex: 1;
            border: none;
            transition: all 0.2s;
        }

        .btn-close-task:hover {
            background: #fecaca;
        }
    </style>
@endpush

@section('content')


    <div class="container pb-5">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Filters Section --}}
        <div style="background: #ffffff; border-radius: 12px; padding: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; gap: 16px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">Search</label>
                <input type="text" id="filter-lot" class="form-control" placeholder="Search Lot No..." style="width: 100%; border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background: #f8fafc;">
            </div>

            @if(isset($isHistory) && $isHistory)
                @php
                    $activity = request('activity', 'received');
                @endphp
                <div style="flex: 1; min-width: 150px;">
                    <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">Type</label>
                    <select class="form-control" onchange="window.location.href=this.value" style="width: 100%; border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background: #f8fafc; appearance: auto;">
                        <option value="{{ route('unit.history', ['activity' => 'received']) }}" {{ $activity === 'received' ? 'selected' : '' }}>Received Tasks</option>
                        <option value="{{ route('unit.history', ['activity' => 'sent']) }}" {{ $activity === 'sent' ? 'selected' : '' }}>Sent Tasks</option>
                    </select>
                </div>
            @endif

            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">From Stage</label>
                <select id="filter-from-stage" class="form-control" style="width: 100%; border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background: #f8fafc; appearance: auto;">
                    <option value="">All From Stages</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">To Stage</label>
                <select id="filter-to-stage" class="form-control" style="width: 100%; border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 14px; background: #f8fafc; appearance: auto;">
                    <option value="">All To Stages</option>
                </select>
            </div>

        </div>

        <div class="row" id="assignments-container">
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
                <div class="col-lg-4 col-md-6 mb-4 assignment-item" 
                     data-lot="{{ strtolower(implode(',', array_unique($lots))) }}"
                     data-from-stage="{{ strtolower($fromStageName) }}"
                     data-to-stage="{{ strtolower($toStageName) }}">
                    <div class="card-simple" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <!-- Row 1: Slip No and Total Pieces -->
                        <div class="task-header" style="align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 12px;">
                            <div class="task-title" style="font-size: 18px; margin: 0;">Slip #{{ $cleanSlipNo }}</div>
                            <div style="text-align: right;">
                                <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px;">Total Pieces</div>
                                <div style="font-size: 18px; font-weight: 800; color: #4f46e5; line-height: 1;">{{ $totalPieces }}</div>
                            </div>
                        </div>

                        <!-- Row 2: From Stage and Sent By -->
                        @if(!$isCutting)
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <div style="flex: 1;">
                                    <div class="info-label" style="font-size: 11px; color: #64748b; margin-bottom: 4px;">From Stage</div>
                                    <div class="info-value" style="font-size: 13px; font-weight: 500; color: #0f172a;">
                                        {{ $fromStageName }}
                                    </div>
                                </div>
                                <div style="flex: 1;">
                                    <div class="info-label" style="font-size: 11px; color: #64748b; margin-bottom: 4px;">To Stage</div>
                                    <div class="info-value" style="font-size: 13px; font-weight: 500; color: #0f172a;">
                                        {{ $toStageName }}
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div class="info-label" style="font-size: 11px; color: #64748b; margin-bottom: 4px;"><i class="fas fa-user mr-1"></i> Sent By</div>
                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $tasks->first()->getFromUnitMaster->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Row 3: Lot No, Assigned, Est Estimate, Status (Looped) -->
                        <div style="background: #f8fafc; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                            <!-- Header -->
                            <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr; gap: 8px; padding: 8px 12px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Lots</div>
                                <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Assigned</div>
                                <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Est. Complete</div>
                                <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</div>
                            </div>
                            <!-- Rows -->
                            @foreach($dateGroups as $group)
                                <div style="display: grid; grid-template-columns: 1.5fr 1.5fr 1.5fr 1fr; gap: 8px; padding: 10px 12px; border-bottom: 1px dashed #e2e8f0;">
                                    <div style="font-size: 12px; font-weight: 700; color: #4f46e5; word-break: break-all; align-self: center;">
                                        {{ empty($group['lots']) ? '-' : implode(', ', array_unique($group['lots'])) }}
                                    </div>
                                    <div style="font-size: 12px; font-weight: 600; color: #1e293b; align-self: center;">
                                        {{ $group['start_date'] ? date('d M, Y', strtotime($group['start_date'])) : '-' }}
                                    </div>
                                    <div style="font-size: 12px; font-weight: 600; {{ $group['status'] === 'Delayed' ? 'color: #dc2626;' : 'color: #1e293b;' }} align-self: center;">
                                        {{ $group['end_date'] ? date('d M, Y', strtotime($group['end_date'])) : '-' }}
                                    </div>
                                    <div style="align-self: center;">
                                        @if($group['status'] === 'Delayed')
                                            <span style="background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Delayed</span>
                                        @elseif($group['status'] === 'Completed')
                                            <span style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Completed</span>
                                        @else
                                            <span style="background: #fef9c3; color: #a16207; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase;">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Row 4: View Slip Details -->
                        <div class="task-actions mt-3">
                            <a href="{{ route('unit.assignments.slip_details', ['slip_id' => $slipNo]) }}" class="btn-view-slip w-100" style="display: block; background: #4f46e5; color: white; border: none; padding: 12px; font-size: 15px;">
                                <i class="fas fa-eye mr-2"></i> View Slip Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-5 text-center">
                        <h3 class="h5 font-weight-bold">No Tasks Found</h3>
                        <p class="text-muted mb-0">
                            @if(($view ?? 'open') === 'closed')
                                Your archived tasks list is empty.
                            @else
                                You have no pending assignments at the moment.
                            @endif
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
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
        }

        filterLot.addEventListener('input', filterAssignments);
        filterFrom.addEventListener('change', filterAssignments);
        filterTo.addEventListener('change', filterAssignments);
    });
</script>
@endpush
