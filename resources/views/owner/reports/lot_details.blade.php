@extends('owner.layouts.app')

@section('content')
    @php use Carbon\Carbon; @endphp

    <style>
        .lot-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .lot-header h2 {
            font-weight: 800;
            margin: 0;
            color: #1e3a8a;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            margin: 20px;
            border: none;
            overflow: hidden;
        }

        .section-title {
            background: #f8f9fa;
            padding: 12px 20px;
            font-weight: 700;
            border-bottom: 1px solid #eee;
            color: #1e3a8a;
        }

        /* Progress cards mirror admin but with owner sizing */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .stage-card {
            border-radius: 10px;
            padding: 15px;
            position: relative;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
            border-left: 6px solid;
            background: white;
        }

        .card-completed {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .card-progress {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .card-delayed {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .card-not_started {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        @media (max-width: 991.98px) {
            .lot-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                color: white;
            }

            .lot-header h2 {
                color: white;
            }

            .section-card {
                margin: 15px;
            }
        }
    </style>

    <div class="lot-header">
        @php $lot = $data['lots_data'][0] ?? null; @endphp
        <h2>Tracking Lot: {{ $lot->lot_no ?? '-' }}</h2>
        <div class="text-sm opacity-80">{{ $lot->orderProductSet->orderMain->sku ?? '-' }} |
            {{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}</div>
    </div>

    <div class="section-card">
        <div class="section-title">Production Summary</div>
        <div class="p-3">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="text-xs text-muted d-block font-weight-bold">Fabric</label>
                    <span>{{ $lot->orderProductSet->fabric->name ?? '-' }}</span>
                </div>
                <div class="col-6 mb-3">
                    <label class="text-xs text-muted d-block font-weight-bold">Unit</label>
                    <span>{{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</span>
                </div>
                <div class="col-6">
                    <label class="text-xs text-muted d-block font-weight-bold">Total Pcs</label>
                    <span
                        class="h5 font-weight-bold">{{ $data['rolls_data']->sum(function ($roll) {
        return $roll->fabricRollAssigningsDetail->sum('quantity'); }) }}
                        Pcs</span>
                </div>
                <div class="col-6">
                    <label class="text-xs text-muted d-block font-weight-bold">Meter Used</label>
                    <span class="h5 font-weight-bold">{{ $data['rolls_data']->sum('meter') }} Mtr</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">Stage Wise Progress</div>
        <div class="progress-grid">
            @foreach($master_stages as $stage)
                @php
                    $d = getLotDetails($data['lot_no'], $stage->id);
                    if (!$d || !$d['time_allocation'])
                        continue;

                    $remaining = (int) $d['remaining_quantity'];
                    $total = (int) $d['quantity'];
                    $eta = Carbon::parse($d['time_allocation']);
                    $completed = $d['completed_time'] ? Carbon::parse($d['completed_time']) : null;

                    if ($total === 0)
                        $status = 'not_started';
                    elseif ($remaining === 0)
                        $status = ($completed && $completed->gt($eta)) ? 'delayed' : 'completed';
                    elseif (now()->gt($eta))
                        $status = 'delayed';
                    else
                        $status = 'progress';
                @endphp
                <div class="stage-card card-{{ $status }}">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="font-weight-bold">{{ $stage->name }}</span>
                        <span
                            class="badge {{ $status == 'completed' ? 'bg-success' : ($status == 'delayed' ? 'bg-danger' : 'bg-warning') }} text-xxs">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </div>
                    <div class="text-xs mb-3 text-muted">Unit: {{ $d['unit_name'] }}</div>
                    <div class="row text-center bg-white rounded p-1 mx-0 border">
                        <div class="col-4 border-right">
                            <label class="d-block text-xxs text-muted m-0">Total</label>
                            <strong class="text-sm">{{ $total }}</strong>
                        </div>
                        <div class="col-4 border-right">
                            <label class="d-block text-xxs text-muted m-0">Left</label>
                            <strong class="text-sm text-danger">{{ $remaining }}</strong>
                        </div>
                        <div class="col-4">
                            <label class="d-block text-xxs text-muted m-0">Done</label>
                            <strong class="text-sm text-success">{{ $total - $remaining }}</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-xxs text-muted">ETA: {{ $eta->format('d M, Y') }}</div>
                </div>
            @endforeach
        </div>
    </div>

@endsection