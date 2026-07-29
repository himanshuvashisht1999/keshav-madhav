<!DOCTYPE html>
<html>

<head>
    <title>Stage Wise Pending Stock Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .meta {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .footer-total {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Stage Wise Pending Stock Report</h2>
        <div class="meta">Generated on: {{ now()->format('d M Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Stage</th>
                <th>Unit Person Name</th>
                <th>Lot No</th>
                <th>Design No</th>
                <th>Size Set</th>
                <th>Pending Quantity</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalPending = 0;
            @endphp
            @foreach($assignments as $item)
                @php 
                    $qty = $item->pending_qty ?? $item->quantity ?? 0;
                    $totalPending += $qty;
                    $stageName = $item->to_stage->name ?? $item->from_stage->name ?? 'Cutting';
                    $lotNo = $item->lot_no ?? ($item->productSet->lot_no ?? '-');
                    if(isset($type) && $type == 'cutting') {
                        $stageName = 'Cutting';
                        $lotNo = $item->lot_no ?? '-';
                    }
                    $unitPersonName = $item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-';
                @endphp
                <tr>
                    <td>{{ $stageName }}</td>
                    <td>{{ $unitPersonName }}</td>
                    <td>{{ $lotNo }}</td>
                    <td>{{ $item->design_number ?? '-' }}</td>
                    <td>{{ $item->size_set_name ?? '-' }}</td>
                    <td>{{ number_format($qty) }} Pcs</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td colspan="5" style="text-align: right;">Grand Total:</td>
                <td>{{ number_format($totalPending) }} Pcs</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
