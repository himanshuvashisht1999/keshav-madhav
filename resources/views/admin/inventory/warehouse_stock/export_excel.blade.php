<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warehouse Stock Export</title>
</head>
<body>
    <h2>Warehouse Stock Report</h2>
    <p><strong>Generated On:</strong> {{ $exportedAt->format('d M Y, h:i A') }}</p>
    
    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Design No.</th>
                <th>Size Set</th>
                <th>Location (Warehouse / Rack)</th>
                <th>Total Boxes</th>
                <th>Total Quantity (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalBoxes = 0;
                $grandTotalQty = 0;
            @endphp
            @forelse($data as $index => $row)
                @php
                    $qty = $row->total_boxes * $row->quantity;
                    $grandTotalBoxes += $row->total_boxes;
                    $grandTotalQty += $qty;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->product->name_of_garment ?? 'N/A' }}</td>
                    <td>{{ $row->product->design_number ?? 'N/A' }}</td>
                    <td>{{ $row->sizeSet->name ?? 'N/A' }}</td>
                    <td>
                        {{ $row->rack->storeroom->name ?? 'N/A' }} / {{ $row->rack->name ?? 'N/A' }}
                    </td>
                    <td>{{ $row->total_boxes }}</td>
                    <td>{{ $qty }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align:right">Grand Total:</th>
                <th>{{ $grandTotalBoxes }}</th>
                <th>{{ $grandTotalQty }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
