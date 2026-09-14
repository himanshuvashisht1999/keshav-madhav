<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fabric Ledger - {{ $fabric->name }}</title>
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
            padding-bottom: 10px;
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
                <div style="color: #64748b; font-size: 9.5px;">Fabric Stock Ledger</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Fabric: {{ $fabric->name }}</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Fabric:</strong> {{ $fabric->name }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Period:</strong> {{ $startDate ? date('d-M-Y', strtotime($startDate)) : 'Start' }} to {{ $endDate ? date('d-M-Y', strtotime($endDate)) : 'Today' }}
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 25%;">Particulars</th>
                <th style="width: 20%;">Party / Dept</th>
                <th style="width: 11%;" class="text-right">Inward (Mtr)</th>
                <th style="width: 11%;" class="text-right">Outward (Mtr)</th>
                <th style="width: 11%;" class="text-right">Balance (Mtr)</th>
            </tr>
        </thead>
        <tbody>
            @if($startDate)
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td>{{ date('d M Y', strtotime($startDate)) }}</td>
                    <td>B/F</td>
                    <td colspan="4">Opening Balance Brought Forward</td>
                    <td class="text-right">{{ number_format($openingBalanceAmount, 2) }}</td>
                </tr>
            @endif
            @php $totalIn = 0; $totalOut = 0; @endphp
            @forelse($transactions as $tx)
                @php 
                    $totalIn += $tx->inward; 
                    $totalOut += $tx->outward; 
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') }}</td>
                    <td>
                        <span style="font-weight: bold; color: {{ $tx->type == 'Inward' ? '#16a34a' : '#dc2626' }};">
                            {{ $tx->type }}
                        </span>
                    </td>
                    <td>{{ $tx->particulars }}</td>
                    <td>{{ $tx->party }}</td>
                    <td class="text-right" style="color: #16a34a;">{{ $tx->inward > 0 ? number_format($tx->inward, 2) : '-' }}</td>
                    <td class="text-right" style="color: #dc2626;">{{ $tx->outward > 0 ? number_format($tx->outward, 2) : '-' }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($tx->running_balance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right" style="color: #16a34a;">{{ number_format($totalIn, 2) }}</td>
                <td class="text-right" style="color: #dc2626;">{{ number_format($totalOut, 2) }}</td>
                <td class="text-right" style="color: #1e3c72;">{{ number_format($transactions->last()->running_balance ?? $openingBalanceAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        SNAPKID ERP &bull; Fabric Ledger Report &bull; Page printed automatically
    </div>
</body>
</html>
