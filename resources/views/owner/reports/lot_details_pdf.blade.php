<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lot Details - {{ $data['lot_no'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #7c3aed;
            font-size: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f8fafc;
            padding: 8px 12px;
            font-weight: bold;
            border-bottom: 1px solid #edf2f7;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-grid td {
            width: 25%;
            padding: 10px;
            border: 1px solid #edf2f7;
            vertical-align: top;
        }
        .info-grid label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .info-grid div {
            font-size: 11px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th, table td {
            border: 1px solid #edf2f7;
            padding: 8px;
            text-align: left;
        }
        table th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .stage-card {
            border: 1px solid #edf2f7;
            border-left: 5px solid #94a3b8;
            padding: 10px;
            margin-bottom: 12px;
            background: #fff;
            page-break-inside: avoid;
        }
        .st-progress { border-left-color: #2563eb; background: #eff6ff; }
        .st-completed { border-left-color: #16a34a; background: #f0fdf4; }
        .st-delayed { border-left-color: #dc2626; background: #fef2f2; }
        .st-waiting { border-left-color: #94a3b8; background: #f8fafc; }

        .status-pill {
            float: right;
            font-size: 8px;
            padding: 2px 8px;
            border-radius: 8px;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
        }
        .sp-progress { background: #2563eb; }
        .sp-completed { background: #16a34a; }
        .sp-delayed { background: #dc2626; }
        .sp-waiting { background: #94a3b8; }

        .stage-title { font-weight: bold; font-size: 12px; margin-bottom: 5px; }
        .stage-unit { font-size: 9px; color: #475569; margin-bottom: 8px; }
        
        .metric-table {
            width: 100%;
            border: none;
            margin-top: 5px;
        }
        .metric-table td {
            border: none;
            padding: 6px;
            text-align: center;
            background: #ffffff;
            border-radius: 4px;
        }
        .metric-label {
            font-size: 8px;
            color: #64748b;
            display: block;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .metric-value {
            font-size: 12px;
            font-weight: bold;
            display: block;
        }
    </style>
</head>
<body>
    @php 
        use Carbon\Carbon;
        $lot_no = $data['lot_no'];
        $lot_first = $data['lots_data'][0] ?? null; 
    @endphp

    <div class="header">
        <h1>LOT DETAILS REPORT</h1>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="info-grid">
        <tr>
            <td>
                <label>Lot Number</label>
                <div>#{{ $lot_no }}</div>
            </td>
            <td>
                <label>Order SKU</label>
                <div>{{ $lot_first->orderProductSet->orderMain->sku ?? '-' }}</div>
            </td>
            <td>
                <label>Customer</label>
                <div>{{ $lot_first->orderProductSet->orderMain->customer->name ?? '-' }}</div>
            </td>
            <td>
                <label>Report Date</label>
                <div>{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Order & Fabric Details</div>
        <table class="info-grid">
            <tr>
                <td>
                    <label>Fabric</label>
                    <div>{{ $lot_first->orderProductSet->fabric->name ?? '-' }}</div>
                </td>
                <td>
                    <label>Color</label>
                    <div>{{ $lot_first->orderProductSet->colors->name ?? '-' }}</div>
                </td>
                <td>
                    <label>Pattern</label>
                    <div>{{ $lot_first->orderProductSet->master_design_pattern->name ?? '-' }}</div>
                </td>
                <td>
                    <label>Design No</label>
                    <div>{{ $lot_first->orderProductSet->design_number ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <label>Fitting</label>
                    <div>{{ $lot_first->orderProductSet->master_product_fitting->name ?? '-' }}</div>
                </td>
                <td>
                    <label>Main Unit</label>
                    <div>{{ $lot_first->productionSlipDigitization->getUnitMaster->name ?? '-' }}</div>
                </td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Cutting & Roll Consumption</div>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 48%; border: none; vertical-align: top; padding: 0;">
                    <h4 style="margin-top: 0;">Size Wise Breakdown</h4>
                    <table>
                        <thead>
                            <tr><th>Size</th><th class="text-right">Quantity</th></tr>
                        </thead>
                        <tbody>
                            @php $total_qty = 0; @endphp
                            @foreach($data['rolls_data'] as $roll)
                                @foreach($roll->fabricRollAssigningsDetail ?? [] as $detail)
                                    <tr>
                                        <td>{{ $detail->size }}</td>
                                        <td class="text-right">{{ $detail->quantity }}</td>
                                    </tr>
                                    @php $total_qty += $detail->quantity; @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td>TOTAL</td><td class="text-right">{{ $total_qty }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 4%; border: none;"></td>
                <td style="width: 48%; border: none; vertical-align: top; padding: 0;">
                    <h4 style="margin-top: 0;">Roll Consumed</h4>
                    <table>
                        <thead>
                            <tr><th>Roll No</th><th class="text-right">Meters</th></tr>
                        </thead>
                        <tbody>
                            @foreach($data['rolls_data'] as $roll)
                                <tr>
                                    <td>{{ $roll->roll_no }}</td>
                                    <td class="text-right">{{ number_format($roll->meter, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td>TOTAL</td><td class="text-right">{{ number_format($data['rolls_data']->sum('meter'), 2) }} m</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Production Progress Tracking</div>
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
            
            <div class="stage-card st-{{ $status }}">
                <span class="status-pill sp-{{ $status }}">{{ $status }}</span>
                <div class="stage-title">{{ $stage->name }}</div>
                <div class="stage-unit">
                    Unit: {{ $status == 'waiting' ? 'Not Assigned' : ($d['unit_name'] ?? 'N/A') }}
                </div>
                
                <table class="metric-table">
                    <tr>
                        <td style="width: 23%;">
                            <span class="metric-label">Total</span>
                            <span class="metric-value">{{ number_format($total) }}</span>
                        </td>
                        <td style="width: 2%;"></td>
                        <td style="width: 23%;">
                            <span class="metric-label">Remaining</span>
                            <span class="metric-value" style="color: {{ $remaining > 0 ? '#dc2626' : '#16a34a' }}">{{ number_format($remaining) }}</span>
                        </td>
                        <td style="width: 2%;"></td>
                        <td style="width: 23%;">
                            <span class="metric-label">ETA</span>
                            <span class="metric-value">{{ $eta->format('d M, Y') }}</span>
                        </td>
                        <td style="width: 2%;"></td>
                        <td style="width: 23%;">
                            <span class="metric-label">Completed</span>
                            <span class="metric-value">{{ $completed ? $completed->format('d M, Y') : '-' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>
</body>
</html>
