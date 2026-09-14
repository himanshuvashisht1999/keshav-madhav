<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fabric Stock Ledger Summary</title>
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
                <div style="color: #64748b; font-size: 9.5px;">Fabric Stock Ledger Summary</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Fabric Ledger List</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 35%;">Fabric Name</th>
                <th style="width: 20%; text-align: right;">Total Inward (Mtr)</th>
                <th style="width: 20%; text-align: right;">Total Outward (Mtr)</th>
                <th style="width: 20%; text-align: right;">Current Balance (Mtr)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totIn = 0;
                $totOut = 0;
                $totBal = 0;
            @endphp
            @forelse($fabrics as $idx => $f)
                @php 
                    $in = (float)$f->total_inward;
                    $out = (float)$f->total_outward;
                    $bal = (float)$f->current_balance;
                    $totIn += $in;
                    $totOut += $out;
                    $totBal += $bal;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ $f->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($in, 2) }}</td>
                    <td class="text-right">{{ number_format($out, 2) }}</td>
                    <td class="text-right font-weight-bold" style="color: #1e3c72;">{{ number_format($bal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px;">No fabrics found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="text-right font-weight-bold">Total:</td>
                <td class="text-right font-weight-bold">{{ number_format($totIn, 2) }}</td>
                <td class="text-right font-weight-bold">{{ number_format($totOut, 2) }}</td>
                <td class="text-right font-weight-bold" style="color: #1e3c72;">{{ number_format($totBal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | SNAPKID ERP
    </div>
</body>
</html>
