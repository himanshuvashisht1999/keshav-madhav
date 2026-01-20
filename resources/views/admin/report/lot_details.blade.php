@extends('admin.layouts.app')

@section('content')
@php use Carbon\Carbon; @endphp

<style>
/* ===== PAGE ===== */
.report-page { background:#f4f6f9; }

/* ===== TOP BAR ===== */
.top-bar{
    background:#ffffff;
    padding:10px 16px;
    border-radius:6px;
    box-shadow:0 1px 4px rgba(0,0,0,.08);
    display:flex;
    justify-content:space-between;
    font-size:14px;
    margin-bottom:12px;
}

/* ===== SECTION ===== */
.section{
    background:#fff;
    border-radius:6px;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
    margin-bottom:12px;
}
.section-title{
    padding:10px 14px;
    font-weight:600;
    border-bottom:1px solid #e5e7eb;
}

/* ===== TABLE ===== */
.compact-table{
    width:100%;
    font-size:13px;
}
.compact-table th{
    background:#111827;
    color:#fff;
    padding:6px;
}
.compact-table td{
    padding:6px;
}
.compact-table tr:last-child td{
    background:#f1f5f9;
    font-weight:600;
}

/* ===== PROGRESS LIST ===== */
.stage-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:10px 12px;
    border-bottom:1px solid #e5e7eb;
    font-size:13px;
}
.stage-row:last-child{ border-bottom:none; }

.stage-left{
    display:flex;
    align-items:center;
    gap:10px;
}
.stage-indicator{
    width:4px;
    height:100%;
    border-radius:2px;
}

/* ===== STATUS COLORS ===== */
.indicator-delayed{ background:#dc2626; }
.indicator-completed{ background:#16a34a; }
.indicator-progress{ background:#f59e0b; }

.badge{
    font-size:11px;
    padding:4px 8px;
    border-radius:12px;
    font-weight:600;
}
.badge-danger{ background:#fee2e2;color:#991b1b; }
.badge-success{ background:#dcfce7;color:#166534; }
.badge-warning{ background:#fef3c7;color:#92400e; }

.stage-meta{
    color:#6b7280;
    font-size:12px;
}
</style>

<div class="content-wrapper report-page">
<section class="content">
<div class="container-fluid">

{{-- ================= TOP BAR ================= --}}
@php $lot = $data['lots_data'][0] ?? null; @endphp
<div class="top-bar">
    <div>
        <strong>Lot:</strong> {{ $lot->lot_no ?? '-' }} |
        <strong>Order:</strong> {{ $lot->orderProductSet->orderMain->sku ?? '-' }} |
        <strong>Customer:</strong> {{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}
    </div>
    <div>{{ now()->format('d M Y') }}</div>
</div>

{{-- ================= ORDER SUMMARY ================= --}}
<div class="section">
    <div class="section-title">Order Summary</div>
    <div class="p-3 row">
        <div class="col-md-3"><strong>Fabric:</strong> {{ $lot->orderProductSet->fabric->name ?? '-' }}</div>
        <div class="col-md-3"><strong>Color:</strong> {{ $lot->orderProductSet->colors->name ?? '-' }}</div>
        <div class="col-md-3"><strong>Pattern:</strong> {{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</div>
        <div class="col-md-3"><strong>Unit:</strong> {{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div>
    </div>
</div>

{{-- ================= CUTTING & ROLLS ================= --}}
<div class="section">
    <div class="section-title">Cutting & Rolls</div>
    <div class="row p-3">
        <div class="col-md-6">
            <table class="compact-table">
                <thead><tr><th>Size</th><th class="text-right">Qty</th></tr></thead>
                <tbody>
                @php $total=0; @endphp
                @foreach($data['rolls_data'] as $roll)
                    @foreach($roll->fabricRollAssigningsDetail ?? [] as $d)
                    <tr><td>{{ $d->size }}</td><td class="text-right">{{ $d->quantity }}</td></tr>
                    @php $total+=$d->quantity; @endphp
                    @endforeach
                @endforeach
                <tr><td>Total</td><td class="text-right">{{ $total }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <table class="compact-table">
                <thead><tr><th>Roll</th><th class="text-right">Meter</th></tr></thead>
                <tbody>
                @foreach($data['rolls_data'] as $roll)
                    <tr><td>{{ $roll->roll_no }}</td><td class="text-right">{{ $roll->meter }}</td></tr>
                @endforeach
                <tr><td>Total</td><td class="text-right">{{ $data['rolls_data']->sum('meter') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= PRODUCTION PROGRESS ================= --}}
<div class="section">
    <div class="section-title">Production Progress</div>

    @foreach($master_stages as $stage)
        @php
            $d = getLotDetails($data['lot_no'],$stage->id);
            if(!$d || !$d['time_allocation']) continue;

            $remaining=(int)$d['remaining_quantity'];
            $eta=Carbon::parse($d['time_allocation']);
            $completed=$d['completed_time']?Carbon::parse($d['completed_time']):null;

            $status='progress';
            if($remaining===0 && $completed && $completed->gt($eta)) $status='delayed';
            elseif($remaining>0 && now()->gt($eta)) $status='delayed';
            elseif($remaining===0) $status='completed';
        @endphp

        <div class="stage-row">
            <div class="stage-left">
                <div class="stage-indicator
                    {{ $status=='delayed'?'indicator-delayed':($status=='completed'?'indicator-completed':'indicator-progress') }}">
                </div>
                <div>
                    <strong>{{ $stage->name }}</strong>
                    <div class="stage-meta">
                        Unit: {{ $d['unit_name'] }} |
                        Remaining: {{ $remaining }} |
                        ETA: {{ $eta->format('d M Y') }}
                    </div>
                </div>
            </div>

            <div>
                @if($status=='delayed')
                    <span class="badge badge-danger">Delayed</span>
                @elseif($status=='completed')
                    <span class="badge badge-success">Completed</span>
                @else
                    <span class="badge badge-warning">In Progress</span>
                @endif
            </div>
        </div>
    @endforeach
</div>

</div>
</section>
</div>
@endsection
