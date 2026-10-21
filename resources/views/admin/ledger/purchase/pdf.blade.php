<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Ledger Report</title>
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
            padding-bottom: 10px;
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
        .filter-summary {
            width: 100%;
            margin-bottom: 15px;
            background: #f6faf1;
            border: 1px solid #dfe9da;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 9.5px;
        }
        .filter-summary td {
            padding: 2px 6px;
        }
        .filter-label {
            font-weight: bold;
            color: #0d5f2c;
        }
        .total-box {
            width: 100%;
            margin-bottom: 15px;
            background: #eef2ff;
            border: 1px solid #edf7e4;
            padding: 8px 12px;
            text-align: right;
            border-radius: 4px;
        }
        .total-box .title {
            font-size: 10px;
            color: #05421c;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 15px;
        }
        .total-box .amount {
            font-size: 15px;
            color: #05421c;
            font-weight: bold;
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
                <div style="color: #0d5f2c; font-size: 9.5px;">Purchase Ledger Statement</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Purchase Ledger</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="filter-summary">
        <tr>
            <td style="width: 20%;">
                <span class="filter-label">Report Type:</span>
                @if(($filters['report_type'] ?? 'both') === 'purchase')
                    Purchase Only
                @elseif(($filters['report_type'] ?? 'both') === 'return')
                    Purchase Return Only
                @else
                    Both (Purchase + Return)
                @endif
            </td>
            <td style="width: 20%;">
                <span class="filter-label">Vendor:</span> {{ $selectedVendor ?? 'All Vendors' }}
            </td>
            <td style="width: 20%;">
                <span class="filter-label">Purchase Agent:</span> {{ $selectedPurchaseAgent ?? 'All Agents' }}
            </td>
            <td style="width: 20%;">
                <span class="filter-label">Date Order:</span> {{ strtoupper($filters['sort_date'] ?? 'desc') }}
            </td>
            <td style="width: 20%;">
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
        <span class="title">Net Purchase Total:</span>
        <span class="amount">₹{{ number_format($netGrandTotal ?? $totalGrandTotal, 2) }}</span>
        @if(($filters['report_type'] ?? 'both') === 'both')
            <span style="font-size: 8.5px; color: #555; margin-left: 10px;">
                (Gross Purchases: ₹{{ number_format($totalPurchaseGrandTotal ?? 0, 2) }} | Returns: ₹{{ number_format($totalReturnGrandTotal ?? 0, 2) }})
            </span>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 10%;" class="text-center">Type</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 16%;">Bill / Return No.</th>
                <th style="width: 24%;">Vendor Name</th>
                <th style="width: 16%;">Purchase Agent</th>
                <th style="width: 17%;" class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php $sno = 1; @endphp
            @forelse($purchases as $item)
                @php $isReturn = ($item->entry_type ?? 'purchase') === 'return'; @endphp
                <tr style="{{ $isReturn ? 'background-color: #fff5f5;' : '' }}">
                    <td class="text-center">{{ $sno++ }}</td>
                    <td class="text-center font-weight-bold" style="{{ $isReturn ? 'color: #b91c1c;' : 'color: #166534;' }}">
                        {{ $isReturn ? 'Return' : 'Purchase' }}
                    </td>
                    <td>{{ $item->date ? date('d M Y', strtotime($item->date)) : 'N/A' }}</td>
                    <td><strong>{{ $item->invoice_no ?? 'N/A' }}</strong></td>
                    <td>{{ $item->vendor_name ?? 'N/A' }}</td>
                    <td>{{ $item->purchase_agent_name ?? 'Direct' }}</td>
                    <td class="text-right font-weight-bold" style="{{ $isReturn ? 'color: #dc2626;' : 'color: #05421c;' }}">
                        {{ $isReturn ? '- ' : '' }}₹{{ number_format($item->grand_total, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #8aa890;">No purchase or return records found matching the criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #dfe9da; font-weight: bold;">
                <td colspan="6" class="text-right">NET TOTAL:</td>
                <td class="text-right" style="font-size: 11px; color: #05421c;">₹{{ number_format($netGrandTotal ?? $totalGrandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        @include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID', 'height' => 10]) ERP &bull; Purchase Ledger Report &bull; Page printed automatically
    </div>
</body>
</html>
