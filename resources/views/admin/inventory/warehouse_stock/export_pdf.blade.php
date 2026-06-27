<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warehouse Stock Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Warehouse Stock Report</h2>
    <p><strong>Generated On:</strong> {{ now()->format('d M Y, h:i A') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Design No.</th>
                <th>Size Set</th>
                <th>Location (Warehouse / Rack)</th>
                <th class="text-center">Total Boxes</th>
                <th class="text-center">Total Quantity (Pcs)</th>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->product->name_of_garment ?? 'N/A' }}</td>
                    <td>{{ $row->product->design_number ?? 'N/A' }}</td>
                    <td>{{ $row->sizeSet->name ?? 'N/A' }}</td>
                    <td>
                        {{ $row->rack->storeroom->name ?? 'N/A' }} / {{ $row->rack->name ?? 'N/A' }}
                    </td>
                    <td class="text-center">{{ $row->total_boxes }}</td>
                    <td class="text-center">{{ $qty }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Grand Total:</th>
                <th class="text-center">{{ $grandTotalBoxes }}</th>
                <th class="text-center">{{ $grandTotalQty }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
