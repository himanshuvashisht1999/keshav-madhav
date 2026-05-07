<style>
    .assignment-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }

    .assignment-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .card-header-simple {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .type-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
    }

    .status-badge-simple {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 4px;
    }

    .badge-assigned { background: #fef3c7; color: #92400e; }
    .badge-closed { background: #d1fae5; color: #065f46; }
    .badge-incoming { background: #e0e7ff; color: #3730a3; }

    .card-body-simple {
        padding: 16px;
        flex-grow: 1;
    }

    .info-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .info-col {
        flex: 1;
        min-width: 50%;
        margin-bottom: 8px;
    }

    .info-label-simple {
        font-size: 11px;
        color: #94a3b8;
        display: block;
        margin-bottom: 2px;
    }

    .info-value-simple {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    .card-footer-simple {
        padding: 12px 16px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 8px;
    }

    .btn-simple {
        flex: 1;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        text-decoration: none !important;
        transition: all 0.2s;
    }

    .btn-simple-primary {
        background: #4f46e5;
        color: #ffffff;
    }

    .btn-simple-primary:hover {
        background: #4338ca;
        color: #ffffff;
    }

    .btn-simple-outline {
        border: 1px solid #d1d5db;
        color: #4b5563;
    }

    .btn-simple-outline:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    
    .btn-simple-danger {
        border: 1px solid #fca5a5;
        color: #b91c1c;
        background: #fef2f2;
    }
    
    .btn-simple-danger:hover {
        background: #fee2e2;
    }

    .btn-simple-success {
        border: 1px solid #6ee7b7;
        color: #047857;
        background: #ecfdf5;
    }

    .btn-simple-success:hover {
        background: #d1fae5;
    }

    .timing-badge {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 700;
        text-transform: uppercase;
        margin-left: 4px;
    }
    .badge-delay { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-on-time { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }

    .assignment-card-delayed {
        border-color: #fca5a5 !important;
        background-color: #fffbfa !important;
    }
</style>

@php
    $isDelayed = isset($item->timing) && !$item->timing->complete_date && now() > $item->timing->end_date;
@endphp

@if($type == 'cutting')
    <div class="assignment-card {{ $isDelayed ? 'assignment-card-delayed' : '' }}">
        <div class="card-header-simple">
            <span class="type-label">Cutting Master</span>
            <span class="status-badge-simple {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'badge-closed' : 'badge-assigned' }}">
                {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'Closed' : 'Assigned' }}
            </span>
        </div>
        <div class="card-body-simple">
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label-simple">CMPO NO</span>
                    <span class="info-value-simple">#{{ $item->id }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label-simple">Date</span>
                    <span class="info-value-simple">{{ $item->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="mb-2">
                <span class="info-label-simple">Company</span>
                <span class="info-value-simple">{{ $item->productSet->orderMain?->customer->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label-simple">Design</span>
                    <span class="info-value-simple">{{ $item->productSet->design_number ?? '-' }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label-simple">Color</span>
                    <span class="info-value-simple">{{ $item->productSet->colors->name ?? '-' }}</span>
                </div>
            </div>
            <div class="mb-2">
                <span class="info-label-simple">Fabric</span>
                <span class="info-value-simple">{{ $item->productSet->fabric_names ?? '-' }}</span>
            </div>
            <div>
                <span class="info-label-simple">Quantity</span>
                <span class="info-value-simple" style="color: #4f46e5;">{{ $item->quantity ?? 0 }} Pcs</span>
            </div>

            @php
                $startDate = $item->timing->start_date ?? $item->start_date ?? null;
                $endDate = $item->timing->end_date ?? $item->end_date ?? null;
                $completeDate = $item->timing->complete_date ?? $item->complete_date ?? null;
                $isDelayed = !$completeDate && $endDate && now() > $endDate;
            @endphp

            @if($startDate || $endDate)
                <hr class="my-2 border-f1f5f9">
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label-simple">Start</span>
                        <span class="info-value-simple text-xs">{{ $startDate ? date('d M H:i', strtotime($startDate)) : '-' }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label-simple">Expected End @if($isDelayed) <span class="timing-badge badge-delay">Delayed</span> @endif</span>
                        <span class="info-value-simple text-xs {{ $isDelayed ? 'text-danger' : '' }}">
                            {{ $endDate ? date('d M H:i', strtotime($endDate)) : '-' }}
                        </span>
                    </div>
                </div>
                @if($completeDate)
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label-simple">Completed</span>
                        <span class="info-value-simple text-xs text-success">{{ date('d M H:i', strtotime($completeDate)) }}</span>
                    </div>
                </div>
                @endif
            @endif
        </div>
        <div class="card-footer-simple">
            @if(!empty($canCloseTasks) && $canCloseTasks)
                <form method="POST" class="task-action-form" style="flex: 1;"
                    data-action="{{ ($view ?? 'open') === 'closed' ? 'reopen' : 'close' }}"
                    action="{{ ($view ?? 'open') === 'closed'
                        ? route('unit.assignments.reopen', ['type' => 'cutting', 'id' => $item->id])
                        : route('unit.assignments.close', ['type' => 'cutting', 'id' => $item->id]) }}">
                    @csrf
                    <button type="submit" class="btn-simple {{ ($view ?? 'open') === 'closed' ? 'btn-simple-success' : 'btn-simple-danger' }}">
                        {{ ($view ?? 'open') === 'closed' ? 'Re-open' : 'Close' }}
                    </button>
                </form>
            @endif

            <a href="{{ route('unit.assignments.details', ['type' => 'cutting', 'id' => $item->id]) }}"
                class="btn-simple btn-simple-primary">
                View Details
            </a>
        </div>
    </div>
@else
    <div class="assignment-card {{ $isDelayed ? 'assignment-card-delayed' : '' }}">
        <div class="card-header-simple">
            <span class="type-label">{{ ucfirst($item->transaction_type ?? 'Task') }}</span>
            <span class="status-badge-simple {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'badge-closed' : 'badge-incoming' }}">
                {{ (!empty($canCloseTasks) && $canCloseTasks && ($view ?? 'open') === 'closed') ? 'Closed' : 'Incoming' }}
            </span>
        </div>
        <div class="card-body-simple">
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label-simple">REF NO</span>
                    <span class="info-value-simple">#{{ $item->orderProduct?->orderProductSet?->order_cutting_stage?->id ?? '-' }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label-simple">Date</span>
                    <span class="info-value-simple">{{ $item->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="mb-2">
                <span class="info-label-simple">From</span>
                <span class="info-value-simple">{{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <div class="info-col">
                    <span class="info-label-simple">Lot No</span>
                    <span class="info-value-simple">{{ $item->lot_no ?? 'Pending' }}</span>
                </div>
                <div class="info-col">
                    <span class="info-label-simple">Quantity</span>
                    <span class="info-value-simple" style="color: #4f46e5;">{{ $item->remaining_quantity ?? '-' }} Pcs</span>
                </div>
            </div>
            <div>
                <span class="info-label-simple">Sent By</span>
                <span class="info-value-simple">{{ $item->getFromUnitMaster->name ?? $item->getUnitMaster->name ?? '-' }}</span>
            </div>

            @php
                $startDate = $item->timing->start_date ?? $item->start_date ?? null;
                $endDate = $item->timing->end_date ?? $item->end_date ?? null;
                $completeDate = $item->timing->complete_date ?? $item->complete_date ?? null;
                $isDelayed = !$completeDate && $endDate && now() > $endDate;
            @endphp

            @if($startDate || $endDate)
                <hr class="my-2 border-f1f5f9">
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label-simple">Start</span>
                        <span class="info-value-simple text-xs">{{ $startDate ? date('d M H:i', strtotime($startDate)) : '-' }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label-simple">Expected End @if($isDelayed) <span class="timing-badge badge-delay">Delayed</span> @endif</span>
                        <span class="info-value-simple text-xs {{ $isDelayed ? 'text-danger' : '' }}">
                            {{ $endDate ? date('d M H:i', strtotime($endDate)) : '-' }}
                        </span>
                    </div>
                </div>
                @if($completeDate)
                <div class="info-row">
                    <div class="info-col">
                        <span class="info-label-simple">Completed</span>
                        <span class="info-value-simple text-xs text-success">{{ date('d M H:i', strtotime($completeDate)) }}</span>
                    </div>
                </div>
                @endif
            @endif
        </div>
        <div class="card-footer-simple">
            @if(!empty($canCloseTasks) && $canCloseTasks)
                <form method="POST" class="task-action-form" style="flex: 1;"
                    data-action="{{ ($view ?? 'open') === 'closed' ? 'reopen' : 'close' }}"
                    action="{{ ($view ?? 'open') === 'closed'
                        ? route('unit.assignments.reopen', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id])
                        : route('unit.assignments.close', ['type' => $item->transaction_type ?? 'production', 'id' => $item->id]) }}">
                    @csrf
                    <button type="submit" class="btn-simple {{ ($view ?? 'open') === 'closed' ? 'btn-simple-success' : 'btn-simple-danger' }}">
                        {{ ($view ?? 'open') === 'closed' ? 'Re-open' : 'Close' }}
                    </button>
                </form>
            @endif

            @if(isset($item->transaction_type))
                <a href="{{ route('unit.assignments.details', ['type' => $item->transaction_type, 'id' => $item->id]) }}"
                    class="btn-simple btn-simple-primary">
                    Process
                </a>
            @elseif($item->production_slip_digitization_id ?? false)
                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->production_slip_digitization_id]) }}"
                    class="btn-simple btn-simple-primary">
                    View Slip
                </a>
            @elseif(isset($item->slip_file))
                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->id]) }}"
                    class="btn-simple btn-simple-primary">
                    View Slip
                </a>
            @endif
        </div>
    </div>
@endif
