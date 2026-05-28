@extends('owner.layouts.app')

@section('title', 'Roll Tracking')

@section('styles')
<style>
    :root {
        --accent-color: var(--text-main);
    }

    body {
        background: #f1f5f9;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 30px 20px 60px;
        color: white;
    }

    .roll-info-floating {
        margin-top: -40px;
        padding: 0 20px;
    }

    .info-card {
        background: white;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        text-align: center;
    }

    .roll-id-big {
        font-size: 32px;
        font-weight: 900;
        color: var(--text-main);
        letter-spacing: -1px;
    }

    .fabric-name-sub {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 15px;
        display: block;
    }

    .ledger-container {
        padding: 30px 20px;
    }

    .timeline {
        position: relative;
        padding-left: 45px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-dot {
        position: absolute;
        left: -33px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--accent-color);
        z-index: 1;
    }

    .timeline-item.negative .timeline-dot {
        border-color: #ef4444;
    }

    .timeline-item.positive .timeline-dot {
        border-color: #10b981;
    }

    .timeline-content {
        background: white;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
    }

    .item-date {
        font-size: 10px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .item-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .item-qty {
        font-weight: 900;
    }

    .qty-in { color: #10b981; }
    .qty-out { color: #ef4444; }

    .item-details {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
        padding: 10px;
        background: #f8fafc;
        border-radius: 12px;
        margin-top: 10px;
    }

    .item-meta {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .meta-tag {
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('owner.report.stock.rolls') }}" class="text-white"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold" style="font-size: 14px; opacity: 0.8;">Tracking Detail</span>
            <div style="width: 20px;"></div>
        </div>
        <div class="text-center pb-3">
            <p class="mb-0 opacity-75" style="font-size: 12px;">Roll Lifecycle History</p>
        </div>
    </div>

    <div class="roll-info-floating">
        <div class="info-card">
            <span class="fabric-name-sub">{{ $fabric->name ?? 'Unknown Fabric' }}</span>
            <div class="roll-id-big">Roll #{{ $rollNo }}</div>
            
            <div class="d-flex justify-content-around mt-4">
                <div>
                    <div class="stat-value" style="font-size: 18px; font-weight: 900;">{{ number_format($data->where('qty', '>', 0)->sum('qty'), 1) }}m</div>
                    <div class="stat-label" style="font-size: 10px; color: var(--text-muted); font-weight: 800;">TOTAL IN</div>
                </div>
                <div style="width: 1px; background: #f1f5f9;"></div>
                <div>
                    <div class="stat-value" style="font-size: 18px; font-weight: 900;">{{ number_format(abs($data->where('qty', '<', 0)->sum('qty')), 1) }}m</div>
                    <div class="stat-label" style="font-size: 10px; color: var(--text-muted); font-weight: 800;">TOTAL OUT</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ledger-container">
        <div class="timeline">
            @foreach($data as $item)
            <div class="timeline-item {{ $item->qty < 0 ? 'negative' : 'positive' }}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="item-date">{{ \Carbon\Carbon::parse($item->date)->format('d M Y, h:i A') }}</span>
                    <div class="item-title">
                        {{ $item->type }}
                        <span class="item-qty {{ $item->qty < 0 ? 'qty-out' : 'qty-in' }}">
                            {{ $item->qty > 0 ? '+' : '' }}{{ number_format($item->qty, 1) }}m
                        </span>
                    </div>
                    
                    <div class="item-details">
                        {{ $item->details }}
                    </div>

                    <div class="item-meta">
                        @if($item->order_no && $item->order_no != '-')
                            <span class="meta-tag"><i class="fas fa-file-invoice mr-1"></i> {{ $item->order_no }}</span>
                        @endif
                        @if($item->lot_no && $item->lot_no != '-')
                            <span class="meta-tag"><i class="fas fa-layer-group mr-1"></i> Lot: {{ $item->lot_no }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
