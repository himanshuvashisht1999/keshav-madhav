<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assignment Details - CMPO {{ $header['id'] }}</title>
    <style>
        @page { margin: 1.2cm 1.5cm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f8fafc;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 8px 4px;
            vertical-align: top;
        }

        .label {
            font-size: 11px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }

        .status-completed { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-delayed { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .status-pending { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        
        .remark-box {
            margin-top: 15px;
            padding: 12px;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 6px;
            font-size: 12px;
            color: #856404;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header-title">
        Assignment Details - CMPO #{{ $header['id'] }}
    </div>

    @php
        $isCompleted = !empty($transaction->image) || (isset($transaction->is_closed_for_unit) && $transaction->is_closed_for_unit == 1);
        $endDate = $header['end_date'] ?? null;
        $isDelayed = !$isCompleted && $endDate && now()->startOfDay() > \Carbon\Carbon::parse($endDate)->startOfDay();
        
        if ($isCompleted) {
            $statusClass = 'status-completed';
            $statusText = 'Completed';
        } elseif ($isDelayed) {
            $statusClass = 'status-delayed';
            $statusText = 'Delayed';
        } else {
            $statusClass = 'status-pending';
            $statusText = 'Pending';
        }
    @endphp

    <div class="card">
        <div class="section-title">Production Info</div>
        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="label">CMPO No</div>
                    <div class="value">CMPO-{{ $header['id'] }}</div>
                </td>
                <td width="50%">
                    <div class="label">Total Pieces</div>
                    <div class="value" style="color: #4f46e5; font-size: 16px;">{{ $header['total_pcs'] ?? 0 }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Status</div>
                    <div class="status-badge {{ $statusClass }}">{{ $statusText }}</div>
                </td>
                <td>
                    <div class="label">Date</div>
                    <div class="value">{{ $header['date'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Design No</div>
                    <div class="value">{{ $header['design_no'] }}</div>
                </td>
                <td>
                    <div class="label">Lot No</div>
                    <div class="value" style="color: #2563eb;">{{ $header['lot_no'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Fabric</div>
                    <div class="value">{{ $header['fabric'] }}</div>
                </td>
                <td>
                    <div class="label">Color</div>
                    <div class="value">{{ $header['color'] }}</div>
                </td>
            </tr>
            
            @if($type === 'cutting')
                <tr>
                    <td colspan="2">
                        <div class="label">Warehouse (Cutting Master)</div>
                        <div class="value">{{ $header['warehouse'] }} ({{ $header['unit_name'] }})</div>
                    </td>
                </tr>
            @else
                <tr>
                    <td>
                        <div class="label">From Stage</div>
                        <div class="value">{{ $header['from_stage'] }}</div>
                    </td>
                    <td>
                        <div class="label">Sent By</div>
                        <div class="value">{{ $header['sent_by'] }}</div>
                    </td>
                </tr>
            @endif

            <tr>
                <td colspan="2">
                    <div class="label">Pattern & Fitting</div>
                    <div class="value">{{ $header['pattern'] }} | {{ $header['fitting'] }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="label">Belt</div>
                    <div class="value">{{ $header['belt'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if(isset($header['start_date']) || isset($header['end_date']))
    <div class="card">
        <div class="section-title">Timing Info</div>
        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="label">Start Date</div>
                    <div class="value">{{ $header['start_date'] ? date('d M Y, h:i A', strtotime($header['start_date'])) : '-' }}</div>
                </td>
                <td width="50%">
                    <div class="label">Expected End</div>
                    <div class="value" style="{{ (!$header['complete_date'] && now() > $header['end_date']) ? 'color: #dc2626;' : '' }}">
                        {{ $header['end_date'] ? date('d M Y, h:i A', strtotime($header['end_date'])) : '-' }}
                        @if(!$header['complete_date'] && now() > $header['end_date'])
                            <span style="font-size: 10px; font-weight: bold; text-transform: uppercase;">(Delayed)</span>
                        @endif
                    </div>
                </td>
            </tr>
            @if($header['complete_date'])
            <tr>
                <td colspan="2">
                    <div class="label">Completed At</div>
                    <div class="value" style="color: #16a34a;">{{ date('d M Y, h:i A', strtotime($header['complete_date'])) }}</div>
                </td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    @if($header['remark'] && $header['remark'] != '-')
        <div class="remark-box">
            <strong>Note:</strong> {{ $header['remark'] }}
        </div>
    @endif

    @if(isset($transaction->productionSlipDigitization->slip_file))
        @php
            $slipPath = public_path('assets/production_slips/' . $transaction->productionSlipDigitization->slip_file);
            $slipB64 = file_exists($slipPath) ? 'data:image/'.pathinfo($slipPath,PATHINFO_EXTENSION).';base64,'.base64_encode(file_get_contents($slipPath)) : null;
        @endphp
        @if($slipB64)
            <div class="card" style="margin-top: 20px; page-break-inside: avoid; text-align: center;">
                <div class="section-title">Previous Stage Slip</div>
                <img src="{{ $slipB64 }}" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
            </div>
        @endif
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | {{ $unit->name ?? 'Keshav Madhav' }}
    </div>

</body>
</html>
