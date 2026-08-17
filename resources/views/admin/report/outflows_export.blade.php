<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outflows Report</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-left {
            text-align: left;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header-title">
        Outflows & Adjustments Report<br>
        Exported At: {{ $exportedAt->format('d-m-Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Log Date</th>
                <th>Slip ID</th>
                <th>Lot No</th>
                <th>Design Number</th>
                <th>Adjustment Type</th>
                <th>Size</th>
                <th>Color</th>
                <th>Quantity</th>
                <th>Responsible Unit</th>
                <th class="text-left">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($outflows as $out)
                @php $grandTotal += $out->quantity; @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d-m-Y', strtotime($out->created_at)) }}</td>
                    <td>#{{ $out->slip_id }}</td>
                    <td>LOT-{{ str_pad($out->lot_no, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $out->product->design_number ?? 'N/A' }}</td>
                    <td style="text-transform: uppercase;">{{ $out->type }}</td>
                    <td>{{ $out->size->size ?? 'N/A' }}</td>
                    <td>{{ $out->color->name ?? 'N/A' }}</td>
                    <td>{{ $out->quantity }}</td>
                    <td>{{ $out->responsibleUnit->name ?? 'N/A' }}</td>
                    <td class="text-left">{{ $out->remarks ?? '-' }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="8" style="text-align: right;">Grand Total:</td>
                <td>{{ $grandTotal }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
