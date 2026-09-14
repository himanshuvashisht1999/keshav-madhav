<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Lot Production Ledger Summary</title>
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
                <div style="color: #64748b; font-size: 9.5px;">Lot Production Ledger Summary</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Lot Ledger List</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Lot No</th>
                <th style="width: 15%;">Order SKU</th>
                <th style="width: 25%;">Customer</th>
                <th style="width: 20%;">Fabric</th>
                <th style="width: 12%; text-align: right;">Total Qty</th>
                <th style="width: 13%;">Current Stage</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @forelse($lots as $lot)
                @php 
                    $qty = (float)($lot->lot_quantity ?? 0);
                    $totalQty += $qty;
                @endphp
                <tr>
                    <td class="font-weight-bold">{{ $lot->lot_no ?? '-' }}</td>
                    <td>{{ $lot->orderMain->sku ?? '-' }}</td>
                    <td>{{ $lot->orderMain->customer->name ?? '-' }}</td>
                    <td>{{ $lot->orderProductSet->fabric->name ?? '-' }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($qty) }}</td>
                    <td>{{ $lot->last_current_stage ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">No lots found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right font-weight-bold">Total:</td>
                <td class="text-right font-weight-bold" style="color: #1e3c72;">{{ number_format($totalQty) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | SNAPKID ERP
    </div>
</body>
</html>
