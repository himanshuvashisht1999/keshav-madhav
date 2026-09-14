<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Lot Ledger - {{ $lot->lot_no }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 15px;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3c72;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3c72;
        }
        .report-title {
            font-size: 14px;
            text-align: right;
            text-transform: uppercase;
            color: #555;
            font-weight: bold;
        }
        .meta-info {
            font-size: 9px;
            color: #777;
            text-align: right;
            margin-top: 4px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 9.5px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #1e3c72;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
            border: 1px solid #1e3c72;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">SNAPKID</div>
                <div style="color: #64748b; font-size: 9.5px;">Lot Process & Movement Ledger</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Lot: {{ $lot->lot_no }}</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 33%;">
                <strong>Order SKU:</strong> {{ $lot->orderMain->sku ?? '-' }}<br>
                <strong>Customer:</strong> {{ $lot->orderMain->customer->name ?? '-' }}
            </td>
            <td style="width: 33%;">
                <strong>Fabric:</strong> {{ $lot->orderProductSet->fabric->name ?? '-' }}<br>
                <strong>Pattern:</strong> {{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}
            </td>
            <td style="width: 34%; text-align: right;">
                <strong>Current Stage:</strong> {{ $lot->last_current_stage ?? 'N/A' }}<br>
                <strong>Initial Qty:</strong> {{ number_format($initialQty) }} Pcs
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Date & Time</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 45%;">Particulars</th>
                <th style="width: 10%; text-align: right;">Quantity</th>
                <th style="width: 10%; text-align: right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                @php
                    $qty = 0;
                    if ($tx->type === 'Inward') {
                        $qty = (float)($tx->inward ?? 0);
                    } elseif ($tx->type === 'Outward') {
                        $qty = (float)($tx->outward ?? 0);
                    } else {
                        $qty = (float)($tx->process_qty ?? 0);
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') }}</td>
                    <td>
                        @if(isset($tx->status))
                            @if($tx->status == 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($tx->status == 'progress')
                                <span class="badge badge-warning">Progress</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($tx->status) }}</span>
                            @endif
                        @else
                            @if($tx->type == 'Inward')
                                <span class="badge badge-info">Assigned</span>
                            @elseif($tx->type == 'Outward')
                                <span class="badge badge-success">Completed</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($tx->type) }}</span>
                            @endif
                        @endif
                    </td>
                    <td>{{ $tx->particulars ?? '-' }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($qty) }}</td>
                    <td class="text-right font-weight-bold" style="color: #1e3c72;">{{ number_format($tx->running_balance ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px;">No transactions recorded for this lot.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | SNAPKID ERP
    </div>
</body>
</html>
