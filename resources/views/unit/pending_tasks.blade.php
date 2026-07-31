@extends('layouts.unit')

@section('title', 'Pending Tasks')

@section('content')
<style>
    .pending-grid {
        display: grid;
        gap: 16px;
    }

    .pending-card {
        background: white;
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }

    .pending-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .pending-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed #e5e7eb;
        padding-bottom: 8px;
    }

    .lot-no {
        font-size: 16px;
        font-weight: 800;
        color: #1f2937;
        letter-spacing: 0.5px;
        line-height: 1.2;
    }
    
    .lot-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 1px;
    }

    .stats-row {
        display: flex;
        justify-content: space-between;
        padding-top: 2px;
    }

    .stat-box {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .stat-box.text-right {
        align-items: flex-end;
    }

    .stat-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 700;
    }

    .value-assigned {
        color: #4b5563;
    }

    .value-pending {
        color: #d97706;
        background: #fef3c7;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 13px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .empty-icon {
        width: 60px;
        height: 60px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        color: #9ca3af;
        font-size: 24px;
    }
    
    .empty-text {
        color: #4b5563;
        font-weight: 600;
        font-size: 16px;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
    }

    .filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        color: #1f2937;
        background: #f9fafb;
        transition: all 0.2s;
    }

    .filter-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
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
        background: var(--primary, #6366f1);
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

    .date-row {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }
    
    .date-box {
        display: flex;
        flex-direction: column;
    }
    
    .date-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .date-value {
        font-size: 11px;
        font-weight: 600;
        color: #4b5563;
    }

    .delayed-badge {
        background: #fee2e2;
        color: #b91c1c;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
<div class="content-wrapper">
    <div class="container-fluid py-4">
        
        <!-- Filter Form -->
        <form action="{{ route('unit.pending-tasks') }}" method="GET" class="filter-section">
            <details {{ request()->hasAny(['lot_no', 'start_date', 'end_date', 'is_delayed']) ? 'open' : '' }}>
                <summary class="filter-title">
                    <div class="filter-title-inner">
                        <i class="fas fa-filter"></i> Filter Tasks
                    </div>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </summary>
                <div class="filter-body">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="lot_no">Lot No</label>
                            <input type="text" id="lot_no" name="lot_no" class="filter-input" placeholder="e.g. 557"
                                value="{{ request('lot_no') }}">
                        </div>
                        <div class="filter-group">
                            <label for="start_date">Assigned From</label>
                            <input type="date" id="start_date" name="start_date" class="filter-input"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="filter-group">
                            <label for="end_date">Estimated By</label>
                            <input type="date" id="end_date" name="end_date" class="filter-input"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="filter-group">
                            <label for="is_delayed">Status</label>
                            <select id="is_delayed" name="is_delayed" class="filter-input">
                                <option value="">All Pending</option>
                                <option value="1" {{ request('is_delayed') == '1' ? 'selected' : '' }}>Only Delayed</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn btn-apply"><i class="fas fa-search"></i> Apply Filters</button>
                        <a href="{{ route('unit.pending-tasks') }}" class="filter-btn btn-clear">Clear All</a>
                    </div>
                </div>
            </details>
        </form>

        <div class="pending-grid">
            @forelse($grouped as $task)
                <div class="pending-card">
                    <div class="pending-header">
                        <div>
                            <div class="lot-label">Lot Number</div>
                            <div class="lot-no">{{ $task['lot_no'] }}</div>
                        </div>
                        @if($task['is_delayed'])
                            <div class="delayed-badge">
                                <i class="fas fa-exclamation-circle"></i> Delayed
                            </div>
                        @else
                            <i class="fas fa-tasks text-gray-300" style="font-size: 24px; color: #d1d5db;"></i>
                        @endif
                    </div>
                    
                    <div class="stats-row">
                        <div class="stat-box">
                            <span class="stat-label">Assigned</span>
                            <span class="stat-value value-assigned">{{ number_format($task['total_assigned']) }} pcs</span>
                        </div>
                        
                        <div class="stat-box text-right">
                            <span class="stat-label">Pending</span>
                            <span class="stat-value value-pending">{{ number_format($task['total_pending']) }} pcs</span>
                        </div>
                    </div>
                    
                    <div class="date-row">
                        <div class="date-box">
                            <span class="date-label">Assigned</span>
                            <span class="date-value">{{ $task['assigned_date'] ? \Carbon\Carbon::parse($task['assigned_date'])->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="date-box" style="text-align: right;">
                            <span class="date-label">Estimated</span>
                            <span class="date-value">{{ $task['estimated_date'] ? \Carbon\Carbon::parse($task['estimated_date'])->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="empty-text">No pending tasks found</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
