<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $party->name }} - Ledger</title>
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
                <div style="font-size: 10px; color: #666; margin-top: 5px;">Management System Ledger</div>
            </td>
            <td class="ledger-title">
                Account Ledger
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>{{ $party->name }}</strong><br>
                {{ $party->address ?? 'No Address' }}<br>
                Phone: {{ $party->phone ?? '-' }}
            </td>
            <td width="40%" class="text-right">
                <strong>Generated On:</strong> {{ date('d-M-Y H:i') }}<br>
                <strong>Type:</strong> {{ ucfirst($type) }}<br>
                @if($startDate || $endDate)
                    <strong>Period:</strong> {{ $startDate ? date('d-M-Y', strtotime($startDate)) : 'Start' }} to {{ $endDate ? date('d-M-Y', strtotime($endDate)) : 'End' }}
                @endif
            </td>
        </tr>
    </table>

    @if(isset($viewMode) && $viewMode === 'party_wise' && isset($groupedLedgers))
        @forelse($groupedLedgers as $ledger)
            <div style="margin-top: 30px; margin-bottom: 10px; font-size: 14px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #1e3c72;">
                Customer: {{ $ledger->shop->name }}
            </div>
            <table class="balance-summary">
                <tr>
                    <td style="border-right: 1px solid #ddd;">
                        <span class="label">Opening Balance</span>
                        <span class="value">₹ {{ number_format(abs($ledger->opening_balance), 2) }} {{ $ledger->opening_balance >= 0 ? 'DR' : 'CR' }}</span>
                    </td>
                    <td>
                        <span class="label">Closing Balance</span>
                        <span class="value">₹ {{ number_format(abs($ledger->closing_balance), 2) }} 
                            {{ $ledger->closing_balance >= 0 ? 'DR' : 'CR' }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="ledger-table">
                <thead>
                    <tr>
                        <th width="10%">Date</th>
                        <th width="12%">Type</th>
                        <th width="15%">Ref</th>
                        <th>Description</th>
                        <th width="12%" class="text-right">Debit</th>
                        <th width="12%" class="text-right">Credit</th>
                        <th width="15%" class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentBalance = $ledger->opening_balance; @endphp
                    @forelse($ledger->transactions as $tx)
                        @php $currentBalance = $tx->running_balance; @endphp
                        <tr>
                            <td>{{ date('d-M-y', strtotime($tx->date)) }}</td>
                            <td>{{ $tx->type }}</td>
                            <td>{{ $tx->ref }}</td>
                            <td>{{ $tx->description }}</td>
                            <td class="text-right">
                                {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                            </td>
                            <td class="text-right text-bold">
                                ₹ {{ number_format(abs($currentBalance), 2) }} {{ $currentBalance >= 0 ? 'DR' : 'CR' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!$ledger->transactions->isEmpty())
                <tfoot>
                    <tr style="background: #f0f0f0;">
                        <td colspan="4" class="text-right text-bold" style="padding: 10px;">TOTALS:</td>
                        <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format($ledger->transactions->sum('debit'), 2) }}</td>
                        <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format($ledger->transactions->sum('credit'), 2) }}</td>
                        <td class="text-right text-bold" style="padding: 10px; color: #1e3c72;">
                            ₹ {{ number_format(abs($currentBalance), 2) }} {{ $currentBalance >= 0 ? 'DR' : 'CR' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @empty
            <div style="text-align: center; padding: 20px;">No customers found.</div>
        @endforelse
    @else
    <table class="balance-summary">
        <tr>
            <td style="border-right: 1px solid #ddd;">
                <span class="label">Opening Balance</span>
                <span class="value">₹ {{ number_format(abs($openingBalAmount), 2) }} {{ $openingBalAmount >= 0 ? 'DR' : 'CR' }}</span>
            </td>
            <td>
                <span class="label">Closing Balance</span>
                <span class="value">₹ {{ number_format(abs($party->balance), 2) }} 
                    @if($type === 'customer')
                        {{ $party->balance <= 0 ? 'DR' : 'CR' }}
                    @else
                        {{ $party->balance >= 0 ? 'CR' : 'DR' }}
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="12%">Type</th>
                <th width="15%">Ref</th>
                <th>Description</th>
                <th width="12%" class="text-right">Debit</th>
                <th width="12%" class="text-right">Credit</th>
                <th width="15%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = 0; @endphp
            @forelse($transactions as $tx)
                @php $currentBalance = $tx->running_balance; @endphp
                <tr>
                    <td>{{ date('d-M-y', strtotime($tx->date)) }}</td>
                    <td>{{ $tx->type }}</td>
                    <td>{{ $tx->ref }}</td>
                    <td>{{ $tx->description }}</td>
                    <td class="text-right">
                        {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                    </td>
                    <td class="text-right text-bold">
                        ₹ {{ number_format(abs($currentBalance), 2) }} {{ $currentBalance >= 0 ? 'DR' : 'CR' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(!$transactions->isEmpty())
        <tfoot>
            <tr style="background: #f0f0f0;">
                <td colspan="4" class="text-right text-bold" style="padding: 10px;">TOTALS:</td>
                <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format($transactions->sum('debit'), 2) }}</td>
                <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format($transactions->sum('credit'), 2) }}</td>
                <td class="text-right text-bold" style="padding: 10px; color: #1e3c72;">
                    ₹ {{ number_format(abs($currentBalance), 2) }} {{ $currentBalance >= 0 ? 'DR' : 'CR' }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
    @endif

    <div class="footer">
        <p>This is a computer generated document. | SNAPKID</p>
    </div>
</body>
</html>