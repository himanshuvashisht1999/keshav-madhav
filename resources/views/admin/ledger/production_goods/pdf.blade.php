<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ledger: {{ $good->design_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
        .ledger-title {
            font-size: 16px;
            text-align: right;
            text-transform: uppercase;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
            padding: 5px 0;
        }
        .balance-summary {
            width: 100%;
            margin-bottom: 20px;
            background: #f8f8f8;
            border: 1px solid #ddd;
        }
        .balance-summary td {
            padding: 10px;
            text-align: center;
            width: 50%;
        }
        .balance-summary .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #777;
            display: block;
            margin-bottom: 5px;
        }
        .balance-summary .value {
            font-size: 18px;
            font-weight: bold;
            color: #1e3c72;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ledger-table th {
            background: #333;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .ledger-table td {
            border-bottom: 1px solid #eee;
            padding: 8px;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #007bff; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">SNAPKID</div>
                <div style="font-size: 10px; color: #666; margin-top: 5px;">Production Goods Ledger</div>
            </td>
            <td class="ledger-title">
                Item Ledger
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Design Number:</strong> {{ $good->design_number }}<br>
                <strong>Series:</strong> {{ $good->series?->name ?? '-' }}<br>
                <strong>Garment:</strong> {{ $good->name_of_garment ?? '-' }}<br>
                <strong>Size Set:</strong> {{ $sizeSet->name ?? '-' }}
                @if($warehouses->count()) <br><strong>Warehouses:</strong> {{ $warehouses->pluck('name')->implode(', ') }} @endif
            </td>
            <td width="40%" class="text-right">
                <strong>Generated On:</strong> {{ date('d-M-Y H:i') }}<br>
                @if($startDate || $endDate)
                    <strong>Period:</strong> {{ $startDate ? date('d-M-Y', strtotime($startDate)) : 'Start' }} to {{ $endDate ? date('d-M-Y', strtotime($endDate)) : 'End' }}
                @endif
            </td>
        </tr>
    </table>

    @php
        $totalInward = $transactions->sum('inward');
        $totalOutward = $transactions->sum('outward');
        $closingBalance = $transactions->last()->running_balance ?? $openingBalanceAmount;
    @endphp

    <table class="balance-summary">
        <tr>
            <td style="border-right: 1px solid #ddd;">
                <span class="label">Opening Balance</span>
                <span class="value">{{ number_format($openingBalanceAmount, 0) }} Boxes</span>
            </td>
            <td>
                <span class="label">Closing Balance</span>
                <span class="value">{{ number_format($closingBalance, 0) }} Boxes</span>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="10%">Type</th>
                <th width="20%">Particulars</th>
                <th>Remarks</th>
                <th width="12%" class="text-right">Inward</th>
                <th width="12%" class="text-right">Outward</th>
                <th width="15%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @if($startDate)
            <tr>
                <td colspan="4" class="text-right text-bold">Opening Balance</td>
                <td class="text-right"></td>
                <td class="text-right"></td>
                <td class="text-right text-bold">{{ number_format($openingBalanceAmount, 0) }}</td>
            </tr>
            @endif

            @forelse($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d-M-y') }}</td>
                    <td>{{ $tx->type }}</td>
                    <td>{{ $tx->particulars }}</td>
                    <td>
                        {{ str_replace('Order No:', 'Ord:', $tx->remarks ?? '-') }}
                    </td>
                    <td class="text-right text-success">
                        {{ $tx->inward > 0 ? number_format($tx->inward, 0) : '-' }}
                    </td>
                    <td class="text-right text-danger">
                        {{ $tx->outward > 0 ? number_format($tx->outward, 0) : '-' }}
                    </td>
                    <td class="text-right text-bold text-primary">
                        {{ number_format($tx->running_balance, 0) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0;">
                <td colspan="4" class="text-right text-bold" style="padding: 10px;">TOTALS:</td>
                <td class="text-right text-bold text-success" style="padding: 10px;">{{ number_format($totalInward, 0) }}</td>
                <td class="text-right text-bold text-danger" style="padding: 10px;">{{ number_format($totalOutward, 0) }}</td>
                <td class="text-right text-bold text-primary" style="padding: 10px;">
                    {{ number_format($closingBalance, 0) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a computer generated document. | SNAPKID</p>
    </div>
</body>
</html>
