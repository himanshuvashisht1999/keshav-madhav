@extends('owner.layouts.app')

@section('title', 'Lot Progress Details')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        --status-completed: #10b981;
        --status-progress: #3b82f6;
        --status-delayed: #ef4444;
        --status-not_started: #94a3b8;
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 30px 20px 60px;
        color: white;
    }

    .lot-info-card {
        margin-top: -40px;
        padding: 0 20px;
    }

    .info-card {
        background: white;
        border-radius: 28px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }

    .lot-number-badge {
        font-size: 11px;
        font-weight: 800;
        background: #f1f5f9;
        color: #475569;
        padding: 6px 14px;
        border-radius: 10px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 10px;
    }

    .lot-title {
        font-size: 24px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.5px;
        margin-bottom: 5px;
    }

    .lot-subtitle {
        font-size: 13px;
        color: #64748b;
        font-weight: 700;
    }

    .progress-container {
        padding: 30px 20px;
    }

    .stage-item {
        position: relative;
        padding-left: 50px;
        margin-bottom: 40px;
    }

    .stage-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: -40px;
        width: 3px;
        background: #e2e8f0;
        z-index: 0;
    }

    .stage-item:last-child::before {
        display: none;
    }

    .stage-dot {
        position: absolute;
        left: 8px;
        top: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: white;
        border: 4px solid var(--status-not_started);
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--status-not_started);
        font-weight: 900;
    }

    /* Status specific dots */
    .stage-item.completed .stage-dot { border-color: var(--status-completed); background: var(--status-completed); color: white; }
    .stage-item.progress .stage-dot { border-color: var(--status-progress); color: var(--status-progress); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.1); }
    .stage-item.delayed .stage-dot { border-color: var(--status-delayed); background: var(--status-delayed); color: white; }

    .stage-content {
        background: white;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
    }

    .stage-name {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stage-meta {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        margin-top: 4px;
        display: block;
    }

    .qty-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #e2e8f0;
    }

    .qty-box {
        text-align: center;
    }

    .qty-label {
        font-size: 9px;
        color: #64748b;
        font-weight: 800;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }

    .qty-val {
        font-size: 16px;
        font-weight: 900;
        color: #1e293b;
    }

    .eta-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 8px 12px;
        margin-top: 12px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        color: #475569;
    }

    .status-badge-small {
        font-size: 8px;
        padding: 3px 8px;
        border-radius: 5px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-completed { background: #dcfce7; color: #16a34a; }
    .badge-progress { background: #dbeafe; color: #2563eb; }
    .badge-delayed { background: #fee2e2; color: #dc2626; }
    .badge-not_started { background: #f1f5f9; color: #64748b; }

    .fab-pdf {
        position: fixed;
        bottom: 30px;
        right: 20px;
        width: 56px;
        height: 56px;
        background: #ef4444;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        text-decoration: none !important;
    }
</style>
@endsection

@section('content')
<div class="mobile-only">
    @php 
        $lot = $data['lots_data'][0] ?? null; 
        $lot_no = $data['lot_no'];
    @endphp

    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('owner.lots') }}" class="text-white opacity-75"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold" style="font-size: 14px; opacity: 0.8;">Lot Tracking</span>
            <div style="width: 20px;"></div>
        </div>
    </div>

    <div class="lot-info-card">
        <div class="info-card">
            <span class="lot-number-badge">LOT #{{ $lot_no }}</span>
            <div class="lot-title">{{ $lot->orderProductSet->orderMain->sku ?? '-' }}</div>
            <div class="lot-subtitle"><i class="far fa-user mr-1 text-muted"></i> {{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}</div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-around text-center">
                <div>
                    <span class="qty-label">Design</span>
                    <span class="qty-val d-block" style="font-size: 13px;">{{ $lot->orderProductSet->design_number ?? '-' }}</span>
                </div>
                <div style="width: 1px; background: #f1f5f9;"></div>
                <div>
                    <span class="qty-label">Color</span>
                    <span class="qty-val d-block" style="font-size: 13px;">{{ $lot->orderProductSet->colors->name ?? '-' }}</span>
                </div>
                <div style="width: 1px; background: #f1f5f9;"></div>
                <div>
                    <span class="qty-label">Unit</span>
                    <span class="qty-val d-block" style="font-size: 13px;">{{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="progress-container">
        @foreach($master_stages as $stage)
            @php
                $d = getLotDetails($lot_no, $stage->id);
                if (!$d || !$d['time_allocation']) continue;

                $remaining = (int) $d['remaining_quantity'];
                $total = (int) $d['quantity'];
                $eta = \Carbon\Carbon::parse($d['time_allocation']);
                $completed = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;

                if ($total === 0) $status = 'not_started';
                elseif ($remaining === 0) $status = ($completed && $completed->gt($eta)) ? 'delayed' : 'completed';
                elseif (now()->gt($eta)) $status = 'delayed';
                else $status = 'progress';
            @endphp
            
            <div class="stage-item {{ $status }}">
                <div class="stage-dot">
                    @if($status === 'completed')
                        <i class="fas fa-check"></i>
                    @else
                        {{ $loop->iteration }}
                    @endif
                </div>
                <div class="stage-content">
                    <div class="stage-name">
                        {{ $stage->name }}
                        <span class="status-badge-small badge-{{ $status }}">
                            {{ str_replace('_', ' ', $status) }}
                        </span>
                    </div>
                    <span class="stage-meta">Unit: {{ $d['unit_name'] ?: 'Not Assigned' }}</span>

                    <div class="qty-row">
                        <div class="qty-box">
                            <span class="qty-label">Total Pcs</span>
                            <span class="qty-val">{{ $total }}</span>
                        </div>
                        <div class="qty-box">
                            <span class="qty-label">Remaining</span>
                            <span class="qty-val {{ $remaining > 0 ? 'text-primary' : 'text-success' }}">{{ $remaining }}</span>
                        </div>
                    </div>

                    <div class="eta-box">
                        <span><i class="far fa-calendar-alt mr-1"></i> ETA: {{ $eta->format('d M Y') }}</span>
                        @if($completed)
                            <span class="text-success"><i class="fas fa-check-circle mr-1"></i> {{ $completed->format('d M') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('owner.lot-details.pdf', ['lot_no' => $lot_no]) }}" class="fab-pdf">
        <i class="fas fa-file-pdf"></i>
    </a>
</div>

<div class="desktop-only p-5 text-center">
    <h3>Switch to Mobile View</h3>
    <p>Detailed lot tracking is optimized for the owner app interface.</p>
</div>
@endsection