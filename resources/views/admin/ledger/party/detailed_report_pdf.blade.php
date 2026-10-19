<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detailed Party Ledger Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 10px;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #05421c;
            margin-bottom: 10px;
            padding-bottom: 6px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #05421c;
        }
        .report-title {
            font-size: 12px;
            text-align: right;
            text-transform: uppercase;
            color: #333;
            font-weight: bold;
        }
        .meta-info {
            font-size: 8.5px;
            color: #666;
            text-align: right;
            margin-top: 3px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #d1d5db;
        }
        .summary-table td {
            padding: 6px 10px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .summary-table td:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .summary-val {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #05421c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            font-size: 8.5px;
            border: 1px solid #05421c;
        }
        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            font-size: 8.5px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .party-header-row td {
            background-color: #e8f5e9 !important;
            font-weight: bold;
            color: #05421c;
            padding: 6px;
            font-size: 9.5px;
            border: 1px solid #c8e6c9;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .badge-cr { color: #15803d; font-weight: bold; }
        .badge-dr { color: #dc2626; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #8aa890;
            text-align: center;
            border-top: 1px solid #dfe9da;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="company-name">SNAPKID</div>
                <div style="font-size: 8.5px; color: #555; margin-top: 2px;">
                    Detailed Financial Ledger Report
                    @if(!empty($typeId))
                        - {{ strtoupper($typeId === 'sales_agent' ? 'Sales Agent' : (isset($masters) && $masters->firstWhere('id', $typeId) ? $masters->firstWhere('id', $typeId)->name : str_replace('_', ' ', $typeId))) }}
                    @endif
                </div>
            </td>
            <td style="width: 50%;" class="text-right">
                <div class="report-title">
                    {{ $viewMode === 'party_wise' ? 'Party-wise (Grouped)' : 'Mix (Consolidated)' }} Statement
                </div>
                <div class="meta-info">
                    Period: 
                    @if($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @elseif($startDate)
                        From {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    @elseif($endDate)
                        Up to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else
                        All Dates
                    @endif
                    | Generated: {{ date('d M Y, h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Top Summary Metrics Strip -->
    <table class="summary-table">
        <tr>
            <td style="width: 20%;">
                <div class="summary-label">Parties Included</div>
                <div class="summary-val">{{ $filteredParties->count() }}</div>
            </td>
            <td style="width: 20%;">
                <div class="summary-label">Opening Balance</div>
                <div class="summary-val">
                    ₹ {{ number_format(abs($totalOpeningBalance), 2) }} 
                    <small class="{{ $totalOpeningBalance >= 0 ? 'badge-cr' : 'badge-dr' }}">{{ $totalOpeningBalance >= 0 ? 'CR' : 'DR' }}</small>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="summary-label">Period Debit (DR)</div>
                <div class="summary-val badge-dr">₹ {{ number_format($totalPeriodDebit, 2) }}</div>
            </td>
            <td style="width: 20%;">
                <div class="summary-label">Period Credit (CR)</div>
                <div class="summary-val badge-cr">₹ {{ number_format($totalPeriodCredit, 2) }}</div>
            </td>
            <td style="width: 20%;">
                <div class="summary-label">Closing Balance</div>
                <div class="summary-val">
                    ₹ {{ number_format(abs($totalClosingBalance), 2) }} 
                    <small class="{{ $totalClosingBalance >= 0 ? 'badge-cr' : 'badge-dr' }}">{{ $totalClosingBalance >= 0 ? 'CR' : 'DR' }}</small>
                </div>
            </td>
        </tr>
    </table>

    @if($viewMode === 'party_wise')
        @foreach($detailedParties as $dp)
            <table class="data-table">
                <tr class="party-header-row">
                    <td colspan="8">
                        {{ $dp->party->name }} ({{ ucfirst($dp->party->party_type) }})
                        &nbsp;|&nbsp; Phone: {{ $dp->party->phone ?? '-' }}
                        &nbsp;|&nbsp; Opening: ₹ {{ number_format(abs($dp->opening_balance), 2) }} {{ $dp->opening_balance >= 0 ? 'CR' : 'DR' }}
                        &nbsp;|&nbsp; Closing: ₹ {{ number_format(abs($dp->closing_balance), 2) }} {{ $dp->closing_balance >= 0 ? 'CR' : 'DR' }}
                    </td>
                </tr>
                <thead>
                    <tr>
                        <th style="width: 75px;">Date</th>
                        <th style="width: 80px;" class="text-center">Type</th>
                        <th style="width: 90px;">Reference</th>
                        <th style="width: 80px;">Bill No</th>
                        <th>Particulars / Description</th>
                        <th class="text-right" style="width: 85px;">Debit (₹)</th>
                        <th class="text-right" style="width: 85px;">Credit (₹)</th>
                        <th class="text-right" style="width: 95px;">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pBal = $dp->opening_balance; @endphp
                    @if($startDate)
                    <tr style="background: #eef7ee; font-style: italic;">
                        <td>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                        <td class="text-center">OPENING</td>
                        <td>-</td>
                        <td>-</td>
                        <td>Opening Balance Brought Forward</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right font-weight-bold">
                            ₹ {{ number_format(abs($dp->opening_balance), 2) }} {{ $dp->opening_balance >= 0 ? 'CR' : 'DR' }}
                        </td>
                    </tr>
                    @endif
                    @forelse($dp->transactions as $tx)
                        @php $pBal = $tx->running_balance ?? $pBal; @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                            <td class="text-center">{{ $tx->type }}</td>
                            <td>{{ $tx->ref }}</td>
                            <td>{{ $tx->bill_no ?? '-' }}</td>
                            <td>{{ $tx->description }}</td>
                            <td class="text-right {{ $tx->debit > 0 ? 'badge-dr' : '' }}">
                                {{ $tx->debit > 0 ? number_format($tx->debit, 2) : '-' }}
                            </td>
                            <td class="text-right {{ $tx->credit > 0 ? 'badge-cr' : '' }}">
                                {{ $tx->credit > 0 ? number_format($tx->credit, 2) : '-' }}
                            </td>
                            <td class="text-right font-weight-bold">
                                {{ number_format(abs($pBal), 2) }} {{ $pBal >= 0 ? 'CR' : 'DR' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-2 text-muted">No transactions in selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: bold;">
                        <td colspan="5" class="text-right">TOTAL:</td>
                        <td class="text-right badge-dr">₹ {{ number_format($dp->period_debit, 2) }}</td>
                        <td class="text-right badge-cr">₹ {{ number_format($dp->period_credit, 2) }}</td>
                        <td class="text-right">₹ {{ number_format(abs($dp->closing_balance), 2) }} {{ $dp->closing_balance >= 0 ? 'CR' : 'DR' }}</td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
    @else
        <!-- Mix Mode Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Date</th>
                    <th style="width: 120px;">Party Name</th>
                    <th style="width: 75px;" class="text-center">Type</th>
                    <th style="width: 85px;">Reference</th>
                    <th style="width: 75px;">Bill No</th>
                    <th>Particulars / Description</th>
                    <th class="text-right" style="width: 80px;">Debit (₹)</th>
                    <th class="text-right" style="width: 80px;">Credit (₹)</th>
                    <th class="text-right" style="width: 90px;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php $curBal = $totalOpeningBalance; @endphp
                @if($startDate)
                <tr style="background: #eef7ee; font-style: italic;">
                    <td>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                    <td>All Filtered Parties</td>
                    <td class="text-center">OPENING</td>
                    <td>-</td>
                    <td>-</td>
                    <td>Opening Balance Brought Forward</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right font-weight-bold">
                        ₹ {{ number_format(abs($totalOpeningBalance), 2) }} {{ $totalOpeningBalance >= 0 ? 'CR' : 'DR' }}
                    </td>
                </tr>
                @endif
                @forelse($allMixTransactions as $tx)
                    @php $curBal = $tx->running_balance ?? $curBal; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                        <td>{{ $tx->party_name ?? '-' }}</td>
                        <td class="text-center">{{ $tx->type }}</td>
                        <td>{{ $tx->ref }}</td>
                        <td>{{ $tx->bill_no ?? '-' }}</td>
                        <td>{{ $tx->description }}</td>
                        <td class="text-right {{ $tx->debit > 0 ? 'badge-dr' : '' }}">
                            {{ $tx->debit > 0 ? number_format($tx->debit, 2) : '-' }}
                        </td>
                        <td class="text-right {{ $tx->credit > 0 ? 'badge-cr' : '' }}">
                            {{ $tx->credit > 0 ? number_format($tx->credit, 2) : '-' }}
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format(abs($curBal), 2) }} {{ $curBal >= 0 ? 'CR' : 'DR' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-2 text-muted">No transactions in selected period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #e2e8f0; font-weight: bold;">
                    <td colspan="6" class="text-right">GRAND TOTALS:</td>
                    <td class="text-right badge-dr">₹ {{ number_format($totalPeriodDebit, 2) }}</td>
                    <td class="text-right badge-cr">₹ {{ number_format($totalPeriodCredit, 2) }}</td>
                    <td class="text-right">₹ {{ number_format(abs($totalClosingBalance), 2) }} {{ $totalClosingBalance >= 0 ? 'CR' : 'DR' }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        SNAPKID ERP &bull; Detailed Party Ledger Statement &bull; Page printed on {{ date('d M Y, h:i A') }}
    </div>
</body>
</html>
