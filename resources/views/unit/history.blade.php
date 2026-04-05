@extends('layouts.unit')

@section('title', 'History')
@section('header_icon')
    <i class="fas fa-history"></i>
@endsection

@push('styles')
<style>
    .unit-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 10px;
        display: inline-block;
        color: white;
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

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 120px;
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

    /* Content */
    .slip-grid {
        display: grid;
        gap: 16px;
    }

    .slip-card {
        background: white;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .slip-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--bg-gradient);
    }

    .slip-card:active {
        transform: scale(0.98);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .slip-thumbnail {
        width: 90px;
        height: 90px;
        border-radius: 12px;
        object-fit: cover;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .slip-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .slip-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .slip-date {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .slip-badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .badge-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .badge-approved {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .badge-type {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .badge-received {
        background: #4f46e5;
        color: white;
    }

    .badge-sent {
        background: #059669;
        color: white;
    }
    
    .task-card-received {
        background: #f0f4ff;
        border-left: 6px solid #4f46e5;
    }

    .task-card-sent {
        background: #f0fff4;
        border-left: 6px solid #059669;
    }

    .event-icon-received { color: #6366f1; }
    .event-icon-sent { color: #10b981; }

    .slip-meta {
        font-size: 13px;
        color: #9ca3af;
        display: flex;
        gap: 12px;
    }

    .slip-meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .chevron {
        color: #d1d5db;
        font-size: 18px;
    }

    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-text {
        font-size: 18px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .empty-subtext {
        font-size: 14px;
        color: #9ca3af;
    }

    /* View Type Switcher */
    .view-type-switcher {
        display: flex;
        background: white;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
    }

    .view-type-option {
        flex: 1;
        position: relative;
    }

    .view-type-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .view-type-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        transition: all 0.2s;
        cursor: pointer;
    }

    .view-type-option input:checked + .view-type-label {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    /* Task Card Styles */
    .task-card {
        background: white;
        border-radius: 20px;
        padding: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #f3f4f6;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        display: block;
        margin-bottom: 16px;
    }

    .task-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 60px;
        height: 60px;
        background: radial-gradient(circle at top right, rgba(102, 126, 234, 0.1), transparent);
    }

    .task-status-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .task-id {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 12px;
        color: #9ca3af;
        background: #f9fafb;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .task-main-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    .task-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px dashed #f3f4f6;
    }

    .task-date {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>
@endpush

@section('content')
    <div class="unit-badge" style="margin-top: -15px; margin-bottom: 15px;">
        {{ session('unit_auth')['name'] ?? 'Unit Master' }}
    </div>

    <div class="view-type-switcher">
        <label class="view-type-option">
            <input type="radio" name="view_mode" value="slips" {{ $viewType === 'slips' ? 'checked' : '' }} onchange="window.location.href='{{ route('unit.history', ['view_type' => 'slips']) }}'">
            <div class="view-type-label">
                <i class="fas fa-file-invoice"></i> Digitized Slips
            </div>
        </label>
        <label class="view-type-option">
            <input type="radio" name="view_mode" value="tasks" {{ $viewType === 'tasks' ? 'checked' : '' }} onchange="window.location.href='{{ route('unit.history', ['view_type' => 'tasks']) }}'">
            <div class="view-type-label">
                <i class="fas fa-tasks"></i> Assigned Tasks
            </div>
        </label>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('unit.history') }}" method="GET" class="filter-section">
        <input type="hidden" name="view_type" value="{{ $viewType }}">
        <details {{ request('lot_no') || request('order_no') || request('status') ? 'open' : '' }}>
            <summary class="filter-title">
                <div class="filter-title-inner">
                    <i class="fas fa-filter"></i> Filter History
                </div>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </summary>
            <div class="filter-body">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="lot_no">Lot No</label>
                        <input type="text" id="lot_no" name="lot_no" class="filter-input" placeholder="e.g. L-123"
                            value="{{ request('lot_no') }}">
                    </div>
                    <div class="filter-group">
                        <label for="order_no">Order No</label>
                        <input type="text" id="order_no" name="order_no" class="filter-input" placeholder="e.g. ORD-123"
                            value="{{ request('order_no') }}">
                    </div>
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="filter-input">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    @if(($viewType ?? '') === 'tasks')
                    <div class="filter-group">
                        <label for="activity_type">Activity Type</label>
                        <select id="activity_type" name="activity_type" class="filter-input">
                            <option value="">All Activities</option>
                            <option value="received" {{ request('activity_type') === 'received' ? 'selected' : '' }}>Only Received</option>
                            <option value="sent" {{ request('activity_type') === 'sent' ? 'selected' : '' }}>Only Sent</option>
                        </select>
                    </div>
                    @endif
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-btn btn-apply"><i class="fas fa-search"></i> Apply Filters</button>
                    <a href="{{ route('unit.history') }}" class="filter-btn btn-clear">Clear All</a>
                </div>
            </div>
        </details>
    </form>

    @if($viewType === 'slips')
        <div class="slip-grid">
            @forelse($slips as $slip)
                <a href="{{ route('unit.view.slip', ['type' => 'production', 'id' => $slip['id']]) }}" class="slip-card">
                    <img src="/assets/production_slips/{{ $slip['slip_file'] }}" alt="Slip" class="slip-thumbnail"
                        onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2214%22%3E📷%3C/text%3E%3C/svg%3E'">
                    <div class="slip-content">
                        <div class="slip-header">
                            <div>
                                <div class="slip-date">
                                    <i class="far fa-clock"></i> {{ $slip['created_at']->format('d M Y, h:i A') }}
                                </div>
                                <div class="slip-badges">
                                    <span class="badge badge-type">
                                        {{ $slip['type'] === 'fabric' ? '🧵 Fabric' : '📦 Production' }}
                                    </span>
                                    <span class="badge {{ $slip['status'] == 0 ? 'badge-pending' : 'badge-approved' }}">
                                        {{ $slip['status'] == 0 ? '⏳ Pending' : '✅ Done' }}
                                    </span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right chevron"></i>
                        </div>
                        <div class="slip-meta" style="flex-wrap: wrap; row-gap: 4px;">
                            @if($slip['type'] === 'fabric')
                                <span><i class="fas fa-tag" style="color:#6366f1;"></i> {{ $slip['lot_no'] }}</span>
                                <span><i class="fas fa-shopping-cart" style="color:#10b981;"></i> {{ $slip['order_no'] }}</span>
                            @else
                                <span style="max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <i class="fas fa-tag" style="color:#6366f1;"></i> {{ $slip['lot_no'] }}
                                </span>
                                @if($slip['order_no'] !== '-')
                                    <span style="max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <i class="fas fa-shopping-cart" style="color:#10b981;"></i> {{ $slip['order_no'] }}
                                    </span>
                                @endif
                                <span><i class="fas fa-layer-group" style="color:#3b82f6;"></i> {{ $slip['stage'] }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-text">No Slips Yet</div>
                    <div class="empty-subtext">Your uploaded slips will appear here</div>
                </div>
            @endforelse
        </div>
    @else
        <div class="task-grid">
            @forelse($tasks as $task)
                @php 
                    $isReceived = $task['event_type'] === 'received';
                    $cardClass = $isReceived ? 'task-card-received' : 'task-card-sent';
                    $badgeClass = $isReceived ? 'badge-received' : 'badge-sent';
                    
                    // IF RECEIVED: Go to assignment details (to upload slip)
                    // IF SENT: Go to the slip view (because it's already done)
                    $detailUrl = $isReceived 
                        ? route('unit.assignments.details', ['type' => $task['type'], 'id' => $task['id']])
                        : route('unit.view.slip', ['type' => ($task['type'] === 'fabric' ? 'fabric' : 'production'), 'id' => $task['id']]);
                @endphp
                <a href="{{ $detailUrl }}" class="task-card {{ $cardClass }}">
                    
                    <div class="task-status-line">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge {{ $badgeClass }}">
                                <i class="fas {{ $isReceived ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i> 
                                {{ $isReceived ? 'Work Received' : 'Work Sent Out' }}
                            </span>
                            <span class="task-id">#{{ $task['id'] }}</span>
                        </div>
                    </div>

                    <div class="task-main-info">
                        <div class="info-item">
                            <span class="info-label">Lot Number</span>
                            <span class="info-value">{{ $task['lot_no'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Quantity</span>
                            <span class="info-value" style="color: {{ $isReceived ? '#4f46e5' : '#059669' }}; font-size: 18px;">
                                {{ $task['quantity'] }} Pcs
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ $isReceived ? 'From Stage' : 'To Next Stage' }}</span>
                            <span class="info-value">{{ $task['from_stage'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order Number</span>
                            <span class="info-value">{{ $task['order_no'] }}</span>
                        </div>
                    </div>

                    <div class="task-footer">
                        <div class="task-date">
                            <i class="far fa-calendar-alt"></i> 
                            <strong>{{ $isReceived ? 'Received On:' : 'Sent On:' }}</strong> 
                            {{ $task['created_at']->format('d M Y, h:i A') }}
                        </div>
                        <i class="fas fa-chevron-right chevron"></i>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <div class="empty-text">No Tasks Found</div>
                    <div class="empty-subtext">Assigned tasks will appear here</div>
                </div>
            @endforelse
        </div>
    @endif
@endsection