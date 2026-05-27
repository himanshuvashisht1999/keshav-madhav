@extends('owner.layouts.app')

@section('title', 'Unit Assignment Report')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 60px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -30px;
        position: relative;
        z-index: 1;
    }

    .app-header h1 {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
        margin-bottom: 5px;
    }

    .breadcrumb-custom {
        display: flex;
        gap: 8px;
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 20px;
        align-items: center;
    }

    .breadcrumb-custom a {
        color: white;
        text-decoration: none;
    }

    .search-container {
        position: relative;
        z-index: 10;
        padding: 0 20px;
        margin-bottom: 20px;
    }

    .search-box {
        background: white;
        border-radius: 20px;
        padding: 15px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .filter-input {
        background: #f1f5f9;
        border: none;
        border-radius: 12px;
        padding: 10px 15px;
        font-size: 13px;
        width: 100%;
        margin-bottom: 10px;
        font-weight: 600;
        color: #475569;
    }

    .assignment-card {
        background: var(--glass-bg);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--glass-border);
    }

    .assign-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .unit-name {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .design-no {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .status-badge {
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .grid-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 15px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .detail-val {
        font-size: 13px;
        color: #1e293b;
        font-weight: 800;
    }

    .qty-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .qty-item {
        text-align: center;
        flex: 1;
    }

    .qty-val {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
    }

    .qty-label {
        font-size: 10px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.dashboard') }}">Home</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <span>Assignments</span>
        </div>
        <h1>Unit Assignments</h1>
        <p class="mb-0 opacity-75">Track tasks & production</p>
    </div>

    <div class="search-container">
        <form action="{{ route('owner.reports.unit-assignments') }}" method="GET" id="filterForm">
            <div class="search-box">
                <div style="font-size: 12px; font-weight: 700; margin-bottom: 8px; color: #475569;">Filters</div>
                
                <div class="row" style="margin: 0 -5px;">
                    <div class="col-6" style="padding: 0 5px;">
                        <select name="stage_id" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Stages</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6" style="padding: 0 5px;">
                        <select name="view" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                            <option value="open" {{ request('view', 'open') == 'open' ? 'selected' : '' }}>Pending Tasks</option>
                            <option value="closed" {{ request('view', 'open') == 'closed' ? 'selected' : '' }}>Done Tasks</option>
                            <option value="delayed" {{ request('view', 'open') == 'delayed' ? 'selected' : '' }}>Delayed Tasks</option>
                        </select>
                    </div>
                </div>

                <div class="row" style="margin: 0 -5px;">
                    <div class="col-6" style="padding: 0 5px;">
                        <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="filter-input" placeholder="Lot No" onchange="document.getElementById('filterForm').submit()">
                    </div>
                    <div class="col-6" style="padding: 0 5px;">
                        <select name="production_status" class="filter-input" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Status Filter</option>
                            <option value="not_printing" {{ request('production_status') == 'not_printing' ? 'selected' : '' }}>No Print</option>
                            <option value="not_stitching" {{ request('production_status') == 'not_stitching' ? 'selected' : '' }}>No Stitch</option>
                        </select>
                    </div>
                </div>
                
                <div class="text-right mt-1">
                    <a href="{{ route('owner.reports.unit-assignments') }}" class="btn btn-sm btn-light" style="border-radius: 8px; font-weight: 700;">Clear</a>
                    <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 8px; font-weight: 700;">Apply</button>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid pb-5">
        @php
            $totalAssigned = 0;
            $totalPending = 0;
        @endphp

        @forelse($assignments as $item)
            @php 
                $totalAssigned += ($item->assigned_qty ?? 0);
                $totalPending += ($item->pending_qty ?? 0);
                
                // Determine names based on type
                $unitName = $type == 'cutting' ? ($item->stage_master_unit->name ?? '-') : ($item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-');
                $phone = $type == 'cutting' ? ($item->stage_master_unit->phone ?? null) : ($item->getToUnitMaster->phone ?? $item->stage_master_unit->phone ?? null);
            @endphp
            
            <div class="assignment-card">
                <div class="assign-header">
                    <div>
                        <div class="unit-name">
                            <i class="fas fa-user-circle text-primary"></i>
                            {{ $unitName }}
                            @if($phone)
                                <a href="https://wa.me/{{ $phone }}" class="text-success"><i class="fab fa-whatsapp"></i></a>
                            @endif
                        </div>
                        <div class="design-no">Design: {{ $item->design_number ?? '-' }}</div>
                    </div>
                    <span class="status-badge badge-{{ $item->status_class ?? 'secondary' }} bg-{{ $item->status_class ?? 'secondary' }} text-white">
                        {{ $item->status_text ?? 'Unknown' }}
                    </span>
                </div>

                <div class="grid-details">
                    @if($type == 'cutting')
                        <div class="detail-item">
                            <span class="detail-label">Order No</span>
                            <span class="detail-val">{{ $item->orderMain->sku ?? '-' }}</span>
                        </div>
                    @else
                        <div class="detail-item">
                            <span class="detail-label">Lot No</span>
                            <span class="detail-val">{{ $item->lot_no ?? 'Pending' }}</span>
                        </div>
                        @if(!$productionStatus)
                            <div class="detail-item">
                                <span class="detail-label">From Stage</span>
                                <span class="detail-val">{{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}</span>
                            </div>
                        @endif
                    @endif

                    @if($productionStatus)
                        <div class="detail-item">
                            <span class="detail-label">Lot Date</span>
                            <span class="detail-val">{{ $item->production_date ?? '-' }}</span>
                        </div>
                    @else
                        <div class="detail-item">
                            <span class="detail-label">Start Date</span>
                            <span class="detail-val">{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Est. Completion</span>
                            <span class="detail-val">{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</span>
                        </div>
                    @endif
                </div>

                @if(!empty($item->lot_no))
                    <a href="{{ route('owner.lot-details', $item->lot_no) }}" class="btn btn-sm btn-outline-info w-100 mb-2" style="border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-eye"></i> View Lot Details
                    </a>
                @endif

                <div class="qty-box">
                    @if($productionStatus)
                        <div class="qty-item">
                            <div class="qty-val text-primary">{{ $item->assigned_qty ?? 0 }}</div>
                            <div class="qty-label">Total Qty</div>
                        </div>
                    @else
                        <div class="qty-item" style="border-right: 1px solid #e2e8f0;">
                            <div class="qty-val text-primary">{{ $item->assigned_qty ?? 0 }}</div>
                            <div class="qty-label">Assigned</div>
                        </div>
                        <div class="qty-item">
                            <div class="qty-val text-danger">{{ $item->pending_qty ?? 0 }}</div>
                            <div class="qty-label">Pending</div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list text-muted" style="font-size: 40px; opacity: 0.3; margin-bottom: 10px;"></i>
                <h5 class="font-weight-bold text-dark">No Assignments Found</h5>
                <p class="text-muted">Try adjusting your filters</p>
            </div>
        @endforelse

        @if($assignments->isNotEmpty() && !$productionStatus)
            <div class="assignment-card bg-primary text-white text-center">
                <div class="row">
                    <div class="col-6 border-right border-light">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; opacity: 0.8;">Total Assigned</div>
                        <div style="font-size: 20px; font-weight: 900;">{{ number_format($totalAssigned) }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; opacity: 0.8;">Total Pending</div>
                        <div style="font-size: 20px; font-weight: 900;">{{ number_format($totalPending) }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>


@endsection
