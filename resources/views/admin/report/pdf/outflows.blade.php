<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outflows Report PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-left {
            text-align: left;
        }
        .header-container {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <table style="width: 100%; border: none; margin: 0; padding: 0;">
        <tr style="border: none; background: none;">
            <td style="text-align: left; border: none; padding: 0; width: 60%;">
                <div class="header-title">Outflows & Adjustments Report</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 3px;">Track debits, sampling allocations, and dead/damage logs</div>
            </td>
            <td style="text-align: right; border: none; padding: 0; width: 40%; vertical-align: bottom;">
                <div class="header-meta">
                    Generated At: {{ now()->format('d M Y') }}<br>
                    Grand Total Quantity: <strong>{{ number_format($totalQuantity) }} pcs</strong>
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 1px; background: #0f172a; margin-top: 8px; margin-bottom: 15px;"></div>

    <table>
        <thead>
            <tr>
                <th width="6%">Sr No</th>
                <th width="12%">Log Date</th>
                <th width="8%">Slip ID</th>
                <th width="10%">Lot No</th>
                <th width="12%">Design Number</th>
                <th width="10%">Type</th>
                <th width="14%">Size & Color</th>
                <th width="10%">Quantity</th>
                <th width="12%">Responsible Unit</th>
                <th class="text-left" width="16%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($outflows as $out)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d-m-Y', strtotime($out->created_at)) }}</td>
                    <td>#{{ $out->slip_id }}</td>
                    <td>LOT-{{ str_pad($out->lot_no, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong>{{ $out->product->design_number ?? 'N/A' }}</strong>
                        <span style="display: block; font-size: 8px; color: #64748b;">{{ $out->product->name ?? 'Garment' }}</span>
                    </td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $out->type }}</td>
                    <td>
                        Size: {{ $out->size->size ?? 'N/A' }}<br>
                        Color: {{ $out->color->name ?? 'N/A' }}
                    </td>
                    <td style="font-weight: bold;">{{ $out->quantity }} pcs</td>
                    <td>{{ $out->responsibleUnit->name ?? 'N/A' }}</td>
                    <td class="text-left" style="font-size: 9px; word-break: break-all;">{{ $out->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #64748b;">No Outflow Adjustments Found</td>
                </tr>
            @endforelse
            @if($outflows->count() > 0)
                <tr style="font-weight: bold; background-color: #f1f5f9;">
                    <td colspan="7" style="text-align: right; border-right: none;">Grand Total:</td>
                    <td style="border-left: none; border-right: none;">{{ number_format($totalQuantity) }} pcs</td>
                    <td colspan="2" style="border-left: none;"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
