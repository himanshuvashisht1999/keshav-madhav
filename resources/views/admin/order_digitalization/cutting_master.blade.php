@extends('admin.layouts.app')

@section('content')
<style>
/* ================= PAGE ================= */
.page-bg { background:#f4f6f9; }

/* ================= HEADER ================= */
.report-header {
    background: linear-gradient(135deg,#1e293b,#334155);
    color:#fff;
    padding:16px 20px;
    border-radius:10px;
    margin-bottom:16px;
}
.report-header h4 { margin:0;font-weight:700; }

/* ================= INFO CARDS ================= */
.info-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
}
.info-card {
    background:#fff;
    border-radius:10px;
    padding:12px 14px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}
.info-card .label { font-size:12px;color:#6b7280;font-weight:600; }
.info-card .value { font-size:15px;font-weight:700;color:#111827; }

/* ================= TABLE ================= */
.simple-table { width:100%;font-size:13px; }
.simple-table th {
    background:#111827;color:#fff;padding:8px;
}
.simple-table td { padding:7px; }
.simple-table tr:last-child td {
    background:#f1f5f9;font-weight:700;
}

/* ================= STAGES ================= */
.stage-wrapper { margin-top:20px; }

.stage-box {
    background:#fff;
    border-radius:10px;
    padding:14px;
    margin-bottom:14px;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
    border-left:6px solid #3b82f6;
}

.stage-completed {
    background:#ecfdf5;
    border-left-color:#16a34a;
}
.stage-delayed {
    background:#fef2f2;
    border-left-color:#dc2626;
}
.stage-progress {
    background:#eff6ff;
    border-left-color:#2563eb;
}

.stage-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:8px;
}

.stage-title {
    font-size:16px;
    font-weight:700;
    color:#111827;
}

/* STATUS BADGES */
.stage-badge {
    padding:4px 10px;
    font-size:12px;
    border-radius:20px;
    font-weight:600;
}
.badge-completed { background:#bbf7d0;color:#166534; }
.badge-delayed   { background:#fecaca;color:#7f1d1d; }
.badge-progress  { background:#bfdbfe;color:#1e3a8a; }

.stage-row {
    display:flex;
    justify-content:space-between;
    font-size:13px;
    padding:3px 0;
}
.stage-row span:last-child { font-weight:700; }

.qty-total { color:#047857; }
.qty-remaining { color:#b91c1c; }
.delay-text { color:#991b1b;font-size:12px;font-weight:600; }
</style>

<div class="content-wrapper page-bg">
<section class="content">
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="report-header">
        <div class="d-flex justify-content-between">
            <div>
                <h4>Lot Production Status</h4>
                <small>Lot No : {{ $data['lots_data'][0]->lot_no ?? '' }}</small>
            </div>
            <div>
                <small>{{ now()->format('d M Y, h:i A') }}</small>
            </div>
        </div>
    </div>

    {{-- ORDER INFO --}}
    @foreach ($data['lots_data'] as $lot)
    <div class="info-grid mb-3">
        <div class="info-card"><div class="label">Order</div><div class="value">{{ $lot->orderProductSet->orderMain->sku ?? '-' }}</div></div>
        <div class="info-card"><div class="label">Customer</div><div class="value">{{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}</div></div>
        <div class="info-card"><div class="label">Fabric</div><div class="value">{{ $lot->orderProductSet->fabric->name ?? '-' }}</div></div>
        <div class="info-card"><div class="label">Color</div><div class="value">{{ $lot->orderProductSet->colors->name ?? '-' }}</div></div>
        <div class="info-card"><div class="label">Pattern</div><div class="value">{{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</div></div>
        <div class="info-card"><div class="label">Unit</div><div class="value">{{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div></div>
    </div>
    @endforeach

    {{-- CUTTING & ROLLS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="mb-2 font-weight-bold">Cutting Quantity</h6>
                <table class="simple-table">
                    <thead>
                        <tr><th>Size</th><th class="text-right">Qty</th></tr>
                    </thead>
                    <tbody>
                    @php $total = 0; @endphp
                    @foreach($data['rolls_data'] as $roll)
                        @foreach($roll->fabricRollAssigningsDetail ?? [] as $d)
                            <tr>
                                <td>{{ $d->size }}</td>
                                <td class="text-right">{{ $d->quantity }}</td>
                            </tr>
                            @php $total += $d->quantity; @endphp
                        @endforeach
                    @endforeach
                    <tr>
                        <td>Total</td>
                        <td class="text-right">{{ $total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card">
                <h6 class="mb-2 font-weight-bold">Roll Consumption</h6>
                <table class="simple-table">
                    <thead>
                        <tr><th>Roll</th><th class="text-right">Meter</th></tr>
                    </thead>
                    <tbody>
                    @foreach($data['rolls_data'] as $roll)
                        <tr>
                            <td>{{ $roll->roll_no }}</td>
                            <td class="text-right">{{ $roll->meter }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Total</td>
                        <td class="text-right">{{ $data['rolls_data']->sum('meter') }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- STAGE PROGRESS --}}
    <div class="stage-wrapper">
        <h5 class="mb-3 font-weight-bold">Production Progress</h5>

        @foreach($master_stages as $stage)
            @php
                use Carbon\Carbon;

                $lot_details = getLotDetails($data['lot_no'], $stage->id);
                if(!$lot_details) continue;

                $remaining = (int)($lot_details['remaining_quantity'] ?? 0);

                $estimatedAt = null;
                if(!empty($lot_details['time_allocation'])) {
                    try { $estimatedAt = Carbon::parse($lot_details['time_allocation']); } catch(Exception $e) {}
                }

                $completedAt = null;
                if(!empty($lot_details['completed_time'])) {
                    try { $completedAt = Carbon::parse($lot_details['completed_time']); } catch(Exception $e) {}
                }

                if ($remaining === 0) {
                    if ($estimatedAt && $completedAt && $completedAt->gt($estimatedAt)) {
                        $status = 'delayed';
                    } else {
                        $status = 'completed';
                    }
                } else {
                    if ($estimatedAt && now()->gt($estimatedAt)) {
                        $status = 'delayed';
                    } else {
                        $status = 'progress';
                    }
                }
            @endphp

            <div class="stage-box stage-{{ $status }}">
                <div class="stage-header">
                    <span class="stage-title">{{ $stage->name }}</span>

                    @if($status === 'completed')
                        <span class="stage-badge badge-completed">Completed</span>
                    @elseif($status === 'delayed')
                        <span class="stage-badge badge-delayed">Delayed</span>
                    @else
                        <span class="stage-badge badge-progress">In Progress</span>
                    @endif
                </div>

                <div class="stage-row">
                    <span>Unit</span>
                    <span>{{ $lot_details['unit_name'] ?? 'N/A' }}</span>
                </div>

                <div class="stage-row">
                    <span>Total Qty</span>
                    <span class="qty-total">{{ $lot_details['quantity'] }}</span>
                </div>

                <div class="stage-row">
                    <span>Remaining</span>
                    <span class="qty-remaining">{{ $remaining }}</span>
                </div>

                <div class="stage-row">
                    <span>Estimated Completion</span>
                    <span>{{ $estimatedAt?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                </div>

                @if($status === 'completed')
                <div class="stage-row">
                    <span>Completed On</span>
                    <span>{{ $completedAt?->format('d M Y, h:i A') }}</span>
                </div>
                @endif

                @if($status === 'delayed' && $estimatedAt)
                <div class="stage-row">
                    <span>Delay</span>
                    <span class="delay-text">
                        {{ $completedAt 
                            ? $estimatedAt->diffInDays($completedAt) 
                            : $estimatedAt->diffInDays(now()) 
                        }} day(s)
                    </span>
                </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
</section>
</div>
@endsection
