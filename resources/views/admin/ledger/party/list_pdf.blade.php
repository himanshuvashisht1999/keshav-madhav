<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Party Financial Ledger Summary</title>
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
            border-bottom: 2px solid #05421c;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #05421c;
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
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #05421c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
            border: 1px solid #05421c;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #c8d8c4;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f6faf1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .badge-cr { color: #15803d; font-weight: bold; }
        .badge-dr { color: #d32f2f; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8.5px;
            color: #8aa890;
            text-align: center;
            border-top: 1px solid #dfe9da;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">@include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID'])</div>
                <div style="color: #0d5f2c; font-size: 9.5px;">Party Financial Ledger Summary</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Party Ledger List</div>
                <div class="meta-info">
                    Period:
                    @if($startDate && $endDate) {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @elseif($startDate) From {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    @elseif($endDate) Up to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else All dates
                    @endif
                    @if(request('min_closing')) | Min Closing: ₹ {{ number_format((float)request('min_closing'), 2) }} @endif
                    @if(request('max_closing')) | Max Closing: ₹ {{ number_format((float)request('max_closing'), 2) }} @endif
                    @if(request('balance_type')) | Balance: {{ strtoupper(request('balance_type')) }} @endif
                    @if(!empty($customerType)) | Customer Type: {{ ucfirst($customerType) }} @endif
                    @if(!empty($vendorType)) | Vendor Type: {{ $vendorType === 'direct' ? 'Direct' : ($vendorType === 'agent' ? 'Fabric Purchase Agent' : ucfirst($vendorType)) }} @endif
                </div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">#</th>
                <th style="width: 24%;">Party Name</th>
                <th style="width: 11%;">Type</th>
                <th style="width: 11%;">Phone</th>
                <th style="width: 13%; text-align: right;">Opening (₹)</th>
                <th style="width: 12%; text-align: right;">Debit (₹)</th>
                <th style="width: 12%; text-align: right;">Credit (₹)</th>
                <th style="width: 13%; text-align: right;">Closing (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalBalance = 0; $totalOpening = 0; $totalDebit = 0; $totalCredit = 0; @endphp
            @forelse($parties->values() as $idx => $p)
                @php
                    $bal = (float)$p->balance;
                    $open = (float)($p->opening_balance ?? 0);
                    $totalBalance += $bal;
                    $totalOpening += $open;
                    $totalDebit += (float)($p->period_debit ?? 0);
                    $totalCredit += (float)($p->period_credit ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ $p->name ?? '-' }}</td>
                    <td>{{ ucfirst($p->party_type ?? '-') }}</td>
                    <td>{{ $p->phone ?? '-' }}</td>
                    <td class="text-right {{ $open >= 0 ? 'badge-cr' : 'badge-dr' }}">
                        ₹ {{ number_format(abs($open), 2) }} {{ $open >= 0 ? 'CR' : 'DR' }}
                    </td>
                    <td class="text-right">₹ {{ number_format((float)($p->period_debit ?? 0), 2) }}</td>
                    <td class="text-right">₹ {{ number_format((float)($p->period_credit ?? 0), 2) }}</td>
                    <td class="text-right {{ $bal >= 0 ? 'badge-cr' : 'badge-dr' }}">
                        ₹ {{ number_format(abs($bal), 2) }} {{ $bal >= 0 ? 'CR' : 'DR' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">No parties found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f8e7; font-weight: bold;">
                <td colspan="4" class="text-right font-weight-bold">Total:</td>
                <td class="text-right font-weight-bold {{ $totalOpening >= 0 ? 'badge-cr' : 'badge-dr' }}">
                    ₹ {{ number_format(abs($totalOpening), 2) }} {{ $totalOpening >= 0 ? 'CR' : 'DR' }}
                </td>
                <td class="text-right font-weight-bold">₹ {{ number_format($totalDebit, 2) }}</td>
                <td class="text-right font-weight-bold">₹ {{ number_format($totalCredit, 2) }}</td>
                <td class="text-right font-weight-bold {{ $totalBalance >= 0 ? 'badge-cr' : 'badge-dr' }}">
                    ₹ {{ number_format(abs($totalBalance), 2) }} {{ $totalBalance >= 0 ? 'CR' : 'DR' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | @include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID', 'height' => 10]) ERP
    </div>
</body>
</html>
