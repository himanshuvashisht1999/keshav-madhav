<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Ledger Report</title>
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
        .filter-summary {
            width: 100%;
            margin-bottom: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 9.5px;
        }
        .filter-summary td {
            padding: 2px 6px;
        }
        .filter-label {
            font-weight: bold;
            color: #475569;
        }
        .total-box {
            width: 100%;
            margin-bottom: 15px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            padding: 8px 12px;
            text-align: right;
            border-radius: 4px;
        }
        .total-box .title {
            font-size: 10px;
            color: #4338ca;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 15px;
        }
        .total-box .amount {
            font-size: 15px;
            color: #1e3c72;
            font-weight: bold;
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
        .badge-type {
            font-size: 8px;
            background: #e2e8f0;
            padding: 1px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">SNAPKID</div>
                <div style="color: #64748b; font-size: 9.5px;">Sales Ledger Statement</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Sales Ledger</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="filter-summary">
        <tr>
            <td style="width: 25%;">
                <span class="filter-label">Party:</span> {{ $selectedParty ?? 'All Parties' }}
            </td>
            <td style="width: 25%;">
                <span class="filter-label">Dispatch Type:</span> {{ !empty($filters['item_type']) ? $filters['item_type'] : 'All Types' }}
            </td>
            <td style="width: 25%;">
                <span class="filter-label">Bill No:</span> {{ !empty($filters['bill_no']) ? $filters['bill_no'] : 'All' }}
            </td>
            <td style="width: 25%;">
                <span class="filter-label">Date Range:</span>
                @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                    {{ !empty($filters['from_date']) ? date('d-m-Y', strtotime($filters['from_date'])) : 'Start' }} to {{ !empty($filters['to_date']) ? date('d-m-Y', strtotime($filters['to_date'])) : 'Today' }}
                @else
                    All Dates
                @endif
            </td>
        </tr>
    </table>

    <div class="total-box">
        <span class="title">Total Grand Total:</span>
        <span class="amount">₹{{ number_format($totalGrandTotal, 2) }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 17%;">Dispatch ID / Bill</th>
                <th style="width: 28%;">Party Name</th>
                <th style="width: 15%;">Agent / Type</th>
                <th style="width: 15%;" class="text-right">Grand Total</th>
                <th style="width: 20%;">Date</th>
            </tr>
        </thead>
        <tbody>
            @php $sno = 1; @endphp
            @forelse($sales as $dispatch)
                <tr>
                    <td class="text-center">{{ $sno++ }}</td>
                    <td>
                        <strong>{{ $dispatch->dispatch_no }}</strong>
                        @if(!empty($dispatch->bill_no))
                            <br><span style="color: #64748b; font-size: 8px;">Bill: {{ $dispatch->bill_no }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $dispatch->party_name ?? 'N/A' }}
                        @if($dispatch->party_type === 'vendor')
                            <span class="badge-type">(Vendor)</span>
                        @elseif($dispatch->source_type === 'corporate')
                            <span class="badge-type">(Corporate)</span>
                        @endif
                    </td>
                    <td>{{ $dispatch->source_type === 'corporate' ? 'Corporate' : ($dispatch->agent_name ?? 'Direct') }}</td>
                    <td class="text-right font-weight-bold">₹{{ number_format($dispatch->grand_total, 2) }}</td>
                    <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #94a3b8;">No sales records found matching the criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right" style="font-size: 11px; color: #1e3c72;">₹{{ number_format($totalGrandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        SNAPKID ERP &bull; Sales Ledger Report &bull; Page printed automatically
    </div>
</body>
</html>
