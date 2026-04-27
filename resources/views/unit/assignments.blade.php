@extends('layouts.unit')

@section('title', 'Assignments')
@section('header_icon')
    <i class="fas fa-clipboard-list"></i>
@endsection

@push('styles')
    <style>
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
        }

        .filter-title {
            font-size: 14px;
            font-weight: 700;
            color: #4b5563;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            list-style: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .filter-title::-webkit-details-marker {
            display: none;
        }

        .filter-title-inner {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-body {
            margin-top: 12px;
            padding-top: 16px;
            border-top: 1px dashed #e5e7eb;
        }

        details[open] .toggle-icon {
            transform: rotate(180deg);
        }

        .toggle-icon {
            transition: transform 0.3s ease;
            color: #9ca3af;
            font-size: 12px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
        }

        .filter-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #f9fafb;
        }

        .filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .filter-btn {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-apply {
            background: var(--primary);
            color: white;
        }

        .btn-clear {
            background: #f3f4f6;
            color: #4b5563;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .tabs {
            display: flex;
            background: #e5e7eb;
            padding: 4px;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        .tab-item {
            flex: 1;
            text-align: center;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            text-decoration: none;
            color: #4b5563;
            transition: all 0.2s;
        }

        .tab-item.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        .assignment-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 12px;
        }

        .date-badge {
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-completed {
            background: #d1fae5;
            color: #059669;
        }

        .assignment-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .assignment-footer {
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-view,
        .btn-secondary {
            border: none;
            outline: none;
            cursor: pointer;
            background: var(--bg-gradient);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .assignment-footer .btn-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
    </style>
@endpush

@section('content')
    @if(!empty($canCloseTasks) && $canCloseTasks)
        <div class="tabs">
            <a href="{{ route('unit.assignments', ['view' => 'open', 'lot_no' => request('lot_no'), 'order_no' => request('order_no')]) }}"
                class="tab-item {{ ($view ?? 'open') === 'open' ? 'active' : '' }}">
                Open Tasks
            </a>
            <a href="{{ route('unit.assignments', ['view' => 'closed', 'lot_no' => request('lot_no'), 'order_no' => request('order_no')]) }}"
                class="tab-item {{ ($view ?? 'open') === 'closed' ? 'active' : '' }}">
                Closed Tasks
            </a>
        </div>
    @endif

    <!-- Filter Form -->
    <form action="{{ route('unit.assignments') }}" method="GET" class="filter-section">
        <input type="hidden" name="view" value="{{ $view ?? 'open' }}">
        <details {{ request('lot_no') || request('order_no') ? 'open' : '' }}>
            <summary class="filter-title">
                <div class="filter-title-inner">
                    <i class="fas fa-filter"></i> Filter Assignments
                </div>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="filter-body">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="lot_no">Lot No / Design No</label>
                        <input type="text" id="lot_no" name="lot_no" class="filter-input" placeholder="e.g. L-123"
                            value="{{ request('lot_no') }}">
                    </div>
                    <div class="filter-group">
                        <label for="order_no">Order No</label>
                        <input type="text" id="order_no" name="order_no" class="filter-input" placeholder="e.g. ORD-123"
                            value="{{ request('order_no') }}">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-btn btn-apply"><i class="fas fa-search"></i> Apply
                        Filters</button>
                    <a href="{{ route('unit.assignments', ['view' => $view ?? 'open']) }}"
                        class="filter-btn btn-clear">Clear</a>
                </div>
            </div>
        </details>
    </form>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">📁</div>
            <h3>
                @if(!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed')
                    No Closed Assignments Found
                @else
                    No Open Assignments Found
                @endif
            </h3>
            <p>
                @if(!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed')
                    You don't have any closed assignments at the moment.
                @else
                    You don't have any pending assignments at the moment.
                @endif
            </p>
        </div>
    @else
        @if($type == 'cutting')
            <!-- CUTTING MASTER ASSIGNMENTS -->
            @foreach($assignments as $item)
                <div class="assignment-card">
                    <div class="assignment-header">
                        <span class="date-badge">{{ $item->created_at->format('d M Y') }}</span>
                        <span
                            class="status-badge {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'status-completed' : 'status-pending' }}">
                            {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'Closed' : 'Assigned' }}
                        </span>
                    </div>
                    <div class="assignment-body">
                        <div class="info-item">
                            <span class="info-label">CMPO No</span>
                            <span class="info-value">CMPO-{{ $item->id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Company</span>
                            <span class="info-value">{{ $item->productSet->orderMain?->customer->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Design No</span>
                            <span class="info-value">{{ $item->productSet->design_number ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fabric</span>
                            <span class="info-value">{{ $item->productSet->fabric->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Color</span>
                            <span class="info-value">{{ $item->productSet->colors->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Assigned Qty</span>
                            <span class="info-value">{{ $item->quantity ?? 0 }} Pcs</span>
                        </div>
                    </div>
                    <div class="assignment-footer">
                        <div class="btn-group">
                            @if(!empty($canCloseTasks) && $canCloseTasks)
                                <form method="POST" class="task-action-form"
                                    data-action="{{ ($view ?? 'open') === 'closed' ? 'reopen' : 'close' }}"
                                    action="{{ ($view ?? 'open') === 'closed'
                                        ? route('unit.assignments.reopen', ['type' => 'cutting', 'id' => $item->id])
                                        : route('unit.assignments.close', ['type' => 'cutting', 'id' => $item->id]) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary">
                                        <i class="fas {{ ($view ?? 'open') === 'closed' ? 'fa-undo' : 'fa-times' }}"></i>
                                        {{ ($view ?? 'open') === 'closed' ? 'Re-open' : 'Close' }}
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('unit.assignments.details', ['type' => 'cutting', 'id' => $item->id]) }}"
                                class="btn-view">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- OTHER STAGES ASSIGNMENTS (Incoming Slips) -->
            @foreach($assignments as $item)
                <div class="assignment-card">
                    <div class="assignment-header">
                        <span class="date-badge">{{ $item->created_at->format('d M Y') }}</span>
                        <span
                            class="status-badge {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'status-completed' : 'status-pending' }}">
                            {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'Closed' : 'Incoming Slip' }}
                        </span>
                    </div>
                    <div class="assignment-body">
                        <div class="info-item">
                            <span class="info-label">CMPO No</span>
                            <span
                                class="info-value">CMPO-{{ $item->orderProduct?->orderProductSet?->order_cutting_stage?->id ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">From Stage</span>
                            <span class="info-value">{{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Lot No</span>
                            <span class="info-value">{{ $item->lot_no ?? 'Pending' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Sent By</span>
                            <span class="info-value">{{ $item->getFromUnitMaster->name ?? $item->getUnitMaster->name ?? '-' }}</span>
                        </div>
                        <!-- For Transactions, show quantity if available (remaining_quantity) -->
                        <div class="info-item">
                            <span class="info-label">Quantity</span>
                            <span class="info-value">{{ $item->remaining_quantity ?? '-' }} Pcs</span>
                        </div>
                    </div>
                    <div class="assignment-footer">
                        <div class="btn-group">
                            @if(!empty($canCloseTasks) && $canCloseTasks)
                                <form method="POST" class="task-action-form"
                                    data-action="{{ ($view ?? 'open') === 'closed' ? 'reopen' : 'close' }}"
                                    action="{{ ($view ?? 'open') === 'closed'
                                        ? route('unit.assignments.reopen', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id])
                                        : route('unit.assignments.close', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id]) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary">
                                        <i class="fas {{ ($view ?? 'open') === 'closed' ? 'fa-undo' : 'fa-times' }}"></i>
                                        {{ ($view ?? 'open') === 'closed' ? 'Re-open' : 'Close' }}
                                    </button>
                                </form>
                            @endif

                            @if(isset($item->transaction_type))
                                <!-- Transaction Details Link -->
                                <a href="{{ route('unit.assignments.details', ['type' => $item->transaction_type, 'id' => $item->id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-clipboard-check"></i> Process
                                </a>
                            @elseif($item->production_slip_digitization_id ?? false)
                                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->production_slip_digitization_id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-eye"></i> View Slip
                                </a>
                            @elseif(isset($item->slip_file))
                                <!-- Legacy/Direct Slip Support -->
                                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-eye"></i> View Slip
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Task Action Forms (Close/Reopen)
            var forms = document.querySelectorAll('.task-action-form');
            forms.forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var action = form.getAttribute('data-action');
                    var isClose = action === 'close';
                    Swal.fire({
                        title: isClose ? 'Close this task?' : 'Re-open this task?',
                        text: isClose ? 'Once closed, this task will move to the Closed tab.' : 'This task will move back to Open tasks.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: isClose ? 'Yes, close it' : 'Yes, re-open it',
                        cancelButtonText: 'Cancel'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush