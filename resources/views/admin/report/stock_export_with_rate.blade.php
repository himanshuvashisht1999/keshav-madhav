<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Stock With Rate</title>
</head>
<body>
    <h2>Fabric Stock Report (With Rate)</h2>
    <p><strong>Generated On:</strong> {{ $exportedAt->format('d M Y, h:i A') }}</p>

    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Fabric Name</th>
                <th>Remaining Qty (Meter)</th>
                <th>Rate (Price per meter)</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalQty = 0;
                $grandTotalValue = 0;
            @endphp
            @forelse($data as $index => $row)
                @php
                    $qty = $row->total_remaining;
                    $rate = $row->price_per_meter ?? 0;
                    $value = $qty * $rate;
                    
                    $grandTotalQty += $qty;
                    $grandTotalValue += $value;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->fabric->name ?? 'N/A' }}</td>
                    <td>{{ number_format($qty, 2, '.', '') }}</td>
                    <td>{{ number_format($rate, 2, '.', '') }}</td>
                    <td>{{ number_format($value, 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No stock found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align:right">Grand Total:</th>
                <th>{{ number_format($grandTotalQty, 2, '.', '') }}</th>
                <th></th>
                <th>{{ number_format($grandTotalValue, 2, '.', '') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
