@extends('layouts.unit')

@section('title', 'Lot Details - ' . $data['lot_no'])

@section('content')
    @php use Carbon\Carbon; @endphp

    <style>
        /* ================= PAGE ================= */
        body {
            background: #f4f6f9;
        }

        .report-page {
            padding: 15px;
            padding-bottom: 100px;
        }

        /* ================= MOBILE CARDS ================= */
        .app-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
        }

        .app-card-header {
            padding: 18px 20px;
            font-weight: 800;
            font-size: 18px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ================= SUMMARY INFO ================= */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            padding: 20px;
        }

        .summary-item {
            background: #f8fafc;
            border-radius: 14px;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        .summary-item-icon {
            width: 36px;
            height: 36px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .summary-item label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .summary-item div {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-item.full-width {
            grid-column: span 2;
            flex-direction: row;
            align-items: center;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .summary-item.full-width .summary-item-icon {
            background: rgba(255,255,255,0.2);
            color: white;
            margin-bottom: 0;
            margin-right: 15px;
            width: 45px;
            height: 45px;
            font-size: 20px;
        }

        .summary-item.full-width label {
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }

        .summary-item.full-width div {
            color: white;
            font-size: 22px;
        }

        /* ================= TABLES ================= */
        .app-table-wrapper {
            padding: 10px 20px 20px;
        }

        .app-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .app-table th {
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
            padding: 0 10px 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .app-table td {
            background: #f8fafc;
            padding: 12px 15px;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .app-table td:first-child { border-radius: 10px 0 0 10px; }
        .app-table td:last-child { border-radius: 0 10px 10px 0; }

        .app-table tr.total-row td {
            background: #e0e7ff;
            color: #4f46e5;
            font-size: 16px;
        }


        /* ================= PROGRESS CARDS ================= */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .stage-card {
            border-radius: 10px;
            padding: 14px;
            position: relative;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
            border-left: 6px solid;
        }

        .stage-card h5 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .stage-card .unit {
            font-size: 12px;
            color: #374151;
            margin-bottom: 10px;
        }

        .stage-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .metric {
            background: rgba(255, 255, 255, .7);
            border-radius: 6px;
            padding: 6px;
            text-align: center;
            font-size: 13px;
        }

        .metric strong {
            display: block;
            font-size: 15px;
        }

        /* STATUS */
        .card-progress {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .card-completed {
            background: #dcfce7;
            border-color: #16a34a;
        }

        .card-delayed {
            background: #fee2e2;
            border-color: #dc2626;
        }

        .card-not_started {
            background: #e5e7eb;
            border-color: #6b7280;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 14px;
            color: #fff;
        }

        .badge-progress {
            background: #f59e0b;
        }

        .badge-completed {
            background: #16a34a;
        }

        .badge-delayed {
            background: #dc2626;
        }

        .badge-not_started {
            background: #6b7280;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .summary-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>

    <div class="report-page">
        @php $lot = $data['lots_data'][0] ?? null; @endphp

        <div class="app-card">
            <div class="app-card-header">
                <i class="fas fa-info-circle text-primary"></i> Order Summary
            </div>
            <div class="summary-grid">
                
                <div class="summary-item full-width">
                    <div class="summary-item-icon"><i class="fas fa-hashtag"></i></div>
                    <div>
                        <label>Lot Number</label>
                        <div>{{ $lot->lot_no ?? '-' }}</div>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-item-icon" style="background:#fef3c7; color:#d97706;"><i class="fas fa-pencil-alt"></i></div>
                    <label>Design No.</label>
                    <div>{{ $lot->orderProductSet->design_number ?? '-' }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-item-icon" style="background:#fce7f3; color:#db2777;"><i class="fas fa-scroll"></i></div>
                    <label>Fabric</label>
                    <div>{{ $lot->orderProductSet->fabric->name ?? '-' }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-item-icon" style="background:#dcfce7; color:#16a34a;"><i class="fas fa-palette"></i></div>
                    <label>Color</label>
                    <div>{{ $lot->orderProductSet->colors->name ?? '-' }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-item-icon" style="background:#e0f2fe; color:#0284c7;"><i class="fas fa-vector-square"></i></div>
                    <label>Pattern</label>
                    <div>{{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-item-icon" style="background:#f3e8ff; color:#9333ea;"><i class="fas fa-tshirt"></i></div>
                    <label>Fitting</label>
                    <div>{{ $lot->orderProductSet->master_product_fitting->name ?? '-' }}</div>
                </div>
                
                <div class="summary-item full-width" style="background: #1e293b;">
                    <div class="summary-item-icon"><i class="fas fa-industry"></i></div>
                    <div>
                        <label>Production Unit</label>
                        <div>{{ $lot->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <i class="fas fa-cut text-warning"></i> Size Wise Quantity
            </div>
            <div class="app-table-wrapper">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Size</th>
                            <th style="text-align: right;">Pieces</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $qtyTotal = 0; @endphp
                        @foreach($data['rolls_data'] as $roll)
                            @foreach($roll->fabricRollAssigningsDetail ?? [] as $d)
                                <tr>
                                    <td>{{ $d->size }}</td>
                                    <td style="text-align: right;">{{ $d->quantity }}</td>
                                </tr>
                                @php $qtyTotal += $d->quantity; @endphp
                            @endforeach
                        @endforeach
                        <tr class="total-row">
                            <td>Total Pieces</td>
                            <td style="text-align: right;">{{ $qtyTotal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <i class="fas fa-scroll text-danger"></i> Roll Consumption
            </div>
            <div class="app-table-wrapper">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Roll No.</th>
                            <th style="text-align: right;">Meters Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['rolls_data'] as $roll)
                            <tr>
                                <td>{{ $roll->roll_no }}</td>
                                <td style="text-align: right;">{{ $roll->meter }} m</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>Total Consumption</td>
                            <td style="text-align: right;">{{ $data['rolls_data']->sum('meter') }} m</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($lot->productionSlipDigitization) && $lot->productionSlipDigitization->slip_file)
        <div class="app-card">
            <div class="app-card-header">
                <i class="fas fa-image text-success"></i> Slip Uploaded (Cutting)
            </div>
            <div style="padding: 20px; text-align: center; background: #fafafa;">
                @php
                    $ext = pathinfo($lot->productionSlipDigitization->slip_file, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                @endphp
                
                @if($isImage)
                    <a href="{{ asset('assets/production_slips/' . $lot->productionSlipDigitization->slip_file) }}" target="_blank">
                        <img src="{{ asset('assets/production_slips/' . $lot->productionSlipDigitization->slip_file) }}" alt="Cutting Slip" style="max-width: 100%; max-height: 500px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    </a>
                @else
                    <a href="{{ asset('assets/production_slips/' . $lot->productionSlipDigitization->slip_file) }}" target="_blank" class="btn btn-primary" style="border-radius: 12px; padding: 15px 30px; font-weight: 700;">
                        <i class="fas fa-file-pdf mr-2"></i> View Uploaded Document
                    </a>
                @endif
            </div>
        </div>
        @endif

    </div>
@endsection