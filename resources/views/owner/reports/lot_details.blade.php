@extends('owner.layouts.app')

@section('content')
@php use Carbon\Carbon; @endphp

<style>
    /* COMPREHENSIVE APP STYLES (MATCHING ADMIN COMPLETENESS + PURPLE THEME) */
    .report-container {
        padding: 20px;
    }

    /* Section Headers */
    .app-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .app-section-title {
        background: #f8fafc;
        padding: 12px 16px;
        font-weight: 700;
        color: #1e293b;
        border-bottom: 1px solid #edf2f7;
        font-size: 15px;
    }

    /* Info Cards (Top Row) */
    .info-app-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-app-card {
        background: white;
        padding: 15px;
        border-radius: 12px;
        border-left: 4px solid var(--primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .info-app-card label {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .info-app-card div {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Summary Grid */
    .summary-app-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        padding: 15px;
    }

    .summary-app-item {
        background: #f1f5f9;
        padding: 12px;
        border-radius: 8px;
    }

    .summary-app-item label {
        font-size: 10px;
        color: #64748b;
        font-weight: 600;
    }

    .summary-app-item div {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Cutting & Rolls */
    .cutting-app-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        padding: 15px;
    }

    @media (max-width: 768px) {
        .cutting-app-grid { grid-template-columns: 1fr; }
    }

    .app-table-compact {
        width: 100%;
        font-size: 13px;
    }

    .app-table-compact th {
        text-align: left;
        color: #64748b;
        padding: 8px;
        border-bottom: 1px solid #edf2f7;
    }

    .app-table-compact td {
        padding: 8px;
        border-bottom: 1px solid #f8fafc;
    }

    /* Production Progress (Timeline/Cards) */
    .progress-app-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        padding: 15px;
    }

    .stage-app-card {
        border-radius: 12px;
        padding: 16px;
        position: relative;
        border-left: 6px solid;
    }

    /* Statuses */
    .st-progress { background: #eff6ff; border-color: #2563eb; }
    .st-completed { background: #f0fdf4; border-color: #16a34a; }
    .st-delayed { background: #fef2f2; border-color: #dc2626; }
    .st-waiting { background: #f8fafc; border-color: #94a3b8; }

    .stage-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .stage-title { font-weight: 800; font-size: 14px; color: #1e293b; }
    
    .status-pill {
        font-size: 9px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 10px;
        text-transform: uppercase;
        color: white;
    }
    .sp-progress { background: #2563eb; }
    .sp-completed { background: #16a34a; }
    .sp-delayed { background: #dc2626; }
    .sp-waiting { background: #94a3b8; }

    .metric-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .metric-box {
        background: rgba(255,255,255,0.6);
        padding: 8px;
        border-radius: 8px;
        text-align: center;
    }

    .metric-box label { font-size: 9px; color: #64748b; display: block; margin-bottom: 2px; }
    .metric-box span { font-size: 14px; font-weight: 800; color: #1e293b; }
</style>

<div class="report-container">
    @php 
        $lot_no = $data['lot_no'];
        $lot_first = $data['lots_data'][0] ?? null; 
    @endphp

    {{-- ================= INFO HEADER ================= --}}
    <div class="info-app-grid">
        <div class="info-app-card">
            <label>Lot Number</label>
            <div>#{{ $lot_no }}</div>
        </div>
        <div class="info-app-card">
            <label>Order SKU</label>
            <div>{{ $lot_first->orderProductSet->orderMain->sku ?? '-' }}</div>
        </div>
        <div class="info-app-card">
            <label>Customer</label>
            <div>{{ $lot_first->orderProductSet->orderMain->customer->name ?? '-' }}</div>
        </div>
        <div class="info-app-card">
            <label>Report Date</label>
            <div>{{ now()->format('d M Y') }}</div>
        </div>
    </div>

    {{-- ================= ORDER SUMMARY ================= --}}
    <div class="app-section">
        <div class="app-section-title">Order & Fabric Details</div>
        <div class="summary-app-grid">
            <div class="summary-app-item">
                <label>Fabric</label>
                <div>{{ $lot_first->orderProductSet->fabric->name ?? '-' }}</div>
            </div>
            <div class="summary-app-item">
                <label>Color</label>
                <div>{{ $lot_first->orderProductSet->colors->name ?? '-' }}</div>
            </div>
            <div class="summary-app-item">
                <label>Pattern</label>
                <div>{{ $lot_first->orderProductSet->master_design_pattern->name ?? '-' }}</div>
            </div>
            <div class="summary-app-item">
                <label>Main Unit</label>
                <div>{{ $lot_first->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- ================= CUTTING & ROLLS ================= --}}
    <div class="app-section">
        <div class="app-section-title">Cutting & Roll Consumption</div>
        <div class="cutting-app-grid">
            <div>
                <h6 style="font-size: 13px; font-weight: 700; padding: 0 8px;">Size Wise Breakdown</h6>
                <table class="app-table-compact">
                    <thead><tr><th>Size</th><th style="text-align: right;">Quantity</th></tr></thead>
                    <tbody>
                        @php $total_qty = 0; @endphp
                        @foreach($data['rolls_data'] as $roll)
                            @foreach($roll->fabricRollAssigningsDetail ?? [] as $detail)
                                <tr>
                                    <td>{{ $detail->size }}</td>
                                    <td style="text-align: right;">{{ $detail->quantity }}</td>
                                </tr>
                                @php $total_qty += $detail->quantity; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; font-weight: 800;">
                        <tr><td>TOTAL</td><td style="text-align: right;">{{ $total_qty }}</td></tr>
                    </tfoot>
                </table>
            </div>
            <div>
                <h6 style="font-size: 13px; font-weight: 700; padding: 0 8px;">Roll Consumed</h6>
                <table class="app-table-compact">
                    <thead><tr><th>Roll No</th><th style="text-align: right;">Meters</th></tr></thead>
                    <tbody>
                        @foreach($data['rolls_data'] as $roll)
                            <tr>
                                <td>{{ $roll->roll_no }}</td>
                                <td style="text-align: right;">{{ number_format($roll->meter, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; font-weight: 800;">
                        <tr><td>TOTAL</td><td style="text-align: right;">{{ number_format($data['rolls_data']->sum('meter'), 2) }} m</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= PRODUCTION PROGRESS ================= --}}
    <div class="app-section">
        <div class="app-section-title">Production Progress Tracking</div>
        <div class="progress-app-grid">
            @foreach($master_stages as $stage)
                @php
                    $d = getLotDetails($lot_no, $stage->id);
                    if(!$d || !$d['time_allocation']) continue;

                    $remaining = (int)$d['remaining_quantity'];
                    $total = (int)$d['quantity'];
                    $eta = Carbon::parse($d['time_allocation']);
                    $completed = $d['completed_time'] ? Carbon::parse($d['completed_time']) : null;

                    if ($total === 0) $status = 'waiting';
                    elseif ($remaining === 0) $status = ($completed && $completed->gt($eta)) ? 'delayed' : 'completed';
                    elseif (now()->gt($eta)) $status = 'delayed';
                    else $status = 'progress';
                @endphp
                
                <div class="stage-app-card st-{{ $status }}">
                    <div class="stage-header">
                        <div class="stage-title">{{ $stage->name }}</div>
                        <span class="status-pill sp-{{ $status }}">{{ ucfirst($status) }}</span>
                    </div>
                    <div style="font-size: 11px; margin-bottom: 12px; color: #475569;">
                        <i class="fas fa-building"></i> {{ $status == 'waiting' ? 'Not Assigned' : ($d['unit_name'] ?? 'N/A') }}
                    </div>
                    <div class="metric-row">
                        <div class="metric-box">
                            <label>Total</label>
                            <span>{{ number_format($total) }}</span>
                        </div>
                        <div class="metric-box">
                            <label>Remaining</label>
                            <span style="color: {{ $remaining > 0 ? '#dc2626' : '#16a34a' }}">{{ number_format($remaining) }}</span>
                        </div>
                        <div class="metric-box" style="grid-column: span 2; margin-top: 5px;">
                            <label>Target Date (ETA)</label>
                            <span>{{ $eta->format('d M, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection