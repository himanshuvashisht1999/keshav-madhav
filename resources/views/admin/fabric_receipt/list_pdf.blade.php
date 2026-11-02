<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fabric Shipments</title>
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
            font-size: 9px;
            border: 1px solid #05421c;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #c8d8c4;
            font-size: 8.5px;
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
                <div style="color: #0d5f2c; font-size: 9.5px;">Fabric Shipments</div>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div class="report-title">Fabric Shipment List</div>
                <div class="meta-info">Generated: {{ date('d M Y, h:i A') }}</div>
                @if($filters)
                    <div class="meta-info">{{ $filters }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">#</th>
                <th style="width: 15%;">Shipment No.</th>
                <th style="width: 15%;">Bill No.</th>
                <th style="width: 23%;">Vendor</th>
                <th style="width: 10%;">Warehouse</th>
                <th style="width: 11%; text-align: center;">Date</th>
                <th style="width: 6%; text-align: right;">Rolls</th>
                <th style="width: 16%; text-align: right;">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totRolls = 0;
                $totAmount = 0;
            @endphp
            @forelse($rows as $idx => $r)
                @php
                    $totRolls += (int) $r->roll;
                    $totAmount += (float) $r->total_amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold" style="white-space: nowrap;">{{ $r->shipment_id }}</td>
                    <td style="white-space: nowrap;">{{ $r->bill_no }}</td>
                    <td>{{ $r->vendor->name ?? 'N/A' }}</td>
                    <td>{{ $r->warehouse_name }}</td>
                    <td class="text-center">{{ $r->time ? getformatDate($r->time) : '' }}</td>
                    <td class="text-right">{{ number_format((int) $r->roll) }}</td>
                    <td class="text-right font-weight-bold">{{ getIndianCurrency((float) $r->total_amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">No shipments found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f8e7; font-weight: bold;">
                <td colspan="6" class="text-right font-weight-bold">TOTAL:</td>
                <td class="text-right font-weight-bold">{{ number_format($totRolls) }}</td>
                <td class="text-right font-weight-bold" style="color: #05421c;">{{ getIndianCurrency($totAmount) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | @include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID', 'height' => 10]) ERP
    </div>
</body>
</html>
